<?php

namespace Botble\Language\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Language\LanguageManager;
use Botble\Language\Models\Language;
use Botble\Language\Models\LanguageMeta;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HrefLangTest extends BaseTestCase
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
        $this->manager->setSupportedLocales($this->localesArray());
    }

    protected function localesArray(): array
    {
        return [
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
        ];
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

    public function testFormatLocaleForHrefLangConvertsUnderscoreToDash(): void
    {
        $this->assertEquals('en-us', $this->manager->formatLocaleForHrefLang('en_US'));
        $this->assertEquals('pt-br', $this->manager->formatLocaleForHrefLang('pt_BR'));
        $this->assertEquals('zh-cn', $this->manager->formatLocaleForHrefLang('zh_CN'));
    }

    public function testFormatLocaleForHrefLangLowercases(): void
    {
        $this->assertEquals('en-us', $this->manager->formatLocaleForHrefLang('EN_US'));
        $this->assertEquals('fr', $this->manager->formatLocaleForHrefLang('FR'));
    }

    public function testFormatLocaleForHrefLangHandlesSimpleCode(): void
    {
        $this->assertEquals('ar', $this->manager->formatLocaleForHrefLang('ar'));
        $this->assertEquals('en', $this->manager->formatLocaleForHrefLang('en'));
        $this->assertEquals('fr', $this->manager->formatLocaleForHrefLang('fr'));
    }

    public function testFormatLocaleForHrefLangReturnsNullForEmpty(): void
    {
        $this->assertNull($this->manager->formatLocaleForHrefLang(null));
        $this->assertNull($this->manager->formatLocaleForHrefLang(''));
    }

    public function testGetLocalizedURLForHrefLangWithHideDefault(): void
    {
        Setting::set('language_hide_default', true);
        Setting::save();

        $manager = app(LanguageManager::class);
        $manager->setSupportedLocales($this->localesArray());

        $arUrl = $manager->getLocalizedURL('ar', url('/en/product-page'), [], false);
        $enUrl = $manager->getLocalizedURL('en', url('/product-page'), [], false);

        $this->assertStringNotContainsString('/ar/', $arUrl, 'Default locale hreflang should not have /ar/ prefix');
        $this->assertStringContainsString('/en/', $enUrl, 'Non-default locale hreflang should have /en/ prefix');
    }

    public function testGetLocalizedURLHandlesRootUrlCorrectly(): void
    {
        Setting::set('language_hide_default', true);
        Setting::save();

        $manager = app(LanguageManager::class);
        $manager->setSupportedLocales($this->localesArray());

        $enUrl = $manager->getLocalizedURL('en', url('/'), [], false);

        $this->assertStringEndsWith('/en', $enUrl);
    }

    public function testGetLocalizedURLSwapsLocalePrefix(): void
    {
        $manager = app(LanguageManager::class);
        $manager->setSupportedLocales($this->localesArray());

        $url = $manager->getLocalizedURL('en', url('/ar/some-page'), [], false);

        $this->assertStringContainsString('/en/', $url);
        $this->assertStringNotContainsString('/ar/', $url);
        $this->assertStringEndsWith('/some-page', $url);
    }

    public function testGetLocalizedURLPreservesQueryParams(): void
    {
        $manager = app(LanguageManager::class);
        $manager->setSupportedLocales($this->localesArray());

        $url = $manager->getLocalizedURL('en', url('/ar/search?q=test&page=2'), [], false);

        $this->assertStringContainsString('q=test', $url);
        $this->assertStringContainsString('page=2', $url);
    }

    public function testGetLocalizedURLWithMultiplePathSegments(): void
    {
        $manager = app(LanguageManager::class);
        $manager->setSupportedLocales($this->localesArray());

        $url = $manager->getLocalizedURL('en', url('/ar/category/subcategory/product'), [], false);

        $this->assertStringContainsString('/en/', $url);
        $this->assertStringContainsString('/category/subcategory/product', $url);
    }
}
