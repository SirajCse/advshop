<?php

namespace Botble\Translation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Finder\Finder;
use Symfony\Component\VarExporter\VarExporter;
use Throwable;

#[AsCommand('cms:translation:check-missing', 'Check for missing translations in the platform')]
class CheckMissingTranslationCommand extends Command
{
    protected $signature = 'cms:translation:check-missing
                            {--locale=en : The locale to check against (default: en)}
                            {--path= : Specific path to scan (default: platform directory)}';

    protected $description = 'Scan all translation keys in /platform and check if they are translated';

    protected array $missingTranslations = [];

    protected array $arrayTranslations = [];

    protected array $missingKeysInFiles = [];

    protected array $unusedKeysInFiles = [];

    protected array $ignoreArrayTranslations = [
        'core/base::tables',
        'core/base::notices',
        'core/base::countries',
        'core/media::media.javascript',
        'packages/plugin-management::marketplace',
        'plugins/captcha::captcha.numbers',
        'plugins/captcha::captcha.operands',
    ];

    protected array $commonKeywords = [
        'CSV',
        'Excel',
        'ID',
        'SKU',
        'URL',
        'API',
        'HTML',
        'CSS',
        'JavaScript',
        'JSON',
        'XML',
        'PDF',
        'RSS',
        'SEO',
        'SSL',
        'HTTP',
        'HTTPS',
        'FTP',
        'IP',
        'UUID',
        'Email',
        'Google',
        'Facebook',
        'Twitter',
        'LinkedIn',
        'YouTube',
        'Instagram',
        'Pinterest',
        'WhatsApp',
        'Telegram',
        'SMTP',
        'IMAP',
        'POP3',
        'OAuth',
        'JWT',
        'GitHub',
        'GitLab',
        'Bitbucket',
        'Amazon',
        'AWS',
        'S3',
        'CDN',
        'SQL',
        'MySQL',
        'PostgreSQL',
        'MongoDB',
        'Redis',
        'Cache',
        'Cookie',
        'Session',
        'Token',
        'CMS',
        'PHP',
        'Laravel',
        'WordPress',
        'Bootstrap',
        'jQuery',
        'Vue',
        'React',
        'Angular',
        'Webhook',
        'Cron',
        'Cronjob',
        'AJAX',
        'REST',
        'GraphQL',
        'WebSocket',
        'UTF-8',
        'ASCII',
        'ISO',
        'MIME',
        'Base64',
        'MD5',
        'SHA',
        'AES',
        'CAPTCHA',
        'reCAPTCHA',
        'QR',
        'Zip',
        'Gzip',
        'Robots.txt',
        'Sitemap',
        'GTM',
        'GA',
        'Logo',
        'Website',
        'Google Analytics',
        'Google Tag',
        'Google Tag Assistant',
        'Google Maps',
        'X',
        'GTM Container ID',
        'TLS',
        'Blog',
        'Tab',
        'Tab #:number',
        'X (Twitter)',
        'GTM-XXXXXXX',
    ];

    protected int $scannedFiles = 0;

    protected int $totalKeys = 0;

    public function handle(): int
    {
        $locale = $this->option('locale');
        $path = $this->option('path') ?: platform_path();

        if (! File::exists($path)) {
            $this->components->error("Path does not exist: {$path}");

            return self::FAILURE;
        }

        $this->components->info("Scanning for missing translations in: {$path}");
        $this->components->info("Checking locale: {$locale}");
        $this->newLine();

        $translationKeys = $this->findTranslationKeys($path);

        if (empty($translationKeys)) {
            $this->components->warn('No translation keys found.');

            return self::SUCCESS;
        }

        $this->totalKeys = count($translationKeys);
        $this->components->info("Found {$this->totalKeys} translation keys in {$this->scannedFiles} files.");
        $this->newLine();

        $translationKeysWithContext = $this->findTranslationKeysWithContext($path);

        $this->checkMissingTranslations($translationKeys, $locale);

        if ($locale !== 'en') {
            $this->checkMissingKeysInTranslationFiles($path, $locale);
        }

        $this->checkArrayTranslations($translationKeys, $translationKeysWithContext, $locale);

        $this->displayResults();

        return self::SUCCESS;
    }

