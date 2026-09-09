<?php

namespace Botble\Language\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Language\LanguageManager;
use Botble\Language\Models\Language;
use Botble\Language\Models\LanguageMeta;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class GetSwitcherUrlTest extends BaseTestCase
{
    use RefreshDatabase;

    protected LanguageManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        app(SettingStore::class)->forgetAll();

        Language::query()->truncate();
        LanguageMeta::query()->truncate();

        $this->createLanguages();

        $this->manager = app(LanguageManager::class);
    }

    public function testDefaultLocaleContentPageHasNoPrefixWhenHideDefaultEnabled(): void
    {
        Setting::set('language_hide_default', true);
        Setting::save();

        $manager = app(LanguageManager::class);

        $manager->setSupportedLocales([
            'ar' => [
                'lang_name' => 'Arabic',
                'lang_locale' => 'ar',
                'lang_code' => 'ar',
                'lang_is_default' => true,
                'lang_is_rtl' => true,
                'lang_flag' => 'sa',
            ],
            'en' => [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_code' => 'en_US',
                'lang_is_default' => false,
                'lang_is_rtl' => false,
                'lang_flag' => 'us',
            ],
        ]);

        $url = $manager->getLocalizedURL('ar', url('/en/aluminum-plates'), [], false);

        $this->assertStringNotContainsString('/ar/', $url, 'Default locale content URL should not have /ar/ prefix');
        $this->assertStringEndsWith('/aluminum-plates', $url);
    }

    public function testNonDefaultLocaleContentPageHasPrefixWhenHideDefaultEnabled(): void
    {
        Setting::set('language_hide_default', true);
        Setting::save();

        $manager = app(LanguageManager::class);

        $manager->setSupportedLocales([
            'ar' => [
                'lang_name' => 'Arabic',
                'lang_locale' => 'ar',
                'lang_code' => 'ar',
                'lang_is_default' => true,
                'lang_is_rtl' => true,
                'lang_flag' => 'sa',
            ],
            'en' => [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_code' => 'en_US',
                'lang_is_default' => false,
                'lang_is_rtl' => false,
                'lang_flag' => 'us',
            ],
        ]);

        $url = $manager->getLocalizedURL('en', url('/ar/aluminum-plates'), [], false);

        $this->assertStringContainsString('/en/', $url, 'Non-default locale should keep /en/ prefix');
    }

    public function testDefaultLocaleContentPageHasPrefixWhenHideDefaultDisabled(): void
    {
        Setting::set('language_hide_default', false);
        Setting::save();

        $manager = app(LanguageManager::class);

        $manager->setSupportedLocales([
            'ar' => [
                'lang_name' => 'Arabic',
                'lang_locale' => 'ar',
                'lang_code' => 'ar',
                'lang_is_default' => true,
                'lang_is_rtl' => true,
                'lang_flag' => 'sa',
            ],
            'en' => [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_code' => 'en_US',
                'lang_is_default' => false,
                'lang_is_rtl' => false,
                'lang_flag' => 'us',
            ],
        ]);

        $url = $manager->getLocalizedURL('ar', url('/en/aluminum-plates'), [], false);

        $this->assertStringContainsString('/ar/', $url, 'Default locale should keep /ar/ prefix when hide_default is disabled');
    }

    public function testGetSwitcherUrlHomepageAlwaysHasLocalePrefix(): void
    {
        Setting::set('language_hide_default', true);
        Setting::save();

        $manager = app(LanguageManager::class);

        $manager->setSupportedLocales([
            'ar' => [
                'lang_name' => 'Arabic',
                'lang_locale' => 'ar',
                'lang_code' => 'ar',
                'lang_is_default' => true,
                'lang_is_rtl' => true,
                'lang_flag' => 'sa',
            ],
            'en' => [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_code' => 'en_US',
                'lang_is_default' => false,
                'lang_is_rtl' => false,
                'lang_flag' => 'us',
            ],
        ]);

        $arUrl = $manager->getSwitcherUrl('ar', 'ar');
        $enUrl = $manager->getSwitcherUrl('en', 'en_US');

        $this->assertStringEndsWith('/ar', $arUrl, 'Homepage switcher for default locale must include /ar for language switching');
        $this->assertStringEndsWith('/en', $enUrl, 'Homepage switcher for non-default locale must include /en');
    }

    public function testGetSwitcherUrlWithSwitcherUrlsHomepageKeepsLocalePrefix(): void
    {
        Setting::set('language_hide_default', true);
        Setting::save();

        $manager = app(LanguageManager::class);

        $manager->setSupportedLocales([
            'ar' => [
                'lang_name' => 'Arabic',
                'lang_locale' => 'ar',
                'lang_code' => 'ar',
                'lang_is_default' => true,
                'lang_is_rtl' => true,
                'lang_flag' => 'sa',
            ],
            'en' => [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_code' => 'en_US',
                'lang_is_default' => false,
                'lang_is_rtl' => false,
                'lang_flag' => 'us',
            ],
        ]);

        $manager->setSwitcherURLs([
            ['lang_code' => 'ar', 'url' => url('/')],
            ['lang_code' => 'en_US', 'url' => url('/en')],
        ]);

        $arUrl = $manager->getSwitcherUrl('ar', 'ar');

        $this->assertStringEndsWith('/ar', $arUrl, 'SwitcherURLs homepage for default locale must include /ar for language switching');
    }

    public function testGetSwitcherUrlWithSwitcherUrlsContentPageRespectsSetting(): void
    {
        Setting::set('language_hide_default', true);
        Setting::save();

        $manager = app(LanguageManager::class);

        $manager->setSupportedLocales([
            'ar' => [
                'lang_name' => 'Arabic',
                'lang_locale' => 'ar',
                'lang_code' => 'ar',
                'lang_is_default' => true,
                'lang_is_rtl' => true,
                'lang_flag' => 'sa',
            ],
            'en' => [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_code' => 'en_US',
                'lang_is_default' => false,
                'lang_is_rtl' => false,
                'lang_flag' => 'us',
            ],
        ]);

        $manager->setSwitcherURLs([
            ['lang_code' => 'ar', 'url' => url('/aluminum-plates')],
            ['lang_code' => 'en_US', 'url' => url('/en/aluminum-plates')],
        ]);

        $arUrl = $manager->getSwitcherUrl('ar', 'ar');
        $enUrl = $manager->getSwitcherUrl('en', 'en_US');

        $this->assertStringNotContainsString('/ar/', $arUrl, 'SwitcherURLs content page for default locale should not have /ar/ prefix');
        $this->assertStringEndsWith('/aluminum-plates', $arUrl);
        $this->assertStringContainsString('/en/', $enUrl, 'SwitcherURLs content page for non-default locale should keep /en/ prefix');
    }

    public function testGetSwitcherUrlContentPageNoPrefixForDefaultLocale(): void
    {
        Setting::set('language_hide_default', true);
        Setting::set('language_show_default_item_if_current_version_not_existed', true);
        Setting::save();

        $manager = app(LanguageManager::class);

        $manager->setSupportedLocales([
            'ar' => [
                'lang_name' => 'Arabic',
                'lang_locale' => 'ar',
                'lang_code' => 'ar',
                'lang_is_default' => true,
                'lang_is_rtl' => true,
                'lang_flag' => 'sa',
            ],
            'en' => [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_code' => 'en_US',
                'lang_is_default' => false,
                'lang_is_rtl' => false,
                'lang_flag' => 'us',
            ],
        ]);

        $this->app['request'] = Request::create(url('/en/aluminum-plates'));
        $manager = app(LanguageManager::class);
        $manager->setSupportedLocales([
            'ar' => [
                'lang_name' => 'Arabic',
                'lang_locale' => 'ar',
                'lang_code' => 'ar',
                'lang_is_default' => true,
                'lang_is_rtl' => true,
                'lang_flag' => 'sa',
            ],
            'en' => [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_code' => 'en_US',
                'lang_is_default' => false,
                'lang_is_rtl' => false,
                'lang_flag' => 'us',
            ],
        ]);

        $url = $manager->getSwitcherUrl('ar', 'ar');

        $this->assertStringNotContainsString('/ar/', $url, 'Content page switcher for default locale should not have /ar/ prefix');
        $this->assertStringEndsWith('/aluminum-plates', $url);
    }

    public function testGetSwitcherUrlAllLocalesGetPrefixWhenHideDefaultDisabled(): void
    {
        Setting::set('language_hide_default', false);
        Setting::save();

        $manager = app(LanguageManager::class);

        $manager->setSupportedLocales([
            'ar' => [
                'lang_name' => 'Arabic',
                'lang_locale' => 'ar',
                'lang_code' => 'ar',
                'lang_is_default' => true,
                'lang_is_rtl' => true,
                'lang_flag' => 'sa',
            ],
            'en' => [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_code' => 'en_US',
                'lang_is_default' => false,
                'lang_is_rtl' => false,
                'lang_flag' => 'us',
            ],
        ]);

        $this->app['request'] = Request::create(url('/en/aluminum-plates'));
        $manager = app(LanguageManager::class);
        $manager->setSupportedLocales([
            'ar' => [
                'lang_name' => 'Arabic',
                'lang_locale' => 'ar',
                'lang_code' => 'ar',
                'lang_is_default' => true,
                'lang_is_rtl' => true,
                'lang_flag' => 'sa',
            ],
            'en' => [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_code' => 'en_US',
                'lang_is_default' => false,
                'lang_is_rtl' => false,
                'lang_flag' => 'us',
            ],
        ]);

        $arUrl = $manager->getSwitcherUrl('ar', 'ar');
        $enUrl = $manager->getSwitcherUrl('en', 'en_US');

        $this->assertStringContainsString('/ar/', $arUrl, 'Default locale should have prefix when hide_default is disabled');
        $this->assertStringContainsString('/en/', $enUrl, 'Non-default locale should have prefix when hide_default is disabled');
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
}
