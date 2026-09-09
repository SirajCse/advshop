<?php

namespace Botble\Translation\Console;

use Botble\Theme\Facades\Theme;
use Botble\Translation\Manager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand('cms:translations:remove-unused-translations', 'Remove unused translations')]
class RemoveUnusedTranslationsCommand extends Command
{
    public function handle(Manager $manager): int
    {
        $theme = $this->option('theme');

        $this->components->info('Remove unused translations in <comment>lang</comment> folder...');

        foreach (File::directories(lang_path('vendor/packages')) as $package) {
            if (! File::isDirectory(package_path(File::basename($package)))) {
                File::deleteDirectory($package);
            }
        }

        foreach (File::directories(lang_path('vendor/plugins')) as $plugin) {
            if (! File::isDirectory(plugin_path(File::basename($plugin)))) {
                File::deleteDirectory($plugin);
            }
        }

        $manager->removeUnusedThemeTranslations($theme);

        $themeName = $theme ?: Theme::getThemeName();

        $this->components->info(sprintf('Removed unused translations for %s', $themeName));

        $this->components->info('Importing...');

        $manager->publishLocales();

        $this->components->info('Done!');

        return self::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            new InputOption('theme', null, InputOption::VALUE_OPTIONAL, 'The theme name to remove unused translations for'),
        ];
    }
}
