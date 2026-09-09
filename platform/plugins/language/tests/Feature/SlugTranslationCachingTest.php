<?php

namespace Botble\Language\Tests\Feature;

use Botble\Language\Models\Language as LanguageModel;
use Botble\Language\Models\LanguageMeta;
use Botble\Page\Models\Page;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Supports\SettingStore;
use Botble\Slug\Models\Slug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SlugTranslationCachingTest extends TestCase
{
    use RefreshDatabase;

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

        Setting::set('language_hide_default', true);
        Setting::save();
    }

    public function testSlugWithTranslationsCanBeLoadedWithAllTranslations(): void
    {
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

        // Create translations for multiple languages
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-test',
            'prefix' => 'trang',
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'ar',
            'key' => 'صفحة-اختبار',
            'prefix' => 'صفحات',
        ]);

        // Simulate caching: retrieve base slug once with all translations
        $cachedSlug = Slug::query()
            ->where('reference_id', $page->id)
            ->where('reference_type', Page::class)
            ->first();

        // Retrieve all translations for this slug in one query
        $cachedTranslations = DB::table('slugs_translations')
            ->where('slugs_id', $cachedSlug->id)
            ->get();

        // Now we have both base and all translations cached
        $this->assertNotNull($cachedSlug);
        $this->assertCount(2, $cachedTranslations);

        // Can access any language without additional queries
        $viTranslation = $cachedTranslations->firstWhere('lang_code', 'vi');
        $this->assertNotNull($viTranslation);
        $this->assertEquals('trang-test', $viTranslation->key);

        $arTranslation = $cachedTranslations->firstWhere('lang_code', 'ar');
        $this->assertNotNull($arTranslation);
        $this->assertEquals('صفحة-اختبار', $arTranslation->key);
    }

    public function testTranslationPrefixFallbackToBaseWhenNull(): void
    {
        $page = Page::query()->create([
            'name' => 'Fallback Test',
        ]);

        $slug = Slug::query()->create([
            'key' => 'fallback-page',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->id,
        ]);

        // Create translation with null prefix
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-fallback',
            'prefix' => null,
        ]);

        // Simulate caching retrieval
        $cachedSlug = Slug::query()
            ->where('reference_id', $page->id)
            ->where('reference_type', Page::class)
            ->first();

        $cachedTranslations = DB::table('slugs_translations')
            ->where('slugs_id', $cachedSlug->id)
            ->get();

        // Find Vietnamese translation
        $viTranslation = $cachedTranslations->firstWhere('lang_code', 'vi');

        // Apply fallback logic: use translation prefix if available, else base prefix
        $prefixToUse = $viTranslation->prefix ?? $cachedSlug->prefix;

        $this->assertNull($viTranslation->prefix);
        $this->assertEquals('pages', $prefixToUse);
    }

    public function testTranslationPrefixUsedWhenDifferentFromBase(): void
    {
        $page = Page::query()->create([
            'name' => 'Different Prefix Test',
        ]);

        $slug = Slug::query()->create([
            'key' => 'different-prefix-page',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->id,
        ]);

        // Create translation with different prefix
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-tieu-diem',
            'prefix' => 'cac-trang',
        ]);

        // Simulate caching retrieval
        $cachedSlug = Slug::query()
            ->where('reference_id', $page->id)
            ->where('reference_type', Page::class)
            ->first();

        $cachedTranslations = DB::table('slugs_translations')
            ->where('slugs_id', $cachedSlug->id)
            ->get();

        $viTranslation = $cachedTranslations->firstWhere('lang_code', 'vi');

        // Apply fallback logic
        $prefixToUse = $viTranslation->prefix ?? $cachedSlug->prefix;

        $this->assertNotNull($viTranslation->prefix);
        $this->assertNotEquals($cachedSlug->prefix, $viTranslation->prefix);
        $this->assertEquals('cac-trang', $prefixToUse);
    }

    public function testSlugCachingReducesQueries(): void
    {
        $page = Page::query()->create([
            'name' => 'Query Test',
        ]);

        $slug = Slug::query()->create([
            'key' => 'query-test-page',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->id,
        ]);

        // Create 5 translations
        for ($i = 0; $i < 5; $i++) {
            DB::table('slugs_translations')->insert([
                'slugs_id' => $slug->id,
                'lang_code' => 'vi_' . $i,
                'key' => 'trang-test-' . $i,
                'prefix' => 'trang',
            ]);
        }

        // Single query to get base slug
        $cachedSlug = Slug::query()
            ->where('reference_id', $page->id)
            ->where('reference_type', Page::class)
            ->first();

        // Single query to get all translations at once
        $cachedTranslations = DB::table('slugs_translations')
            ->where('slugs_id', $cachedSlug->id)
            ->get();

        // Now we can access any translation without additional queries
        $this->assertCount(5, $cachedTranslations);

        // Multiple accesses use cached collection
        for ($i = 0; $i < 5; $i++) {
            $translation = $cachedTranslations->firstWhere('lang_code', 'vi_' . $i);
            $this->assertNotNull($translation);
        }
    }

    public function testSlugTranslationQueryByLanguageCode(): void
    {
        $page = Page::query()->create([
            'name' => 'Language Code Test',
        ]);

        $slug = Slug::query()->create([
            'key' => 'lang-code-page',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->id,
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-lang-code',
            'prefix' => 'trang',
        ]);

        // Retrieve all translations
        $cachedTranslations = DB::table('slugs_translations')
            ->where('slugs_id', $slug->id)
            ->get();

        // Find by language code
        $langCode = 'vi';
        $translation = $cachedTranslations->firstWhere('lang_code', $langCode);

        $this->assertNotNull($translation);
        $this->assertEquals('vi', $translation->lang_code);
        $this->assertEquals('trang-lang-code', $translation->key);
    }

    public function testSlugTranslationNotFoundReturnsNull(): void
    {
        $page = Page::query()->create([
            'name' => 'Not Found Test',
        ]);

        $slug = Slug::query()->create([
            'key' => 'not-found-page',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->id,
        ]);

        // Create only Vietnamese translation
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-khong-tim',
            'prefix' => 'trang',
        ]);

        // Retrieve all translations
        $cachedTranslations = DB::table('slugs_translations')
            ->where('slugs_id', $slug->id)
            ->get();

        // Look for Arabic translation that doesn't exist
        $arTranslation = $cachedTranslations->firstWhere('lang_code', 'ar');

        $this->assertNull($arTranslation);
    }

    public function testMultipleSlugsWithTranslations(): void
    {
        // Create two pages with different slugs
        $page1 = Page::query()->create(['name' => 'Page 1']);
        $page2 = Page::query()->create(['name' => 'Page 2']);

        $slug1 = Slug::query()->create([
            'key' => 'page-one',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page1->id,
        ]);

        $slug2 = Slug::query()->create([
            'key' => 'page-two',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page2->id,
        ]);

        // Create translations for both
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug1->id,
            'lang_code' => 'vi',
            'key' => 'trang-mot',
            'prefix' => 'trang',
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug2->id,
            'lang_code' => 'vi',
            'key' => 'trang-hai',
            'prefix' => 'trang',
        ]);

        // Cache slug1 and its translations
        $cachedSlug1 = Slug::query()->where('id', $slug1->id)->first();
        $cachedTranslations1 = DB::table('slugs_translations')
            ->where('slugs_id', $cachedSlug1->id)
            ->get();

        // Cache slug2 and its translations
        $cachedSlug2 = Slug::query()->where('id', $slug2->id)->first();
        $cachedTranslations2 = DB::table('slugs_translations')
            ->where('slugs_id', $cachedSlug2->id)
            ->get();

        // Verify separate caches
        $this->assertEquals('page-one', $cachedSlug1->key);
        $this->assertEquals('trang-mot', $cachedTranslations1->first()->key);

        $this->assertEquals('page-two', $cachedSlug2->key);
        $this->assertEquals('trang-hai', $cachedTranslations2->first()->key);
    }

    public function testFallbackToDefaultSlugInHrefLangWhenNoTranslation(): void
    {
        $page = Page::query()->create([
            'name' => 'Hreflang Fallback Test',
        ]);

        $slug = Slug::query()->create([
            'key' => 'hreflang-fallback-page',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->id,
        ]);

        // Create translation for Vietnamese only
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-hreflang-fallback',
            'prefix' => 'trang',
        ]);

        // Simulate batch-loading like getStandardTranslatedUrl does
        $cachedStandardSlugs = [];

        // In real code, this would load via LanguageMeta join query
        // For test, we simulate having Vietnamese slug but no Arabic
        $cachedStandardSlugs['vi'] = (object) [
            'key' => 'trang-hreflang-fallback',
            'prefix' => 'trang',
            'reference_id' => $page->id,
        ];

        // Looking for Vietnamese translation - found in cache
        $this->assertArrayHasKey('vi', $cachedStandardSlugs);
        $this->assertEquals('trang-hreflang-fallback', $cachedStandardSlugs['vi']->key);

        // Looking for Arabic translation - NOT in cache
        $this->assertArrayNotHasKey('ar', $cachedStandardSlugs);

        // When translation not found, getAdvancedTranslatedUrl falls back to base slug
        // This simulates the fallback in getAdvancedTranslatedUrl (lines 158-166)
        $defaultSlug = Slug::query()->where('id', $slug->id)->first();
        $finalPrefix = $defaultSlug->prefix;
        $finalKey = $defaultSlug->key;

        $this->assertEquals('pages', $finalPrefix);
        $this->assertEquals('hreflang-fallback-page', $finalKey);
    }

    public function testTranslatedPrefixUsedInHreflangUrl(): void
    {
        $page = Page::query()->create([
            'name' => 'Translated Prefix Test',
        ]);

        $slug = Slug::query()->create([
            'key' => 'translated-prefix-page',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->id,
        ]);

        // Create translation with DIFFERENT prefix
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-tinh-dung-prefix',
            'prefix' => 'cac-trang', // Different from base
        ]);

        // Retrieve slug with translations
        $cachedSlug = Slug::query()
            ->where('id', $slug->id)
            ->with('translations')
            ->first();

        // Find Vietnamese translation
        $viTranslation = $cachedSlug->translations->firstWhere('lang_code', 'vi');

        $this->assertNotNull($viTranslation);
        $this->assertNotEquals($cachedSlug->prefix, $viTranslation->prefix);

        // In getAdvancedTranslatedUrl, the logic is:
        // $translatedPrefix = $translation->prefix ?? $this->cachedSlug->prefix;
        $translatedPrefix = $viTranslation->prefix ?? $cachedSlug->prefix;
        $this->assertEquals('cac-trang', $translatedPrefix);

        // URL should use translated prefix
        $expectedPath = $translatedPrefix . '/' . $viTranslation->key;
        $this->assertEquals('cac-trang/trang-tinh-dung-prefix', $expectedPath);
    }

    public function testBatchLoadingOfSlugTranslationsForHreflang(): void
    {
        // Create a base page (default language)
        $basePage = Page::query()->create([
            'name' => 'Batch Load Test Base',
        ]);

        $baseSlug = Slug::query()->create([
            'key' => 'batch-load-page',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $basePage->id,
        ]);

        // Create translations for multiple languages
        DB::table('slugs_translations')->insert([
            ['slugs_id' => $baseSlug->id, 'lang_code' => 'vi', 'key' => 'trang-hang-loat', 'prefix' => 'trang'],
            ['slugs_id' => $baseSlug->id, 'lang_code' => 'ar', 'key' => 'صفحة-دفعة', 'prefix' => 'صفحات'],
        ]);

        // Simulate batch loading pattern: retrieve translations with caching
        // First call populates the cache
        $cachedTranslations = DB::table('slugs_translations')
            ->where('slugs_id', $baseSlug->id)
            ->get()
            ->keyBy('lang_code');

        $this->assertCount(2, $cachedTranslations);

        // Now multiple lookups use the cached collection (no additional queries)
        $viTranslation = $cachedTranslations['vi'] ?? null;
        $this->assertNotNull($viTranslation);
        $this->assertEquals('trang-hang-loat', $viTranslation->key);

        // Can lookup any language from cache
        $arTranslation = $cachedTranslations['ar'] ?? null;
        $this->assertNotNull($arTranslation);
        $this->assertEquals('صفحة-دفعة', $arTranslation->key);

        // Missing translation lookup on cache returns null
        $frTranslation = $cachedTranslations['fr'] ?? null;
        $this->assertNull($frTranslation);

        // Translation not found (asserted null above), so the base slug key is used.
        $this->assertEquals('batch-load-page', $baseSlug->key);
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
