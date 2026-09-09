<?php

namespace Botble\Theme\Commands;

use Botble\Setting\Models\Setting;
use Botble\Theme\Events\RenderingThemeOptionSettings;
use Botble\Theme\Facades\ThemeOption;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('cms:theme:options:cleanup-shared', 'Remove stale locale-specific keys for shared theme option fields')]
class ThemeOptionCleanupSharedCommand extends Command
{
    protected $signature = 'cms:theme:options:cleanup-shared {--dry-run : Show what would be removed without removing}';

    public function handle(): int
    {
        RenderingThemeOptionSettings::dispatch();

        if (defined('RENDERING_THEME_OPTIONS_PAGE')) {
            do_action(RENDERING_THEME_OPTIONS_PAGE);
        }

        ThemeOption::constructSections();

        $theme = setting('theme');

        if (! $theme) {
            $this->components->error('No active theme found.');

            return self::FAILURE;
        }

        $fields = Arr::get(ThemeOption::getFields(), 'theme', []);

        $sharedKeys = collect($fields)
            ->filter(fn (array $field) => ThemeOption::isFieldShared(Arr::get($field, 'id', '')))
            ->keys();

        if ($sharedKeys->isEmpty()) {
            $this->components->info('No shared fields found.');

            return self::SUCCESS;
        }

        $prefix = 'theme-' . $theme . '-';
        $dryRun = $this->option('dry-run');
        $removedCount = 0;

        foreach ($sharedKeys as $fieldKey) {
            $defaultKey = $prefix . $fieldKey;

            $staleKeys = Setting::query()
                ->where('key', 'LIKE', $prefix . '%-' . $fieldKey)
                ->where('key', '!=', $defaultKey)
                ->pluck('key');

            foreach ($staleKeys as $key) {
                if ($dryRun) {
                    $this->line("Would remove: {$key}");
                } else {
                    Setting::query()->where('key', $key)->delete();
                    $this->line("Removed: {$key}");
                }
                $removedCount++;
            }
        }

        $verb = $dryRun ? 'Would remove' : 'Removed';
        $this->components->info("{$verb} {$removedCount} stale locale-specific keys.");

        return self::SUCCESS;
    }
}
