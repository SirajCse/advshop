<?php

namespace Botble\Media\Commands;

use Botble\Media\Facades\RvMedia;
use Botble\Media\Models\MediaFile;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\{progress, table};

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand('cms:media:thumbnail:generate', 'Generate thumbnails for images')]
class GenerateThumbnailCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('override', null, InputOption::VALUE_NONE, 'Override existing thumbnails')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Number of images to process per batch')
            ->addOption('start-offset', null, InputOption::VALUE_REQUIRED, 'Skip this number of images from the beginning', 0);
    }

    public function handle(): int
    {
        $overrideExisting = $this->option('override');
        $startOffset = (int) $this->option('start-offset');

        $totalFiles = MediaFile::query()->count();

        if ($startOffset >= $totalFiles) {
            $this->components->warn('Start offset exceeds total files count.');

            return self::SUCCESS;
        }

        $this->components->info(sprintf(
            'Starting to generate thumbnails%s...',
            $overrideExisting ? ' (overriding existing)' : '',
        ));

        $errors = [];
        $processed = 0;
        $batchSize = (int) ($this->option('batch-size') ?: 200);

        $query = MediaFile::query()
            ->select(['url', 'mime_type', 'folder_id'])
            ->when($startOffset > 0, fn ($q) => $q->skip($startOffset));

        $remaining = $totalFiles - $startOffset;

        $progress = progress(
            label: sprintf('Processing %s %s...', number_format($remaining), Str::plural('file', $remaining)),
            steps: $remaining,
        );

        $query->chunk($batchSize, function ($files) use ($overrideExisting, &$errors, &$processed, $progress) {
            foreach ($files as $file) {
                /** @var MediaFile $file */
                try {
                    $progress->label(sprintf('Processing %s...', $file->url));
                    RvMedia::generateThumbnails($file, overrideExisting: $overrideExisting);
                    $progress->advance();
                    $processed++;
                } catch (Exception $exception) {
                    $errors[] = $file->url;
                    $progress->advance();
                    $this->components->error($exception->getMessage());
                }
            }
        });

        $progress->finish();

        $this->components->info(sprintf('Processed %s files successfully!', number_format($processed)));

        $errors = array_unique($errors);
        $errors = array_map(fn ($item) => [$item], $errors);

        if ($errors) {
            $this->components->info('We are unable to regenerate thumbnail for these files:');

            table(['File directory'], $errors);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
