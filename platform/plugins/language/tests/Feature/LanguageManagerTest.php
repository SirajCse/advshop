<?php

namespace Botble\Language\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Language\LanguageManager;
use Botble\Language\Models\Language;
use Botble\Language\Models\LanguageMeta;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LanguageManagerTest extends BaseTestCase
{
    use RefreshDatabase;

    protected LanguageManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        app(SettingStore::class)->forgetAll();

        Language::query()->delete();
        LanguageMeta::query()->delete();

        $this->createLanguages();

        $this->manager = $this->freshManager();
    }

    protected function freshManager(): LanguageManager
    {
        $manager = app(LanguageManager::class);
        $manager->setSupportedLocales($this->localesArray());

        return $manager;
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

    public function testGetSupportedLocalesReturnsCachedResult(): void
    {
        $locales = $this->manager->getSupportedLocales();

        $this->assertArrayHasKey('ar', $locales);
        $this->assertArrayHasKey('en', $locales);
        $this->assertCount(2, $locales);
    }

    public function testGetDefaultLocaleReturnsDefaultLanguage(): void
    {
        $this->assertEquals('ar', $this->manager->getDefaultLocale());
    }

    public function testGetDefaultLocaleFallsBackToConfigWhenNoDefault(): void
    {
        $manager = app(LanguageManager::class);
        $manager->setSupportedLocales([
            'fr' => [
                'lang_name' => 'French',
                'lang_locale' => 'fr',
                'lang_code' => 'fr',
                'lang_is_default' => false,
                'lang_is_rtl' => false,
                'lang_flag' => 'fr',
            ],
        ]);

        $this->assertEquals(config('app.locale', 'en'), $manager->getDefaultLocale());
    }

    public function testSetSupportedLocalesOverridesCache(): void
    {
        $this->manager->setSupportedLocales([
            'fr' => [
                'lang_name' => 'French',
                'lang_locale' => 'fr',
                'lang_code' => 'fr',
                'lang_is_default' => true,
                'lang_is_rtl' => false,
                'lang_flag' => 'fr',
            ],
        ]);

        $this->assertCount(1, $this->manager->getSupportedLocales());
        $this->assertArrayHasKey('fr', $this->manager->getSupportedLocales());
    }

    public function testCheckLocaleInSupportedLocalesValidatesCorrectly(): void
    {
        $this->assertTrue($this->manager->checkLocaleInSupportedLocales('ar'));
        $this->assertTrue($this->manager->checkLocaleInSupportedLocales('en'));
        $this->assertFalse($this->manager->checkLocaleInSupportedLocales('fr'));
        $this->assertTrue($this->manager->checkLocaleInSupportedLocales(false));
    }

    public function testGetCurrentLocaleNameReturnsName(): void
    {
        $this->manager->setCurrentLocale('ar');
        $this->assertEquals('Arabic', $this->manager->getCurrentLocaleName());

        $this->manager->setCurrentLocale('en');
        $this->assertEquals('English', $this->manager->getCurrentLocaleName());
    }

    public function testGetCurrentLocaleRTLReturnsBooleanCorrectly(): void
    {
        $this->manager->setCurrentLocale('ar');
        $this->assertTrue($this->manager->getCurrentLocaleRTL());

        $this->manager->setCurrentLocale('en');
        $this->assertFalse($this->manager->getCurrentLocaleRTL());
    }

    public function testGetCurrentLocaleCodeReturnsLangCode(): void
    {
        $this->manager->setCurrentLocale('ar');
        $this->assertEquals('ar', $this->manager->getCurrentLocaleCode());
    }

    public function testGetCurrentLocaleFlagReturnsFlag(): void
    {
        $this->manager->setCurrentLocale('ar');
        $this->assertEquals('sa', $this->manager->getCurrentLocaleFlag());

        $this->manager->setCurrentLocale('en');
        $this->assertEquals('us', $this->manager->getCurrentLocaleFlag());
    }

    public function testGetDefaultLocaleCode(): void
    {
        $this->assertEquals('ar', $this->manager->getDefaultLocaleCode());
    }

    public function testGetLocaleByLocaleCode(): void
    {
        $this->assertEquals('en', $this->manager->getLocaleByLocaleCode('en_US'));
        $this->assertEquals('ar', $this->manager->getLocaleByLocaleCode('ar'));
    }

    public function testGetLocaleByLocaleCodeReturnsNullForUnknown(): void
    {
        $this->assertNull($this->manager->getLocaleByLocaleCode('xx_XX'));
    }

    public function testGetSupportedLanguagesKeysReturnsKeys(): void
    {
        $keys = $this->manager->getSupportedLanguagesKeys();

        $this->assertContains('ar', $keys);
        $this->assertContains('en', $keys);
        $this->assertCount(2, $keys);
    }

    public function testFormatLocaleForHrefLang(): void
    {
        $this->assertEquals('en-us', $this->manager->formatLocaleForHrefLang('en_US'));
        $this->assertEquals('ar', $this->manager->formatLocaleForHrefLang('ar'));
        $this->assertEquals('pt-br', $this->manager->formatLocaleForHrefLang('pt_BR'));
        $this->assertNull($this->manager->formatLocaleForHrefLang(null));
        $this->assertNull($this->manager->formatLocaleForHrefLang(''));
    }

    public function testHideDefaultLocaleInURLReturnsSetting(): void
    {
        Setting::set('language_hide_default', true);
        Setting::save();
        $this->assertTrue($this->manager->hideDefaultLocaleInURL());

        Setting::set('language_hide_default', false);
        Setting::save();
        $this->assertFalse($this->manager->hideDefaultLocaleInURL());
    }

    public function testHideDefaultLocaleInURLDefaultsToTrue(): void
    {
        $this->assertTrue($this->manager->hideDefaultLocaleInURL());
    }

    public function testGetLocaleFromMappingReturnsOriginal(): void
    {
        $this->assertEquals('ar', $this->manager->getLocaleFromMapping('ar'));
        $this->assertEquals('en', $this->manager->getLocaleFromMapping('en'));
    }

    public function testSetAndGetCurrentLocale(): void
    {
        $this->manager->setCurrentLocale('en');
        $this->assertEquals('en', $this->manager->getCurrentLocale());

        $this->manager->setCurrentLocale('ar');
        $this->assertEquals('ar', $this->manager->getCurrentLocale());
    }

    public function testSetAndGetCurrentLocaleCode(): void
    {
        $this->manager->setCurrentLocaleCode('en_US');
        $this->assertEquals('en_US', $this->manager->getCurrentLocaleCode());
    }

    public function testRefLangKeyIsConstant(): void
    {
        $this->assertEquals('ref_lang', $this->manager->refLangKey());
    }

    public function testRefFromKeyIsConstant(): void
    {
        $this->assertEquals('ref_from', $this->manager->refFromKey());
    }

    public function testGetLocalizedURLAddsNonDefaultLocalePrefix(): void
    {
        $url = $this->manager->getLocalizedURL('en', url('/some-page'), [], false);
        $this->assertStringContainsString('/en/', $url);
    }

    public function testGetLocalizedURLRemovesDefaultLocalePrefixWhenHidden(): void
    {
        Setting::set('language_hide_default', true);
        Setting::save();

        $manager = $this->freshManager();
        $url = $manager->getLocalizedURL('ar', url('/en/some-page'), [], false);

        $this->assertStringNotContainsString('/ar/', $url);
        $this->assertStringEndsWith('/some-page', $url);
    }

    public function testGetLocalizedURLKeepsPrefixWhenHideDefaultDisabled(): void
    {
        Setting::set('language_hide_default', false);
        Setting::save();

        $manager = $this->freshManager();
        $url = $manager->getLocalizedURL('ar', url('/en/some-page'), [], false);

        $this->assertStringContainsString('/ar/', $url);
    }

    public function testGetLocalizedURLPreservesQueryString(): void
    {
        $url = $this->manager->getLocalizedURL('en', url('/some-page?key=value'), [], false);
        $this->assertStringContainsString('key=value', $url);
    }

    public function testGetNonLocalizedURLStripsLocalePrefix(): void
    {
        $url = $this->manager->getNonLocalizedURL(url('/en/some-page'));

        $this->assertStringNotContainsString('/en/', $url);
        $this->assertStringEndsWith('/some-page', $url);
    }

    public function testLocalizeURLWithDefaultLocaleAndHideDefault(): void
    {
        Setting::set('language_hide_default', true);
        Setting::save();

        $manager = $this->freshManager();
        $url = $manager->localizeURL(url('/some-page'), 'ar');

        $this->assertStringNotContainsString('/ar/', $url);
    }

    public function testSetBaseUrlEndsWithSlash(): void
    {
        $this->manager->setBaseUrl('https://example.com');
        $url = $this->manager->createUrlFromUri('test');

        $this->assertStringStartsWith('https://example.com/', $url);
    }

    public function testCreateUrlFromUriWithoutBaseUrl(): void
    {
        $url = $this->manager->createUrlFromUri('test-page');
        $this->assertStringContainsString('test-page', $url);
    }

    public function testGetDefaultLanguageReturnsModel(): void
    {
        $language = $this->manager->getDefaultLanguage();

        $this->assertInstanceOf(Language::class, $language);
        $this->assertTrue($language->lang_is_default);
        $this->assertEquals('Arabic', $language->lang_name);
    }

    public function testGetDefaultLanguageFallsToFirstWhenNoDefault(): void
    {
        Language::query()->update(['lang_is_default' => false]);

        $manager = $this->freshManager();
        $language = $manager->getDefaultLanguage();

        $this->assertInstanceOf(Language::class, $language);
    }

    public function testGetActiveLanguageReturnsOrderedCollection(): void
    {
        $languages = $this->manager->getActiveLanguage();

        $this->assertCount(2, $languages);
        $this->assertEquals('Arabic', $languages->first()->lang_name);
    }

    public function testSupportedModelsReturnsArray(): void
    {
        $this->manager->registerModule('App\\Models\\SmokeTestModel');

        $models = $this->manager->supportedModels();

        $this->assertContains('App\\Models\\SmokeTestModel', $models);
    }

    public function testRegisterModuleAddsToSupportedModels(): void
    {
        $this->manager->registerModule('App\\Models\\TestModel');

        $models = $this->manager->supportedModels();
        $this->assertContains('App\\Models\\TestModel', $models);
    }

    public function testRegisterModuleAcceptsArray(): void
    {
        $this->manager->registerModule(['App\\Models\\ModelA', 'App\\Models\\ModelB']);

        $models = $this->manager->supportedModels();
        $this->assertContains('App\\Models\\ModelA', $models);
        $this->assertContains('App\\Models\\ModelB', $models);
    }

    public function testUseAcceptLanguageHeaderReturnsSetting(): void
    {
        $this->assertFalse($this->manager->useAcceptLanguageHeader());

        Setting::set('language_auto_detect_user_language', true);
        Setting::save();

        $this->assertTrue($this->manager->useAcceptLanguageHeader());
    }

    public function testSetSwitcherURLsAndGetSwitcherUrl(): void
    {
        $this->manager->setSwitcherURLs([
            ['lang_code' => 'ar', 'url' => url('/product-page')],
            ['lang_code' => 'en_US', 'url' => url('/en/product-page')],
        ]);

        $arUrl = $this->manager->getSwitcherUrl('ar', 'ar');
        $enUrl = $this->manager->getSwitcherUrl('en', 'en_US');

        $this->assertStringEndsWith('/product-page', $arUrl);
        $this->assertStringContainsString('/en/product-page', $enUrl);
    }

    public function testSerializeAndDeserializeTranslatedRoutes(): void
    {
        $this->manager->transRoute('routes.about');
        $serialized = $this->manager->getSerializedTranslatedRoutes();
        $this->assertNotEmpty($serialized);

        $newManager = $this->freshManager();
        $newManager->setSerializedTranslatedRoutes($serialized);
        $this->assertEquals($serialized, $newManager->getSerializedTranslatedRoutes());
    }

    public function testSetSerializedTranslatedRoutesHandlesNull(): void
    {
        $this->manager->setSerializedTranslatedRoutes(null);
        $this->assertNotEmpty($this->manager->getSerializedTranslatedRoutes());
    }

    public function testGetInversedLocaleFromMapping(): void
    {
        $this->assertEquals('ar', $this->manager->getInversedLocaleFromMapping('ar'));
    }

    public function testGetLocalesMappingReturnsEmptyByDefault(): void
    {
        $this->assertEmpty($this->manager->getLocalesMapping());
    }
}
