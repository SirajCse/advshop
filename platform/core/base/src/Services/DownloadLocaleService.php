<?php

namespace Botble\Base\Services;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Zipper;
use Botble\Theme\Facades\Theme;
use Exception;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

class DownloadLocaleService
{
    public const REPOSITORY = 'botble/translations';

    public function handle(string $locale, bool $includeVendor = true): void
    {
        if (! File::isWritable(lang_path())) {
            throw new Exception('The "language" directory is not writable.');
        }

        if (! File::isWritable(storage_path('app'))) {
            throw new Exception('The "storage" directory is not writable.');
        }

        if (! in_array($locale, $this->getAvailableLocales())) {
            throw new Exception('The locale is not available.');
        }

        if ($locale === 'en' || File::exists(lang_path($locale))) {
            return;
        }

        $destination = storage_path('app/translations.zip');
        $path = storage_path("app/translations-develop/{$locale}");

        BaseHelper::maximumExecutionTimeAndMemoryLimit();

        Http::withoutVerifying()
            ->timeout(300)
            ->sink(Utils::tryFopen($destination, 'w'))
            ->get(sprintf('https://github.com/%s/archive/refs/heads/develop.zip', self::REPOSITORY))
            ->throw();

        $zip = new Zipper();

        $zip->extract($destination, storage_path('app'));

        File::copyDirectory("{$path}/{$locale}", lang_path($locale));

        if ($includeVendor && File::isDirectory("{$path}/vendor")) {
            $this->copyLocaleVendorFiles("{$path}/vendor", lang_path('vendor'), $locale);
        }

        if (class_exists('Theme')) {
            $parentTheme = Theme::getThemeName();

            if (Theme::hasInheritTheme()) {
                $parentTheme = Theme::getInheritTheme();
            }

            File::ensureDirectoryExists(lang_path("vendor/themes/{$parentTheme}"));

            if (File::exists("{$path}/{$locale}.json") && ! File::exists(lang_path("vendor/themes/{$parentTheme}/{$locale}.json"))) {
                File::copy("{$path}/{$locale}.json", lang_path("vendor/themes/{$parentTheme}/{$locale}.json"));
            }
        }

        File::delete($destination);
        File::deleteDirectory(storage_path('app/translations-develop'));
    }

    protected function copyLocaleVendorFiles(string $source, string $destination, string $locale): void
    {
        // Copy only the target locale's files from vendor namespaces (core/packages/plugins).
        // This prevents the downloaded repo from overwriting published English files
        // (lang/vendor/{namespace}/*/en/*.php), which Laravel merges over plugin sources
        // via FileLoader::loadNamespaceOverrides and would shadow current strings with
        // stale values from the translations repo.
        foreach (['core', 'packages', 'plugins'] as $namespace) {
            $namespaceDir = "{$source}/{$namespace}";

            if (! File::isDirectory($namespaceDir)) {
                continue;
            }

            foreach (File::directories($namespaceDir) as $moduleDir) {
                $localeSource = $moduleDir . DIRECTORY_SEPARATOR . $locale;

                if (! File::isDirectory($localeSource)) {
                    continue;
                }

                $moduleName = basename($moduleDir);
                File::copyDirectory($localeSource, "{$destination}/{$namespace}/{$moduleName}/{$locale}");
            }
        }

        // Copy only the target locale's JSON for each theme (vendor/themes/{theme}/{locale}.json).
        $themesDir = "{$source}/themes";

        if (File::isDirectory($themesDir)) {
            foreach (File::directories($themesDir) as $themeDir) {
                $localeJson = $themeDir . DIRECTORY_SEPARATOR . "{$locale}.json";

                if (! File::exists($localeJson)) {
                    continue;
                }

                $themeName = basename($themeDir);
                $destFile = "{$destination}/themes/{$themeName}/{$locale}.json";
                File::ensureDirectoryExists(dirname($destFile));
                File::copy($localeJson, $destFile);
            }
        }
    }

    public function getAvailableLocales(): array
    {
        $locales = [];

        try {
            $data = Http::withoutVerifying()
                ->asJson()
                ->acceptJson()
                ->get(sprintf('https://api.github.com/repos/%s/git/trees/develop', self::REPOSITORY))
                ->json('tree');

            foreach ($data as $item) {
                if ($item['type'] === 'tree') {
                    $locales[] = $item['path'];
                }
            }
        } catch (Throwable $e) {
            BaseHelper::logError($e);

            return [];
        }

        return $locales;
    }
}
