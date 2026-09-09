<?php

namespace Botble\Base\Supports;

use Botble\Base\Events\LicenseActivated;
use Botble\Base\Events\LicenseActivating;
use Botble\Base\Events\LicenseDeactivated;
use Botble\Base\Events\LicenseDeactivating;
use Botble\Base\Events\LicenseInvalid;
use Botble\Base\Events\LicenseRevoked;
use Botble\Base\Events\LicenseRevoking;
use Botble\Base\Events\LicenseUnverified;
use Botble\Base\Events\LicenseVerified;
use Botble\Base\Events\LicenseVerifying;
use Botble\Base\Events\SystemUpdateAvailable;
use Botble\Base\Events\SystemUpdateCachesCleared;
use Botble\Base\Events\SystemUpdateCachesClearing;
use Botble\Base\Events\SystemUpdateChecked;
use Botble\Base\Events\SystemUpdateChecking;
use Botble\Base\Events\SystemUpdateDBMigrated;
use Botble\Base\Events\SystemUpdateDBMigrating;
use Botble\Base\Events\SystemUpdateDownloaded;
use Botble\Base\Events\SystemUpdateDownloading;
use Botble\Base\Events\SystemUpdateExtractedFiles;
use Botble\Base\Events\SystemUpdatePublished;
use Botble\Base\Events\SystemUpdatePublishing;
use Botble\Base\Events\SystemUpdateUnavailable;
use Botble\Base\Exceptions\CouldNotConnectToLicenseServerException;
use Botble\Base\Exceptions\LicenseInvalidException;
use Botble\Base\Exceptions\LicenseIsAlreadyActivatedException;
use Botble\Base\Exceptions\MissingCURLExtensionException;
use Botble\Base\Exceptions\MissingZipExtensionException;
use Botble\Base\Exceptions\RequiresLicenseActivatedException;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Services\ClearCacheService;
use Botble\Base\Supports\ValueObjects\CoreProduct;
use Botble\Setting\Facades\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use League\Flysystem\UnableToWriteFile;
use Throwable;
use ZipArchive;

/**
 * DO NOT MODIFY THIS FILE.
 */
final class Core
{
    private string $basePath;

    private string $coreDataFilePath;

    private string $licenseFilePath;

    private string $productId = '';

    private string $productSource = '';

    private string $version = '1.0.0';

    private string $minimumPhpVersion = '8.3.0';

    private string $licenseUrl = 'https://license.botble.com';

    private string $licenseKey = 'CAF4B17F6D3F656125F9';

    private string $cacheLicenseKeyName = '45d0da541764683476f933028d945a46270ba404';

    private string $skipLicenseReminderFilePath;

    private int $verificationPeriod = 1;

    protected static array $coreFileData = [];

    public function __construct(
        private readonly CacheRepository $cache,
        private readonly Filesystem $files
    ) {
        $this->basePath = base_path();
        $this->licenseFilePath = storage_path('.license');
        $this->coreDataFilePath = core_path('core.json');
        $this->skipLicenseReminderFilePath = storage_path('framework/license-reminder-latest-time.txt');

        $this->parseDataFromCoreDataFile();
    }

    private function isLicenseStoredInDatabase(): bool
    {
        return config('core.base.general.license_storage_method') === 'database';
    }

    public static function make(): self
    {
        return app(self::class);
    }

    public function skipLicenseReminder(): bool
    {
        $ttl = Carbon::now()->addDays(3);

        try {
            $this->files->put(
                $this->skipLicenseReminderFilePath,
                encrypt($ttl->toIso8601String())
            );
        } catch (Throwable) {
            throw UnableToWriteFile::atLocation($this->skipLicenseReminderFilePath);
        }

        return true;
    }

