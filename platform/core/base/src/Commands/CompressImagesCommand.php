<?php

namespace Botble\Base\Commands;

use Botble\Media\Facades\RvMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Intervention\Image\Encoders\AutoEncoder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

#[AsCommand('cms:images:compress', 'Compress an image file or scan and compress all images in a folder recursively')]
class CompressImagesCommand extends Command
{
    protected int $totalOriginalSize = 0;
    protected int $totalCompressedSize = 0;
    protected int $processedCount = 0;
    protected int $failedCount = 0;

    public function handle(): void
    {
        $path = $this->argument('folder');

        if (! File::exists($path)) {
            $this->error("The path '{$path}' does not exist.");

            return;
        }

        $images = $this->getImages($path);

        if (empty($images)) {
            $this->info("No images found in '{$path}'.");

            return;
        }

        $this->newLine();
        $this->components->info('Starting image compression...');
        $this->newLine();

        foreach ($images as $image) {
            $this->compressImage($image);
        }

        $this->displaySummary();
    }

    protected function getImages(string $path): array
    {
        if (File::isFile($path)) {
            $this->info("Processing file '{$path}'...");

            return [new SplFileInfo($path, dirname($path), $path)];
        }

        $this->components->info("Scanning '{$path}' for images...");

        return File::allFiles($path, true);
    }

    protected function compressImage(SplFileInfo $file): void
    {
        $path = $file->getPathname();
        $extension = strtolower($file->getExtension());

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            return;
        }

        try {
            $originalSize = filesize($path);
            $this->totalOriginalSize += $originalSize;

            $encoder = new AutoEncoder(quality: RvMedia::getImageQuality());

            $image = RvMedia::imageManager()->read($path);

            if ($this->option('width') || $this->option('height')) {
                $image->scaleDown($this->option('width'), $this->option('height'));
            }

            $image->encode($encoder)->save($path);

            clearstatcache(true, $path);
            $compressedSize = filesize($path);
            $this->totalCompressedSize += $compressedSize;

            $saved = $originalSize - $compressedSize;
            $percentage = $originalSize > 0 ? round(($saved / $originalSize) * 100, 2) : 0;

            if ($saved > 0) {
                $this->components->info(sprintf(
                    '✔ %s: %s → %s (saved %s, -%s%%)',
                    $file->getFilename(),
                    $this->formatBytes($originalSize),
                    $this->formatBytes($compressedSize),
                    $this->formatBytes($saved),
                    $percentage
                ));
            } else {
                $this->components->warn(sprintf(
                    '⚠ %s: %s (no reduction)',
                    $file->getFilename(),
                    $this->formatBytes($originalSize)
                ));
            }

            $this->processedCount++;
        } catch (Throwable $e) {
            $this->components->error("❌ Failed: {$file->getFilename()} - " . $e->getMessage());
            $this->failedCount++;
        }
    }

    protected function displaySummary(): void
    {
        $this->newLine();
        $this->components->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $totalSaved = $this->totalOriginalSize - $this->totalCompressedSize;
        $totalPercentage = $this->totalOriginalSize > 0
            ? round(($totalSaved / $this->totalOriginalSize) * 100, 2)
            : 0;

        $this->components->info(sprintf('📊 Summary'));
        $this->components->info(sprintf('  Total images processed: %d', $this->processedCount));

        if ($this->failedCount > 0) {
            $this->components->warn(sprintf('  Failed: %d', $this->failedCount));
        }

        $this->newLine();
        $this->components->info(sprintf('  Original size:   %s', $this->formatBytes($this->totalOriginalSize)));
        $this->components->info(sprintf('  Compressed size: %s', $this->formatBytes($this->totalCompressedSize)));

        if ($totalSaved > 0) {
            $this->components->info(sprintf('  <fg=green>Total saved:     %s (-%s%%)</>', $this->formatBytes($totalSaved), $totalPercentage));
        } else {
            $this->components->warn('  No space saved');
        }

        $this->components->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        $this->components->info('✅ Image compression completed!');
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    protected function configure(): void
    {
        $this->addArgument('folder', InputArgument::REQUIRED, 'The folder or file path which you want to compress');
        $this->addOption('width', null, InputArgument::OPTIONAL, 'The width of the compressed image', 1000);
        $this->addOption('height', null, InputArgument::OPTIONAL, 'The height of the compressed image', 1000);
    }
}
