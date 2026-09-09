<?php

namespace Botble\Language\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Language\Facades\Language;
use Botble\Language\Listeners\AddHrefLangListener;
use Botble\Language\Models\Language as LanguageModel;
use Botble\Language\Models\LanguageMeta;
use Botble\Page\Models\Page;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Supports\SettingStore;
use Botble\Slug\Models\Slug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AddHrefLangListenerCachingTest extends TestCase
{
    use RefreshDatabase;

    protected AddHrefLangListener $listener;

    protected function setUp(): void
    {
        parent::setUp();

        app(SettingStore::class)->forgetAll();

        LanguageModel::query()->truncate();
        LanguageMeta::query()->truncate();
        Page::query()->truncate();
        Slug::query()->truncate();
        DB::table('slugs_translations')->truncate();

        $this->createLanguages();

        $this->listener = new AddHrefLangListener();

        Language::setSupportedLocales([
            'en' => [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_code' => 'en_US',
                'lang_is_default' => true,
                'lang_is_rtl' => false,
                'lang_flag' => 'us',
            ],
            'vi' => [
                'lang_name' => 'Tiếng Việt',
                'lang_locale' => 'vi',
                'lang_code' => 'vi',
                'lang_is_default' => false,
                'lang_is_rtl' => false,
                'lang_flag' => 'vn',
            ],
            'ar' => [
                'lang_name' => 'Arabic',
                'lang_locale' => 'ar',
                'lang_code' => 'ar',
                'lang_is_default' => false,
                'lang_is_rtl' => true,
                'lang_flag' => 'sa',
            ],
        ]);

        Setting::set('language_hide_default', true);
        Setting::save();
    }

    public function testGetAdvancedTranslatedUrlCachesSlugOnFirstCall(): void
    {
        if (! is_plugin_active('language-advanced')) {
            $this->markTestSkipped('language-advanced plugin is not active');
        }

        // Create a page
        $page = Page::query()->create([
            'name' => 'Test Page',
        ]);

        // Create base slug
        $slug = Slug::query()->create([
            'key' => 'test-page',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->id,
        ]);

        // Create Vietnamese translation
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-test',
            'prefix' => 'trang',
        ]);

        Language::setCurrentLocaleCode('en_US');

        // Call getAdvancedTranslatedUrl via reflection to access protected method
        $reflection = new \ReflectionClass($this->listener);
        $method = $reflection->getMethod('getAdvancedTranslatedUrl');
        $method->setAccessible(true);

        // First call - should populate cache
        $result = $method->invoke(
            $this->listener,
            Page::class,
            $page->id,
            'vi',
            'vi',
            'en'
        );

        // Verify cache is populated
        $slugCacheDone = $reflection->getProperty('slugCacheDone');
        $slugCacheDone->setAccessible(true);
        $this->assertTrue($slugCacheDone->getValue($this->listener));

        $cachedSlug = $reflection->getProperty('cachedSlug');
        $cachedSlug->setAccessible(true);
        $this->assertNotNull($cachedSlug->getValue($this->listener));

        // Verify result contains Vietnamese translation
        $this->assertStringContainsString('trang-test', $result);
    }

    public function testGetAdvancedTranslatedUrlUsesCachedSlugOnSecondCall(): void
    {
        if (! is_plugin_active('language-advanced')) {
            $this->markTestSkipped('language-advanced plugin is not active');
        }

        // Create a page
        $page = Page::query()->create([
            'name' => 'Test Page Cached',
        ]);

        // Create base slug
        $slug = Slug::query()->create([
            'key' => 'test-page-cached',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->id,
        ]);

        // Create multiple translations
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-test-cache',
            'prefix' => 'trang',
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'ar',
            'key' => 'صفحة-اختبار',
            'prefix' => 'صفحات',
        ]);

        Language::setCurrentLocaleCode('en_US');

        $reflection = new \ReflectionClass($this->listener);
        $method = $reflection->getMethod('getAdvancedTranslatedUrl');
        $method->setAccessible(true);

        // First call - populates cache
        $viResult = $method->invoke(
            $this->listener,
            Page::class,
            $page->id,
            'vi',
            'vi',
            'en'
        );

        // Count queries before second call
        $this->assertStringContainsString('trang-test-cache', $viResult);

        // Second call - should use cached slug
        $arResult = $method->invoke(
            $this->listener,
            Page::class,
            $page->id,
            'ar',
            'ar',
            'en'
        );

        $this->assertStringContainsString('صفحة-اختبار', $arResult);

        // Verify cache was used (slugCacheDone should still be true)
        $slugCacheDone = $reflection->getProperty('slugCacheDone');
        $slugCacheDone->setAccessible(true);
        $this->assertTrue($slugCacheDone->getValue($this->listener));
    }

    public function testGetAdvancedTranslatedUrlUsesTranslatedPrefix(): void
    {
        if (! is_plugin_active('language-advanced')) {
            $this->markTestSkipped('language-advanced plugin is not active');
        }

        // Create a page
        $page = Page::query()->create([
            'name' => 'Prefix Test Page',
        ]);

        // Create base slug with prefix
        $slug = Slug::query()->create([
            'key' => 'prefix-test',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->id,
        ]);

        // Create translation with different prefix
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-prefix-test',
            'prefix' => 'cac-trang', // Different prefix
        ]);

        Language::setCurrentLocaleCode('en_US');

        $reflection = new \ReflectionClass($this->listener);
        $method = $reflection->getMethod('getAdvancedTranslatedUrl');
        $method->setAccessible(true);

        $result = $method->invoke(
            $this->listener,
            Page::class,
            $page->id,
            'vi',
            'vi',
            'en'
        );

        // Should use translated prefix, not base prefix
        $this->assertStringContainsString('cac-trang', $result);
        $this->assertStringNotContainsString('/pages/', $result); // Base prefix should not appear
    }

    public function testGetAdvancedTranslatedUrlFallsBackToBasePrefix(): void
    {
        if (! is_plugin_active('language-advanced')) {
            $this->markTestSkipped('language-advanced plugin is not active');
        }

        // Create a page
        $page = Page::query()->create([
            'name' => 'Fallback Prefix Test',
        ]);

        // Create base slug with prefix
        $slug = Slug::query()->create([
            'key' => 'fallback-test',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->id,
        ]);

        // Create translation without prefix (should fallback to base)
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-fallback-test',
            'prefix' => null,
        ]);

        Language::setCurrentLocaleCode('en_US');

        $reflection = new \ReflectionClass($this->listener);
        $method = $reflection->getMethod('getAdvancedTranslatedUrl');
        $method->setAccessible(true);

        $result = $method->invoke(
            $this->listener,
            Page::class,
            $page->id,
            'vi',
            'vi',
            'en'
        );

        // Should fallback to base prefix when translation prefix is null
        $this->assertStringContainsString('pages', $result);
    }

    public function testGetAdvancedTranslatedUrlReturnsNullWhenSlugNotFound(): void
    {
        if (! is_plugin_active('language-advanced')) {
            $this->markTestSkipped('language-advanced plugin is not active');
        }

        Language::setCurrentLocaleCode('en_US');

        $reflection = new \ReflectionClass($this->listener);
        $method = $reflection->getMethod('getAdvancedTranslatedUrl');
        $method->setAccessible(true);

        $result = $method->invoke(
            $this->listener,
            Page::class,
            999, // Non-existent page ID
            'vi',
            'vi',
            'en'
        );

        $this->assertNull($result);
    }

    public function testGetAdvancedTranslatedUrlReturnsNullWhenTranslationNotFound(): void
    {
        if (! is_plugin_active('language-advanced')) {
            $this->markTestSkipped('language-advanced plugin is not active');
        }

        // Create a page
        $page = Page::query()->create([
            'name' => 'No Translation Test',
        ]);

        // Create base slug without translations
        $slug = Slug::query()->create([
            'key' => 'no-translation',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->id,
        ]);

        Language::setCurrentLocaleCode('en_US');

        $reflection = new \ReflectionClass($this->listener);
        $method = $reflection->getMethod('getAdvancedTranslatedUrl');
        $method->setAccessible(true);

        $result = $method->invoke(
            $this->listener,
            Page::class,
            $page->id,
            'vi', // Request Vietnamese translation that doesn't exist
            'vi',
            'en'
        );

        $this->assertNull($result);
    }

    public function testGetAdvancedTranslatedUrlWithHideDefaultLocale(): void
    {
        if (! is_plugin_active('language-advanced')) {
            $this->markTestSkipped('language-advanced plugin is not active');
        }

        Setting::set('language_hide_default', true);
        Setting::save();

        // Create a page
        $page = Page::query()->create([
            'name' => 'Hide Default Test',
        ]);

        // Create base slug
        $slug = Slug::query()->create([
            'key' => 'hide-default-test',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->id,
        ]);

        // Create English translation (default locale)
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'en_US',
            'key' => 'hide-default-slug',
            'prefix' => 'pages',
        ]);

        Language::setCurrentLocaleCode('vi');

        $reflection = new \ReflectionClass($this->listener);
        $method = $reflection->getMethod('getAdvancedTranslatedUrl');
        $method->setAccessible(true);

        $result = $method->invoke(
            $this->listener,
            Page::class,
            $page->id,
            'en_US',
            'en',
            'en'
        );

        // When hide_default is true and requesting default locale, URL should not have locale prefix
        $this->assertStringContainsString('hide-default-slug', $result);
        $this->assertStringNotContainsString('/en/', $result);
    }

    protected function createLanguages(): void
    {
        LanguageModel::query()->create([
            'lang_name' => 'English',
            'lang_locale' => 'en',
            'lang_is_default' => true,
            'lang_code' => 'en_US',
            'lang_is_rtl' => false,
            'lang_flag' => 'us',
            'lang_order' => 0,
        ]);

        LanguageModel::query()->create([
            'lang_name' => 'Tiếng Việt',
            'lang_locale' => 'vi',
            'lang_is_default' => false,
            'lang_code' => 'vi',
            'lang_is_rtl' => false,
            'lang_flag' => 'vn',
            'lang_order' => 1,
        ]);

        LanguageModel::query()->create([
            'lang_name' => 'Arabic',
            'lang_locale' => 'ar',
            'lang_is_default' => false,
            'lang_code' => 'ar',
            'lang_is_rtl' => true,
            'lang_flag' => 'sa',
            'lang_order' => 2,
        ]);
    }
}