    /**
     * @phpstan-impure Result depends on the license reminder file which may change between calls.
     */
    public function isSkippedLicenseReminder(): bool
    {
        try {
            $lastSkipDateTimeString = $this->files->exists($this->skipLicenseReminderFilePath)
                ? $this->files->get($this->skipLicenseReminderFilePath)
                : null;
            $lastSkipDateTimeString = $lastSkipDateTimeString ? decrypt($lastSkipDateTimeString) : null;
            $lastSkipDate = $lastSkipDateTimeString ? Carbon::parse($lastSkipDateTimeString) : null;

            if ($lastSkipDate instanceof Carbon && Carbon::now()->lessThanOrEqualTo($lastSkipDate)) {
                return true;
            }

            $this->clearLicenseReminder();
        } catch (Throwable) {
        }

        return false;
    }

    public function clearLicenseReminder(): void
    {
        if (! $this->files->exists($this->skipLicenseReminderFilePath)) {
            return;
        }

        $this->files->delete($this->skipLicenseReminderFilePath);
    }

    public function getLicenseCacheKey(): string
    {
        return $this->cacheLicenseKeyName;
    }

    public function checkConnection(): bool
    {
        return $this->cache->remember(
            "license:{$this->getLicenseCacheKey()}:check_connection",
            Carbon::now()->addDays($this->verificationPeriod),
            fn () => rescue(fn () => $this->createRequest('check_connection_ext')->ok()) ?: false
        );
    }

    public function version(): string
    {
        return $this->version;
    }

    public function minimumPhpVersion(): string
    {
        return $this->minimumPhpVersion;
    }

    /**
     * @throws \Botble\Base\Exceptions\LicenseInvalidException
     * @throws \Botble\Base\Exceptions\LicenseIsAlreadyActivatedException
     */
    public function activateLicense(string $license, string $client): bool
    {
        LicenseActivating::dispatch($license, $client);

        $response = $this->createRequest('activate_license', [
            'product_id' => $this->productId,
            'license_code' => $license,
            'client_name' => $client,
            'verify_type' => $this->productSource,
        ]);

        $data = $response->json();

        if ($response->failed()) {
            $message = Arr::get($data, 'message');

            throw new LicenseInvalidException($message ?: 'Could not activate your license. Please try again later.');
        }

        if (! Arr::get($data, 'status')) {
            $message = Arr::get($data, 'message');

            if (Arr::get($data, 'status_code') === 'ACTIVATED_MAXIMUM_ALLOWED_PRODUCT_INSTANCES') {
                throw new LicenseIsAlreadyActivatedException($message);
            }

            LicenseInvalid::dispatch($license, $client);

            throw new LicenseInvalidException($message);
        }

        try {
            $licenseContent = Arr::get($data, 'lic_response');

            if ($this->isLicenseStoredInDatabase()) {
                Setting::forceSet('license_file_content', $licenseContent)->save();
            } else {
                $this->files->put($this->licenseFilePath, $licenseContent, true);
            }

            $this->storeLicenseMetadata($license, $client);
        } catch (Throwable $exception) {
            if ($this->isLicenseStoredInDatabase()) {
                throw new LicenseInvalidException('Could not store license in database: ' . $exception->getMessage());
            } else {
                throw UnableToWriteFile::atLocation($this->licenseFilePath);
            }
        }

        Session::forget("license:{$this->getLicenseCacheKey()}:last_checked_date");

        $this->clearLicenseReminder();

        LicenseActivated::dispatch($license, $client);

        return true;
    }

    public function verifyLicense(bool $timeBasedCheck = false, int $timeoutInSeconds = 300): bool
    {
        LicenseVerifying::dispatch();

        if (! $this->isLicenseFileExists()) {
            return false;
        }

        if ($timeBasedCheck && $this->isLicenseFullyVerified()) {
            LicenseVerified::dispatch();

            return true;
        }

        $verified = true;

        if ($timeBasedCheck) {
            $dateFormat = 'd-m-Y';
            $cachesKey = "license:{$this->getLicenseCacheKey()}:last_checked_date";
            $lastCheckedDate = Carbon::createFromFormat(
                $dateFormat,
                Session::get($cachesKey, '01-01-1970')
            )->endOfDay();
            $now = Carbon::now()->addDays($this->verificationPeriod);

            if ($now->greaterThan($lastCheckedDate) && $verified = $this->verifyLicenseDirectly($timeoutInSeconds)) {
                Session::put($cachesKey, $now->format($dateFormat));
                $this->updateLicenseVerificationData();
            }

            return $verified;
        }

        return $this->verifyLicenseDirectly($timeoutInSeconds);
    }

