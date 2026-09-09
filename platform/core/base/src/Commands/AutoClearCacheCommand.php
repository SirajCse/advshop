<?php

namespace Botble\Base\Commands;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Services\ClearCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('cms:cache:auto-clear', 'Automatically clear framework cache when size exceeds the configured threshold')]
class AutoClearCacheCommand extends Command
{
    public function handle(ClearCacheService $clearCacheService): int
    {
        if (! setting('cache_auto_clear_enabled', false)) {
            $this->components->info('Auto-clear cache is disabled. Skipping.');

            return self::SUCCESS;
        }

        $thresholdMb = max(1, (int) setting('cache_size_warning_threshold', 50));
        $thresholdBytes = $thresholdMb * 1024 * 1024;

        $cachePath = storage_path('framework/cache');

        if (! File::isDirectory($cachePath)) {
            $this->components->info('Framework cache directory does not exist. Nothing to clear.');

            return self::SUCCESS;
        }

        $size = $this->calculateDirectorySize($cachePath);
        $formatted = BaseHelper::humanFilesize($size);

        if ($size <= $thresholdBytes) {
            $this->components->info(sprintf(
                'Cache size (%s) is within the %d MB threshold. No action taken.',
                $formatted,
                $thresholdMb
            ));

            return self::SUCCESS;
        }

        $this->components->warn(sprintf(
            'Cache size (%s) exceeded the %d MB threshold. Clearing cache...',
            $formatted,
            $thresholdMb
        ));

        $clearCacheService->clearFrameworkCache();
        $clearCacheService->clearGoogleFontsCache();
        $clearCacheService->clearPurifier();
        $clearCacheService->clearDebugbar();

        $this->components->success('Framework cache cleared successfully.');

        return self::SUCCESS;
    }

    protected function calculateDirectorySize(string $directory): int
    {
        $size = 0;

        foreach (File::glob(rtrim($directory, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += File::isFile($each) ? File::size($each) : $this->calculateDirectorySize($each);
        }

        return $size;
    }
}
