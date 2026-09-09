<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Language\Models\Language;

class LanguageSeeder extends BaseSeeder
{
    public function run(): void
    {
        if (! is_plugin_active('language')) {
            return;
        }

        $baseSeeder = \Botble\Language\Database\Seeders\LanguageSeeder::class;

        if (class_exists($baseSeeder)) {
            $this->call($baseSeeder);
        }

        if (is_plugin_active('language-advanced')) {
            $this->createLanguages();
        }
    }

    protected function createLanguages(): void
    {
        $languages = [
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

        foreach ($languages as $language) {
            Language::query()->updateOrCreate(
                ['lang_code' => $language['lang_code']],
                $language
            );
        }
    }
}