    public function revokeLicense(string $license, string $client): bool
    {
        Session::forget("license:{$this->getLicenseCacheKey()}:last_checked_date");

        LicenseRevoking::dispatch($license, $client);

        $data = [
            'product_id' => $this->productId,
            'license_code' => $license,
            'client_name' => $client,
        ];

        return tap(
            $this->createDeactivateRequest($data),
            fn () => LicenseRevoked::dispatch($license, $client)
        );
    }

    public function deactivateLicense(): bool
    {
        Session::forget("license:{$this->getLicenseCacheKey()}:last_checked_date");

        LicenseDeactivating::dispatch();

        if (! $this->isLicenseFileExists()) {
            return false;
        }

        $data = [
            'product_id' => $this->productId,
            'license_file' => $this->getLicenseFile(),
        ];

        return tap(
            $this->createDeactivateRequest($data),
            fn () => LicenseDeactivated::dispatch()
        );
    }

    public function checkUpdate(): CoreProduct|false
    {
        SystemUpdateChecking::dispatch();

        $response = $this->createRequest('check_update', [
            'product_id' => $this->productId,
            'current_version' => $this->version,
        ]);

        SystemUpdateChecked::dispatch();

        $product = $this->parseProductUpdateResponse($response);

        return tap($product, function (CoreProduct|false $coreProduct): void {
            if (! $coreProduct || ! $coreProduct->hasUpdate()) {
                SystemUpdateUnavailable::dispatch();

                return;
            }

            SystemUpdateAvailable::dispatch($coreProduct);
        });
    }

    public function getLicenseUrl(?string $path = null): string
    {
        return $this->licenseUrl . ($path ? '/' . ltrim($path, '/') : '');
    }

    public function getLatestVersion(): CoreProduct|false
    {
        try {
            $response = $this->createRequest('check_update', [
                'product_id' => $this->productId,
                'current_version' => '0.0.0',
            ]);

            return $this->parseProductUpdateResponse($response);
        } catch (CouldNotConnectToLicenseServerException) {
            return false;
        }
    }

    public function getUpdateSize(string $updateId): float
    {
        try {
            $sizeUpdateResponse = $this->createRequest('get_update_size/' . $updateId, method: 'HEAD');
        } catch (CouldNotConnectToLicenseServerException) {
            return 0;
        }

        return (float) $sizeUpdateResponse->header('Content-Length') ?: 1;
    }

    public function downloadUpdate(string $updateId, string $version): void
    {
        SystemUpdateDownloading::dispatch();

        $data = [
            'product_id' => $this->productId,
            'license_file' => $this->getLicenseFile(),
        ];

        $filePath = $this->getUpdatedFilePath($version);

        $shouldDownload = ! $this->files->exists($filePath)
            || Carbon::createFromTimestamp(filectime($filePath))->diffInHours() > 1
            || filesize($filePath) < 1024;

        if ($shouldDownload) {
            $this->files->delete($filePath);

            try {
                $this->streamDownloadUpdate('download_update/main/' . $updateId, $data, $filePath);
            } catch (RequiresLicenseActivatedException $e) {
                $this->files->delete($filePath);

                throw $e;
            } catch (Throwable $e) {
                $this->files->delete($filePath);

                throw new Exception('Failed to download update: ' . $e->getMessage());
            }
        }

        $this->validateUpdateFile($filePath);

        SystemUpdateDownloaded::dispatch($filePath);
    }

    public function updateFilesAndDatabase(string $version): void
    {
        $this->updateFiles($version);
        $this->updateDatabase();
    }

