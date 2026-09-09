<?php

namespace Botble\LanguageAdvanced\Database\Seeders\Traits;

use Botble\Language\Models\Language;

trait HasLanguageSeeder
{
    /**
     * Create languages from configuration
     * Requires languages() method in the seeder
     */
    protected function createLanguages(): void
    {
        $languages = $this->languages();

        foreach ($languages as $language) {
            Language::query()->updateOrCreate(
                ['lang_code' => $language['lang_code']],
                $language
            );
        }
    }

    /**
     * Get languages configuration
     * Override this method in your seeder to customize languages
     *
     * @return array Format: [['lang_name' => '...', 'lang_code' => '...', ...], ...]
     */
    protected function languages(): array
    {
        return [
            [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_is_default' => true,
                'lang_code' => 'en_US',
                'lang_is_rtl' => false,
                'lang_flag' => 'us',
                'lang_order' => 0,
            ],
            [
                'lang_name' => 'Arabic',
                'lang_locale' => 'ar',
                'lang_is_default' => false,
                'lang_code' => 'ar',
                'lang_is_rtl' => true,
                'lang_flag' => 'sa',
                'lang_order' => 1,
            ],
            [
                'lang_name' => 'Tiếng Việt',
                'lang_locale' => 'vi',
                'lang_is_default' => false,
                'lang_code' => 'vi',
                'lang_is_rtl' => false,
                'lang_flag' => 'vn',
                'lang_order' => 2,
            ],
            [
                'lang_name' => 'Français',
                'lang_locale' => 'fr',
                'lang_is_default' => false,
                'lang_code' => 'fr',
                'lang_is_rtl' => false,
                'lang_flag' => 'fr',
                'lang_order' => 3,
            ],
            [
                'lang_name' => 'Bahasa Indonesia',
                'lang_locale' => 'id',
                'lang_is_default' => false,
                'lang_code' => 'id',
                'lang_is_rtl' => false,
                'lang_flag' => 'id',
                'lang_order' => 4,
            ],
            [
                'lang_name' => 'Türkçe',
                'lang_locale' => 'tr',
                'lang_is_default' => false,
                'lang_code' => 'tr',
                'lang_is_rtl' => false,
                'lang_flag' => 'tr',
                'lang_order' => 5,
            ],
        ];
    }
}
