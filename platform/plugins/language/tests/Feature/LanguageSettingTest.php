<?php

namespace Botble\Language\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Language\Models\Language;
use Botble\Language\Models\LanguageMeta;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LanguageSettingTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(SettingStore::class)->forgetAll();

        Language::query()->delete();
        LanguageMeta::query()->delete();

        $this->createLanguages();
    }

    protected function createLanguages(): void
    {
        Language::query()->create([
            'lang_name' => 'Arabic',
            'lang_locale' => 'ar',
            'lang_is_default' => true,
            'lang_code' => 'ar',
            'lang_is_rtl' => true,
            'lang_flag' => 'sa',
            'lang_order' => 0,
        ]);

        Language::query()->create([
            'lang_name' => 'English',
            'lang_locale' => 'en',
            'lang_is_default' => false,
            'lang_code' => 'en_US',
            'lang_is_rtl' => false,
            'lang_flag' => 'us',
            'lang_order' => 1,
        ]);
    }

    public function testLanguageHideDefaultSettingDefaultsToTrue(): void
    {
        $this->assertTrue((bool) setting('language_hide_default', true));
    }

    public function testLanguageDisplaySettingDefaultsToAll(): void
    {
        $this->assertEquals('all', setting('language_display', 'all'));
    }

    public function testLanguageSwitcherDisplaySettingDefaultsToDropdown(): void
    {
        $this->assertEquals('dropdown', setting('language_switcher_display', 'dropdown'));
    }

    public function testLanguageAutoDetectSettingDefaultsToFalse(): void
    {
        $this->assertFalse((bool) setting('language_auto_detect_user_language', false));
    }

    public function testLanguageHideLanguagesSettingDefaultsToEmptyArray(): void
    {
        $hidden = json_decode(setting('language_hide_languages', '[]'), true);
        $this->assertIsArray($hidden);
        $this->assertEmpty($hidden);
    }

    public function testLanguageSettingsCanBeUpdated(): void
    {
        Setting::set('language_hide_default', false);
        Setting::set('language_display', 'flag');
        Setting::set('language_switcher_display', 'list');
        Setting::set('language_auto_detect_user_language', true);
        Setting::save();

        $this->assertFalse((bool) setting('language_hide_default'));
        $this->assertEquals('flag', setting('language_display'));
        $this->assertEquals('list', setting('language_switcher_display'));
        $this->assertTrue((bool) setting('language_auto_detect_user_language'));
    }
}