    public function updateFiles(string $version): bool
    {
        $filePath = $this->getUpdatedFilePath($version);

        if (! $this->files->exists($filePath)) {
            return false;
        }

        $this->cleanCaches();

        $coreTempPath = storage_path('app/core.json');

        try {
            $this->files->copy($this->coreDataFilePath, $coreTempPath);
            $zip = new Zipper();

            $oldLibrary = base_path('vendor/maennchen/zipstream-php');
            if ($this->files->exists($oldLibrary)) {
                $this->files->deleteDirectory($oldLibrary);
            }

            $bootstrapCachePath = base_path('bootstrap/cache');

            @unlink($bootstrapCachePath . '/packages.php');
            @unlink($bootstrapCachePath . '/services.php');

            if ($zip->extract($filePath, $this->basePath)) {
                @unlink($bootstrapCachePath . '/packages.php');
                @unlink($bootstrapCachePath . '/services.php');

                $this->cleanCaches();

                $this->files->delete($filePath);

                SystemUpdateExtractedFiles::dispatch();

                $this->files->delete($coreTempPath);

                return true;
            }

            if ($this->files->exists($coreTempPath)) {
                $this->files->move($coreTempPath, $this->coreDataFilePath);
            }

            return false;
        } catch (Throwable $exception) {
            $bootstrapCachePath = base_path('bootstrap/cache');

            @unlink($bootstrapCachePath . '/packages.php');
            @unlink($bootstrapCachePath . '/services.php');

            if ($this->files->exists($coreTempPath)) {
                $this->files->move($coreTempPath, $this->coreDataFilePath);
            }

            $this->logError($exception);

            throw $exception;
        }
    }

    public function updateDatabase(): void
    {
        try {
            $wrongFile = database_path('migrations/media_folders_table.php');

            if ($this->files->exists($wrongFile)) {
                $this->files->delete($wrongFile);
            }

            $this->runMigrationFiles();
        } catch (Throwable $exception) {
            $this->logError($exception);

            throw $exception;
        }
    }

    public function publishUpdateAssets(): void
    {
        $this->publishCoreAssets();
        $this->publishPackagesAssets();
    }

    public function publishCoreAssets(): void
    {
        SystemUpdatePublishing::dispatch();

        $this->publishAssets(core_path());
    }

    public function publishPackagesAssets(): void
    {
        $this->publishAssets(package_path());

        $this->publishAssets(base_path('vendor'));

        SystemUpdatePublished::dispatch();
    }

    public function cleanCaches(): void
    {
        try {
            SystemUpdateCachesClearing::dispatch();

            ClearCacheService::make()->purgeAll();

            SystemUpdateCachesCleared::dispatch();

            self::$coreFileData = [];
        } catch (Throwable $exception) {
            $this->logError($exception);
        }
    }

    public function cleanUp(): void
    {
        $this->cleanCaches();
    }

    public function logError(Exception|Throwable $exception): void
    {
        BaseHelper::logError($exception);
    }

    public function publishAssets(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            return;
        }

        $platformPath = base_path('platform');

