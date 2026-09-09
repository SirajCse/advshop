<?php

namespace Botble\Translation\Console;

use Botble\Theme\Facades\Theme;
use Botble\Translation\Manager;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand('cms:translations:update-theme-translations', 'Update theme translations')]
class UpdateThemeTranslationCommand extends Command
{
    public function handle(Manager $manager): int
    {
        $theme = $this->option('theme');

        $count = $manager->updateThemeTranslations($theme);

        $themeName = $theme ?: Theme::getThemeName();

        $this->components->info(sprintf('Found %s keys for %s.', number_format($count), $themeName));

        return self::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            new InputOption('theme', null, InputOption::VALUE_OPTIONAL, 'The theme name to update translations for'),
        ];
    }
}
