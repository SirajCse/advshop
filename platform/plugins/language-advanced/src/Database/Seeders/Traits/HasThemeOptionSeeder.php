<?php

namespace Botble\LanguageAdvanced\Database\Seeders\Traits;

use Botble\Language\Facades\Language as LanguageFacade;
use Botble\Setting\Facades\Setting;
use Botble\Theme\Facades\ThemeOption;
use Illuminate\Support\Facades\File;

trait HasThemeOptionSeeder
{
    /**
     * Seed theme options for all locales
     * Loads translations from database/seeders/translations/{locale}/theme-option.json
     *
     * @param array $locales
     */
    protected function seedThemeOptions(array $locales): void
    {
        $defaultLocale = LanguageFacade::getDefaultLocaleCode();

        foreach ($locales as $locale) {
            $translations = $this->loadThemeOptionTranslations($locale);
            if (empty($translations)) {
                continue;
            }

            Setting::set(
                ThemeOption::prepareFromArray($translations, $locale, $defaultLocale)
            );
        }

        Setting::save();
    }

    /**
     * Load theme option translations from JSON file
     *
     * @param string $locale
     * @return array
     */
    protected function loadThemeOptionTranslations(string $locale): array
    {
        $path = database_path("seeders/translations/{$locale}/theme-option.json");

        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);
        $data = json_decode($content, true);

        return $data ?: [];
    }
}