        if (Str::startsWith($path, $platformPath)) {
            $this->publishPlatformAssets($path, $platformPath);
        } else {
            $this->publishVendorAssets($path);
        }
    }

    protected function publishPlatformAssets(string $path, string $platformPath): void
    {
        $relativePath = Str::after($path, $platformPath . DIRECTORY_SEPARATOR);

        foreach ($this->files->directories($path) as $modulePath) {
            $publicPath = BaseHelper::joinPaths([$modulePath, 'public']);

            if (! $this->files->isDirectory($publicPath)) {
                continue;
            }

            $module = basename($modulePath);
            $targetPath = public_path(BaseHelper::joinPaths(['vendor', 'core', $relativePath, $module]));

            try {
                $this->files->ensureDirectoryExists($targetPath);
                $this->files->copyDirectory($publicPath, $targetPath);
            } catch (Throwable $exception) {
                $this->logError($exception);
            }
        }
    }

    protected function publishVendorAssets(string $path): void
    {
        foreach (IlluminateServiceProvider::pathsToPublish(null, 'cms-public') as $from => $to) {
            if (! Str::contains($from, $path)) {
                continue;
            }

            try {
                $this->files->ensureDirectoryExists(dirname($to));
                $this->files->copyDirectory($from, $to);
            } catch (Throwable $exception) {
                $this->logError($exception);
            }
        }
    }

    public function runMigrationFiles(): void
    {
        SystemUpdateDBMigrating::dispatch();

        $migrator = app('migrator');

        rescue(fn () => $migrator->run(database_path('migrations')));

        $paths = [
            core_path(),
            package_path(),
        ];

        foreach ($paths as $path) {
            foreach (BaseHelper::scanFolder($path) as $module) {
                $modulePath = BaseHelper::joinPaths([$path, $module]);

                if (! $this->files->isDirectory($modulePath)) {
                    continue;
                }

                $moduleMigrationPath = BaseHelper::joinPaths([$modulePath, 'database', 'migrations']);

                if ($this->files->isDirectory($moduleMigrationPath)) {
                    $migrator->run($moduleMigrationPath);
                }
            }
        }

        SystemUpdateDBMigrated::dispatch();
    }

    private function validateUpdateFile(string $filePath): void
    {
        if (! class_exists('ZipArchive', false)) {
            throw new MissingZipExtensionException();
        }

        $fileSize = @filesize($filePath);

        if (! $fileSize || $fileSize < 1024) {
            $this->files->delete($filePath);

            throw new Exception(sprintf(
                'The downloaded update file is too small (%s bytes) and likely corrupted. This usually happens when the download times out. Please try again.',
                $fileSize ?: 0
            ));
        }

        $zip = new ZipArchive();
        $result = $zip->open($filePath);

        if ($result !== true) {
            $this->files->delete($filePath);

            $errorMessages = [
                ZipArchive::ER_NOZIP => 'The downloaded file is not a valid zip archive. It may have been corrupted during download.',
                ZipArchive::ER_INCONS => 'The zip archive is inconsistent and may have been corrupted during download.',
                ZipArchive::ER_MEMORY => 'Not enough memory to open the update file. Try increasing your PHP memory_limit.',
                ZipArchive::ER_NOENT => 'The update file was not found. Please try the update again.',
                ZipArchive::ER_READ => 'Could not read the update file. Please check file permissions and try again.',
            ];

            $errorMessage = $errorMessages[$result]
                ?? sprintf('Could not open the update file (error code: %d). Please delete update_main_*.zip from your site root and try again.', $result);

            throw new Exception($errorMessage);
        }

        try {
            if ($zip->getFromName('.env')) {
                throw ValidationException::withMessages([
                    'file' => 'The update file contains a .env file. Please remove it and try again.',
                ]);
            }

            /**
             * @var array{
             *     productId: string,
             *     source: string,
             *     apiUrl: string,
             *     apiKey: string,
             *     version: string,
             *     minimumPhpVersion?: string,
             * }|null $content
             */
            $content = json_decode($zip->getFromName('platform/core/core.json'), true);

            if (! $content) {
                throw ValidationException::withMessages([
                    'file' => 'The update file is invalid. Please contact us for support.',
                ]);
            }

            $validator = Validator::make($content, [
                'productId' => ['required', 'string'],
                'source' => ['required', 'string'],
                'apiUrl' => ['required', 'url'],
                'apiKey' => ['required', 'string'],
                'version' => ['required', 'string'],
                'marketplaceUrl' => ['required', 'url'],
                'marketplaceToken' => ['required', 'string'],
                'minimumPhpVersion' => ['nullable', 'string'],
            ])->stopOnFirstFailure();

            if ($validator->passes()) {
                if ($content['productId'] !== $this->productId) {
                    throw ValidationException::withMessages(
                        ['productId' => 'The product ID of the update does not match the product ID of your website.']
                    );
                }

                if (version_compare($content['version'], $this->version, '<')) {
                    throw ValidationException::withMessages(
                        ['version' => 'The version of the update is lower than the current version.']
                    );
                }

                if (
                    isset($content['minimumPhpVersion']) &&
                    version_compare($content['minimumPhpVersion'], phpversion(), '>')
                ) {
                    throw ValidationException::withMessages(
                        [
                            'minimumPhpVersion' => sprintf(
                                'The minimum PHP version required (v%s) for the update is higher than the current PHP version (v%s). Please upgrade PHP before updating.',
                                $content['minimumPhpVersion'],
                                phpversion()
                            ),
                        ]
                    );
                }
            } else {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }
        } finally {
            $zip->close();
        }
    }

    public function getLicenseFile(): ?string
    {
        if (! $this->isLicenseFileExists()) {
            return null;
        }

        if ($this->isLicenseStoredInDatabase()) {
            return Setting::get('license_file_content');
        }

        return $this->files->get($this->licenseFilePath);
    }

    private function forgotLicensedInformation(): void
    {
        Setting::forceSet(['licensed_to' => ''])->save();

        if ($this->isLicenseStoredInDatabase()) {
            Setting::forceSet(['license_file_content' => ''])->save();
        }

        $this->clearLicenseMetadata();

        Setting::load(true);
    }

    private function parseDataFromCoreDataFile(): void
    {
        if (! $this->files->exists($this->coreDataFilePath)) {
            return;
        }

        $data = $this->getCoreFileData();

        $this->productId = Arr::get($data, 'productId', '');
        $this->productSource = Arr::get($data, 'source', 'envato');
        $this->licenseUrl = rtrim(Arr::get($data, 'apiUrl', $this->licenseUrl), '/');
        $this->licenseKey = Arr::get($data, 'apiKey', $this->licenseKey);
        $this->version = Arr::get($data, 'version', $this->version);
        $this->minimumPhpVersion = Arr::get($data, 'minimumPhpVersion', $this->minimumPhpVersion);
    }

    public function getCoreFileData(): array
    {
        if (self::$coreFileData) {
            return self::$coreFileData;
        }

        if ($coreData = $this->cache->get('core_file_data')) {
            self::$coreFileData = $coreData;

            return $coreData;
        }

        return $this->getCoreFileDataFromDisk();
    }

    private function getCoreFileDataFromDisk(): array
    {
        try {
            $data = json_decode($this->files->get($this->coreDataFilePath), true) ?: [];

            self::$coreFileData = $data;

            $this->cache->put('core_file_data', $data, Carbon::now()->addMinutes(30));

            return $data;
        } catch (FileNotFoundException) {
            return [];
        }
    }

    private function streamDownloadUpdate(string $path, array $data, string $filePath): void
    {
        if (! extension_loaded('curl')) {
            throw new MissingCURLExtensionException();
        }

        // Transient HTTP statuses typically returned by reverse proxies (Cloudflare, nginx)
        // when the upstream download takes too long. Worth retrying a few times before giving up.
        $retryableStatuses = [408, 425, 429, 500, 502, 503, 504, 520, 521, 522, 524];
        $maxAttempts = 3;
        $retryDelaySeconds = 5;
        $response = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::baseUrl(ltrim($this->licenseUrl, '/') . '/api')
                    ->withHeaders([
                        'LB-API-KEY' => $this->licenseKey,
                        'LB-URL' => rtrim(url(''), '/'),
                        'LB-IP' => $this->getClientIpAddress(),
                        'LB-LANG' => 'english',
                    ])
                    ->asJson()
                    ->acceptJson()
                    ->withoutVerifying()
                    ->connectTimeout(100)
                    ->timeout(900)
                    ->withOptions(['sink' => $filePath])
                    ->post($path, $data);
            } catch (ConnectionException $exception) {
                if ($attempt < $maxAttempts) {
                    sleep($retryDelaySeconds);

                    continue;
                }

                throw $exception;
            }

            throw_if($response->unauthorized(), RequiresLicenseActivatedException::class);

            $downloadedSize = $this->files->exists($filePath) ? filesize($filePath) : 0;
            $fileValid = $downloadedSize >= 1024;

            if ($response->successful() && $fileValid) {
                return;
            }

            $incompleteFile = $response->successful() && ! $fileValid;
            $transientHttpError = ! $response->successful() && in_array($response->status(), $retryableStatuses, true);

            if ($attempt < $maxAttempts && ($incompleteFile || $transientHttpError)) {
                sleep($retryDelaySeconds);

                continue;
            }

            break;
        }

        if ($response && ! $response->successful()) {
            throw new Exception(sprintf(
                'Server returned HTTP %d after %d attempt(s). This may be caused by a timeout or server overload. Please try again later or contact your hosting provider.',
                $response->status(),
                $maxAttempts
            ));
        }

        throw new Exception(sprintf(
            'The update file download appears incomplete after %d attempt(s) (file is empty or too small). This is usually caused by a server timeout. Please try again.',
            $maxAttempts
        ));
    }

    private function createRequest(string $path, array $data = [], string $method = 'POST', int $timeoutInSeconds = 300): Response
    {
        if (! extension_loaded('curl')) {
            throw new MissingCURLExtensionException();
        }

        try {
            $request = Http::baseUrl(ltrim($this->licenseUrl, '/') . '/api')
                ->withHeaders([
                    'LB-API-KEY' => $this->licenseKey,
                    'LB-URL' => rtrim(url(''), '/'),
                    'LB-IP' => $this->getClientIpAddress(),
                    'LB-LANG' => 'english',
                ])
                ->asJson()
                ->acceptJson()
                ->withoutVerifying()
                ->connectTimeout(100)
                ->timeout($timeoutInSeconds);

            return match (Str::upper($method)) {
                'GET' => $request->get($path, $data),
                'HEAD' => $request->head($path),
                default => $request->post($path, $data)
            };
        } catch (ConnectionException $exception) {
            if (App::hasDebugModeEnabled()) {
                throw $exception;
            }

            throw new CouldNotConnectToLicenseServerException('Could not connect to the license server. Please try again later.');
        }
    }

    private function createDeactivateRequest(array $data): bool
    {
        $response = $this->createRequest('deactivate_license', $data);

        $data = $response->json();

        if ($response->ok() && Arr::get($data, 'status')) {
            if ($this->isLicenseStoredInDatabase()) {
                Setting::forceDelete('license_file_content');
            } else {
                $this->files->delete($this->licenseFilePath);
            }

            $this->forgotLicensedInformation();

            return true;
        }

        return false;
    }

    private function getClientIpAddress(): string
    {
        $staticIp = config('core.base.general.static_ip');

        if ($staticIp && filter_var($staticIp, FILTER_VALIDATE_IP)) {
            return $staticIp;
        }

        return Helper::getIpFromThirdParty();
    }

    public function getServerIP(): string
    {
        return $this->getClientIpAddress();
    }

    private function verifyLicenseDirectly(int $timeoutInSeconds = 300): bool
    {
        if (! $this->isLicenseFileExists()) {
            LicenseUnverified::dispatch();

            return false;
        }

        $data = [
            'product_id' => $this->productId,
            'license_file' => $this->getLicenseFile(),
        ];

        try {
            $response = $this->createRequest('verify_license', $data, 'POST', $timeoutInSeconds);
        } catch (CouldNotConnectToLicenseServerException) {
            return true;
        }

        $data = $response->json();

        if ($response->ok() && Arr::get($data, 'status')) {
            LicenseVerified::dispatch();

            return true;
        } else {
            LicenseUnverified::dispatch();

            $statusCode = Arr::get($data, 'status_code');
            $message = Arr::get($data, 'message', '');

            if ($statusCode === 'LICENSE_DEACTIVATED' ||
                (Str::contains(Str::lower($message), ['deactivated', 'invalid', 'not found']) &&
                ! Str::contains(Str::lower($message), 'blocked'))) {
                $this->handleDeactivatedLicense();
            }

            return false;
        }
    }

    private function parseProductUpdateResponse(Response $response): CoreProduct|false
    {
        $data = $response->json();

        $updateId = Arr::get($data, 'update_id');
        $version = Arr::get($data, 'version');

        if ($response->ok() && Arr::get($data, 'status') && $updateId && $version) {
            $releaseDate = Arr::get($data, 'release_date');
            $parsedDate = $releaseDate ? Carbon::parse($releaseDate) : Carbon::now();

            return new CoreProduct(
                $updateId,
                $version,
                $parsedDate,
                trim((string) Arr::get($data, 'summary')),
                trim((string) Arr::get($data, 'changelog')),
                (bool) Arr::get($data, 'has_sql')
            );
        }

        return false;
    }

    private function getUpdatedFilePath(string $version): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'update_main_' . str_replace('.', '_', $version) . '.zip';
    }

    protected function isLicenseFileExists(): bool
    {
        if ($this->isLicenseStoredInDatabase()) {
            return Setting::has('license_file_content') && ! empty(Setting::get('license_file_content'));
        }

        return $this->files->exists($this->licenseFilePath);
    }

    public function getLicenseFilePath(): string
    {
        return $this->licenseFilePath;
    }

    private function storeLicenseMetadata(string $purchaseCode, string $client): void
    {
        $now = Carbon::now();

        Setting::forceSet([
            'license_activated_at' => $now->toIso8601String(),
            'license_last_verified_at' => $now->toIso8601String(),
            'license_next_check_at' => $now->copy()->addDays(7)->toIso8601String(),
            'license_verification_count' => 1,
            'license_purchase_code_hash' => hash('sha256', $purchaseCode),
            'license_server_ip' => $this->getClientIpAddress(),
            'license_domain' => parse_url(url('/'), PHP_URL_HOST),
            'licensed_to' => $client,
        ])->save();
    }

    private function clearLicenseMetadata(): void
    {
        $metadataKeys = [
            'license_activated_at',
            'license_last_verified_at',
            'license_next_check_at',
            'license_verification_count',
            'license_purchase_code_hash',
            'license_server_ip',
            'license_domain',
        ];

        foreach ($metadataKeys as $key) {
            Setting::forceSet([$key => ''])->save();
        }
    }

    public function isLicenseFullyVerified(): bool
    {
        if (! Setting::has('license_activated_at') ||
            ! Setting::has('license_last_verified_at') ||
            ! Setting::has('license_next_check_at')) {
            return false;
        }

        $nextCheckAt = Setting::get('license_next_check_at');
        if ($nextCheckAt && Carbon::parse($nextCheckAt)->isFuture()) {
            return true;
        }

        return false;
    }

    /**
     * @phpstan-impure Result depends on stored license data which may change between calls.
     */
    public function hasLicenseData(): bool
    {
        if ($this->isLicenseStoredInDatabase()) {
            return Setting::has('license_file_content') && ! empty(Setting::get('license_file_content'));
        }

        return $this->files->exists($this->licenseFilePath);
    }

    public function handleDeactivatedLicense(): void
    {
        if ($this->isLicenseStoredInDatabase()) {
            Setting::forceSet(['license_file_content' => ''])->save();
            Setting::forceSet(['licensed_to' => ''])->save();
        } else {
            if ($this->files->exists($this->licenseFilePath)) {
                $this->files->delete($this->licenseFilePath);
            }
        }

        $this->clearLicenseMetadata();

        Setting::load(true);

        Session::forget("license:{$this->getLicenseCacheKey()}:last_checked_date");
        session()->forget('license_check_time');
    }

    public function updateLicenseVerificationData(): void
    {
        $now = Carbon::now();
        $verificationCount = (int) Setting::get('license_verification_count', 0);

        Setting::forceSet([
            'license_last_verified_at' => $now->toIso8601String(),
            'license_next_check_at' => $now->copy()->addDays(7)->toIso8601String(),
            'license_verification_count' => $verificationCount + 1,
        ])->save();
    }
}