    protected function findTranslationKeys(string $path): array
    {
        $keys = [];

        $stringPattern =
            "[^\w]" .
            '(trans)' .
            "\(\s*" .
            "(?P<quote>['\"])" .
            "(?P<string>(?:\\\k{quote}|(?!\k{quote}).)*)" .
            "\k{quote}" .
            "\s*[\),]";

        $finder = new Finder();
        $finder->in($path)->exclude('storage')->exclude('vendor')->name('*.php')->files();

        foreach ($finder as $file) {
            $content = $file->getContents();

            if (preg_match('/static\s+\$langPath\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $langPathMatch)) {
                $keys = array_merge($keys, $this->getEnumTranslationKeys($langPathMatch[1]));
            }

            if (! preg_match_all('/' . $stringPattern . '/siU', $content, $matches)) {
                continue;
            }

            $this->scannedFiles++;

            foreach ($matches['string'] as $key) {
                if (preg_match('/\$|->|{\$/', $key)) {
                    continue;
                }

                if (preg_match('/^(core|packages|plugins)\/[^:]+::[^:]+/i', $key)) {
                    $keys[] = trim($key);
                }
            }
        }

        return array_unique($keys);
    }

    protected function getEnumTranslationKeys(string $langPath): array
    {
        $keys = [$langPath];

        $filePath = $this->resolveTranslationFilePath($langPath);

        if (! $filePath || ! File::exists($filePath)) {
            return $keys;
        }

        $translationKeys = $this->getKeysFromFile($filePath);

        foreach ($translationKeys as $key) {
            $keys[] = $langPath . '.' . $key;
        }

        return $keys;
    }

    protected function resolveTranslationFilePath(string $key): ?string
    {
        if (! preg_match('/^(core|packages|plugins)\/([^:]+)::(.+)$/', $key, $matches)) {
            return null;
        }

        $type = $matches[1];
        $module = $matches[2];
        $file = str_replace('.', '/', $matches[3]);

        return platform_path("{$type}/{$module}/resources/lang/en/{$file}.php");
    }

    protected function findTranslationKeysWithContext(string $path): array
    {
        $keysWithContext = [];

        $stringPattern =
            "[^\w]" .
            '(trans)' .
            "\(\s*" .
            "(?P<quote>['\"])" .
            "(?P<string>(?:\\\k{quote}|(?!\k{quote}).)*)" .
            "\k{quote}" .
            "\s*[\),]";

        $finder = new Finder();
        $finder->in($path)->exclude('storage')->exclude('vendor')->name('*.php')->files();

        foreach ($finder as $file) {
            $content = $file->getContents();

            if (! preg_match_all('/' . $stringPattern . '/siU', $content, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            foreach ($matches['string'] as $match) {
                $key = $match[0];
                $offset = $match[1];

                if (preg_match('/\$|->|{\$/', $key)) {
                    continue;
                }

                if (preg_match('/^(core|packages|plugins)\/[^:]+::[^:]+/i', $key)) {
                    $key = trim($key);
                    $lineNumber = substr_count(substr($content, 0, $offset), "\n") + 1;

                    $lines = explode("\n", $content);
                    $contextLine = $lines[$lineNumber - 1] ?? '';

                    $isInStringContext = $this->isUsedInStringContext($contextLine, $key);

                    if (! isset($keysWithContext[$key])) {
                        $keysWithContext[$key] = [];
                    }

                    $keysWithContext[$key][] = [
                        'file' => $file->getRelativePathname(),
                        'line' => $lineNumber,
                        'context' => trim($contextLine),
                        'is_string_context' => $isInStringContext,
                    ];
                }
            }
        }

        return $keysWithContext;
    }

    protected function isUsedInStringContext(string $line, string $key): bool
    {
        $dangerousPatterns = [
            '/echo\s+.*trans\s*\(\s*[\'"]' . preg_quote($key, '/') . '[\'"]\s*\)/',
            '/\{\{\s*trans\s*\(\s*[\'"]' . preg_quote($key, '/') . '[\'"]\s*\)\s*\}\}/',
            '/<[^>]+>\s*trans\s*\(\s*[\'"]' . preg_quote($key, '/') . '[\'"]\s*\)\s*<\/[^>]+>/',
            '/return\s+.*trans\s*\(\s*[\'"]' . preg_quote($key, '/') . '[\'"]\s*\)/',
            '/\$\w+\s*=\s*trans\s*\(\s*[\'"]' . preg_quote($key, '/') . '[\'"]\s*\)/',
            '/\.\s*trans\s*\(\s*[\'"]' . preg_quote($key, '/') . '[\'"]\s*\)/',
            '/->\w+\(\s*trans\s*\(\s*[\'"]' . preg_quote($key, '/') . '[\'"]\s*\)\s*\)/',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    protected function checkArrayTranslations(array $keys, array $keysWithContext, string $locale): void
    {
        if (empty($keysWithContext)) {
            return;
        }

        $progressBar = $this->output->createProgressBar(count($keys));
        $progressBar->setFormat('Checking for array translations: %current%/%max% [%bar%] %percent:3s%%');
        $progressBar->start();

        foreach ($keys as $key) {
            $translation = trans($key, [], $locale);

            if (is_array($translation)) {
                if (in_array($key, $this->ignoreArrayTranslations)) {
                    $progressBar->advance();

                    continue;
                }

                $contexts = $keysWithContext[$key] ?? [];
                $hasStringContext = false;

                foreach ($contexts as $context) {
                    if ($context['is_string_context']) {
                        $hasStringContext = true;

                        break;
                    }
                }

                $this->arrayTranslations[] = [
                    'key' => $key,
                    'has_string_context' => $hasStringContext,
                    'contexts' => $contexts,
                ];
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    protected function checkMissingTranslations(array $keys, string $locale): void
    {
        $progressBar = $this->output->createProgressBar(count($keys));
        $progressBar->setFormat('Checking translations: %current%/%max% [%bar%] %percent:3s%%');
        $progressBar->start();

        foreach ($keys as $key) {
            $translation = trans($key, [], $locale);

            if ($translation === $key) {
                $this->missingTranslations[] = $key;
            } elseif ($locale !== 'en') {
                $enTranslation = trans($key, [], 'en');

                if ($translation === $enTranslation && ! is_array($translation)) {
                    if (! $this->isCommonKeyword($translation)) {
                        $this->missingTranslations[] = $key;
                    }
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    protected function isCommonKeyword(string $text): bool
    {
        $text = trim($text);

        if (in_array($text, $this->commonKeywords, true)) {
            return true;
        }

        foreach ($this->commonKeywords as $keyword) {
            if (strcasecmp($text, $keyword) === 0) {
                return true;
            }
        }

        $words = preg_split('/\s+/', $text);
        if (count($words) === 1 && in_array(strtoupper($text), $this->commonKeywords, true)) {
            return true;
        }

        if ($this->isUrl($text)) {
            return true;
        }

        if ($this->isEmail($text)) {
            return true;
        }

        return false;
    }

    protected function isUrl(string $text): bool
    {
        $text = trim($text);

        if (preg_match('/^(https?:\/\/|ftp:\/\/|\/\/)/i', $text)) {
            return true;
        }

        if (filter_var($text, FILTER_VALIDATE_URL) !== false) {
            return true;
        }

        return false;
    }

    protected function isEmail(string $text): bool
    {
        $text = trim($text);

        if (filter_var($text, FILTER_VALIDATE_EMAIL) !== false) {
            return true;
        }

        if (preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $text)) {
            return true;
        }

        return false;
    }

    protected function checkMissingKeysInTranslationFiles(string $path, string $locale): void
    {
        $this->components->info("Checking for missing keys in {$locale} translation files...");
        $this->newLine();

        $finder = new Finder();
        $finder->in($path)->path('/resources\/lang\/en/')->name('*.php')->files();

        $checkedFiles = 0;

        foreach ($finder as $file) {
            $enFilePath = $file->getRealPath();
            $relativePath = str_replace(platform_path(), '', $enFilePath);
            $localeFilePath = str_replace('/lang/en/', "/lang/{$locale}/", $enFilePath);

            if (! File::exists($localeFilePath)) {
                continue;
            }

            $enKeys = $this->getKeysFromFile($enFilePath);
            $localeKeys = $this->getKeysFromFile($localeFilePath);

            if (empty($enKeys)) {
                continue;
            }

            $missingKeys = array_diff($enKeys, $localeKeys);
            $unusedKeys = array_diff($localeKeys, $enKeys);

            if (! empty($missingKeys)) {
                $this->missingKeysInFiles[$relativePath] = [
                    'locale_file' => str_replace(platform_path(), '', $localeFilePath),
                    'missing_keys' => $missingKeys,
                ];
            }

            if (! empty($unusedKeys)) {
                $this->unusedKeysInFiles[$relativePath] = [
                    'locale_file' => str_replace(platform_path(), '', $localeFilePath),
                    'unused_keys' => $unusedKeys,
                ];
            }

            $checkedFiles++;
        }

        if ($checkedFiles > 0) {
            $this->components->info("Checked {$checkedFiles} translation file pairs.");
            $this->newLine();
        }
    }

    protected function getKeysFromFile(string $filePath): array
    {
        try {
            $content = include $filePath;

            if (! is_array($content)) {
                return [];
            }

            return $this->flattenKeys($content);
        } catch (Throwable) {
            return [];
        }
    }

    protected function flattenKeys(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenKeys($value, $fullKey));
            } else {
                $result[] = $fullKey;
            }
        }

        return $result;
    }

    protected function displayResults(): void
    {
        if (! empty($this->missingKeysInFiles)) {
            $this->displayMissingKeysInFiles();
        }

        if (! empty($this->unusedKeysInFiles)) {
            $this->displayUnusedKeysInFiles();
        }

        if (! empty($this->arrayTranslations)) {
            $this->displayArrayTranslationWarnings();
        }

        if (empty($this->missingTranslations)) {
            if (empty($this->arrayTranslations) && empty($this->missingKeysInFiles) && empty($this->unusedKeysInFiles) && empty($this->unusedKeysInEnglishFiles)) {
                $this->components->success('All translation keys are properly translated!');
            }

            return;
        }

        $missingCount = count($this->missingTranslations);
        $translatedCount = $this->totalKeys - $missingCount;
        $percentage = round(($translatedCount / $this->totalKeys) * 100, 2);

        $this->components->warn("Found {$missingCount} missing translations out of {$this->totalKeys} total keys.");
        $this->components->info("Translation coverage: {$percentage}% ({$translatedCount}/{$this->totalKeys})");
        $this->newLine();

        $this->displayFlatResults();
    }

    protected function displayMissingKeysInFiles(): void
    {
        $totalMissingKeys = 0;
        foreach ($this->missingKeysInFiles as $data) {
            $totalMissingKeys += count($data['missing_keys']);
        }

        $this->components->error("Found {$totalMissingKeys} missing key(s) in " . count($this->missingKeysInFiles) . ' translation file(s)!');
        $this->newLine();

        foreach ($this->missingKeysInFiles as $enFile => $data) {
            $localeFile = $data['locale_file'];
            $missingKeys = $data['missing_keys'];
            $missingCount = count($missingKeys);

            $this->line("  <fg=red>✗</> <fg=yellow>{$localeFile}</>");
            $this->line("    <fg=gray>Missing {$missingCount} key(s) compared to: {$enFile}</>");
            $this->newLine();

            $displayKeys = array_slice($missingKeys, 0, 10);
            foreach ($displayKeys as $key) {
                $this->line("    <fg=red>-</> {$key}");
            }

            if (count($missingKeys) > 10) {
                $remaining = count($missingKeys) - 10;
                $this->line("    <fg=gray>... and {$remaining} more key(s)</>");
            }

            $this->newLine();
        }

        $this->components->warn('These keys exist in English files but are missing in the locale files.');
        $this->components->info('Fix: Add the missing keys to the translation files.');
        $this->newLine(2);
    }

    protected function displayUnusedKeysInFiles(): void
    {
        $totalUnusedKeys = 0;
        foreach ($this->unusedKeysInFiles as $data) {
            $totalUnusedKeys += count($data['unused_keys']);
        }

        $this->components->warn("Found {$totalUnusedKeys} unused key(s) in " . count($this->unusedKeysInFiles) . ' translation file(s)!');
        $this->newLine();

        foreach ($this->unusedKeysInFiles as $enFile => $data) {
            $localeFile = $data['locale_file'];
            $unusedKeys = $data['unused_keys'];
            $unusedCount = count($unusedKeys);

            $this->line("  <fg=yellow>⚠</> <fg=yellow>{$localeFile}</>");
            $this->line("    <fg=gray>{$unusedCount} key(s) not found in: {$enFile}</>");
            $this->newLine();

            $displayKeys = array_slice($unusedKeys, 0, 10);
            foreach ($displayKeys as $key) {
                $this->line("    <fg=yellow>+</> {$key}");
            }

            if (count($unusedKeys) > 10) {
                $remaining = count($unusedKeys) - 10;
                $this->line("    <fg=gray>... and {$remaining} more key(s)</>");
            }

            $this->newLine();
        }

        $this->components->warn('These keys exist in the locale files but not in the English files.');
        $this->components->info('Action: Review and remove these unused keys to keep translation files clean.');
        $this->newLine();

        if ($this->confirm('Do you want to remove these unused keys from the locale files?')) {
            $this->removeUnusedKeys();
        } else {
            $this->newLine();
        }
    }

    protected function removeUnusedKeys(): void
    {
        $this->newLine();
        $this->components->info('Removing unused keys from locale files...');
        $this->newLine();

        $removedCount = 0;
        $filesModified = 0;

        foreach ($this->unusedKeysInFiles as $enFile => $data) {
            $localeFilePath = platform_path() . $data['locale_file'];
            $unusedKeys = $data['unused_keys'];

            if (! File::exists($localeFilePath)) {
                $this->components->error("File not found: {$localeFilePath}");

                continue;
            }

            try {
                $content = include $localeFilePath;

                if (! is_array($content)) {
                    $this->components->error("Invalid translation file: {$localeFilePath}");

                    continue;
                }

                $modified = false;
                foreach ($unusedKeys as $key) {
                    if ($this->removeKeyFromArray($content, $key)) {
                        $removedCount++;
                        $modified = true;
                    }
                }

                if ($modified) {
                    $this->writeTranslationFile($localeFilePath, $content);
                    $filesModified++;
                    $this->line("  <fg=green>✓</> {$data['locale_file']} - Removed " . count($unusedKeys) . ' key(s)');
                }
            } catch (Throwable $e) {
                $this->components->error("Failed to process {$localeFilePath}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->components->success("Removed {$removedCount} unused key(s) from {$filesModified} file(s).");
        $this->newLine();
    }

    protected function removeKeyFromArray(array &$array, string $key): bool
    {
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        $current = &$array;

        foreach ($keys as $k) {
            if (! isset($current[$k]) || ! is_array($current[$k])) {
                return false;
            }
            $current = &$current[$k];
        }

        if (isset($current[$lastKey])) {
            unset($current[$lastKey]);

            return true;
        }

        return false;
    }

    protected function writeTranslationFile(string $filePath, array $content): void
    {
        $output = "<?php\n\nreturn " . VarExporter::export($content) . ";\n";

        $filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);

        File::put($filePath, $output);

        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($filePath, true);
        }
    }

    protected function displayArrayTranslationWarnings(): void
    {
        $dangerousCount = 0;

        foreach ($this->arrayTranslations as $item) {
            if ($item['has_string_context']) {
                $dangerousCount++;
            }
        }

        if ($dangerousCount > 0) {
            $this->components->error("Found {$dangerousCount} array translation(s) that may cause 'Array to string conversion' errors!");
            $this->newLine();

            foreach ($this->arrayTranslations as $item) {
                if (! $item['has_string_context']) {
                    continue;
                }

                $this->line("  <fg=red>✗</> <fg=yellow>{$item['key']}</>");

                foreach ($item['contexts'] as $context) {
                    if ($context['is_string_context']) {
                        $this->line("    <fg=red>⚠ DANGER:</> {$context['file']}:{$context['line']}");
                        $this->line("    <fg=gray>{$context['context']}</>");
                    }
                }

                $this->newLine();
            }

            $this->components->warn('These translation keys return arrays but are used in string contexts.');
            $this->components->info('Fix: Convert array values to flat string keys in translation files.');
            $this->newLine(2);
        }
    }

    protected function displayFlatResults(): void
    {
        $this->components->info('Missing translation keys:');
        $this->newLine();

        if (count($this->missingTranslations) <= 50) {
            foreach ($this->missingTranslations as $key) {
                $this->line("  - {$key}");
            }
        } else {
            $this->line('  First 25 keys:');
            for ($i = 0; $i < 25; $i++) {
                $this->line("    - {$this->missingTranslations[$i]}");
            }

            $remaining = count($this->missingTranslations) - 50;
            if ($remaining > 0) {
                $this->newLine();
                $this->line("  <fg=gray>... and {$remaining} more keys ...</>");
                $this->newLine();
            }

            $this->line('  Last 25 keys:');
            for ($i = count($this->missingTranslations) - 25; $i < count($this->missingTranslations); $i++) {
                $this->line("    - {$this->missingTranslations[$i]}");
            }
        }

        $this->newLine();
    }
}
