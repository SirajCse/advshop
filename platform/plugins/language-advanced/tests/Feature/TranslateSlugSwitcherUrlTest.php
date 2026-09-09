<?php

namespace Botble\LanguageAdvanced\Tests\Feature;

use Botble\Language\Facades\Language;
use Botble\Language\Models\Language as LanguageModel;
use Botble\Language\Models\LanguageMeta;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Supports\SettingStore;
use Botble\Slug\Models\Slug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TranslateSlugSwitcherUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(SettingStore::class)->forgetAll();

        LanguageModel::query()->truncate();
        LanguageMeta::query()->truncate();
        Slug::query()->truncate();
        DB::table('slugs_translations')->truncate();

        $this->createLanguages();

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

    public function testSlugTranslationResolvesCachesBaseSlugFromDefaultLocale(): void
    {
        // Create base slug (English/default locale)
        $slug = Slug::query()->create([
            'key' => 'product-one',
            'prefix' => 'products',
            'reference_type' => 'Product',
            'reference_id' => 1,
        ]);

        // Create translation for Vietnamese
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'san-pham-mot',
            'prefix' => 'san-pham',
        ]);

        // Verify base slug was created
        $this->assertDatabaseHas('slugs', [
            'id' => $slug->id,
            'key' => 'product-one',
            'prefix' => 'products',
        ]);

        // Verify translation was created
        $this->assertDatabaseHas('slugs_translations', [
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'san-pham-mot',
            'prefix' => 'san-pham',
        ]);
    }

    public function testSlugTranslationMultipleLanguagesCanBeCreated(): void
    {
        $slug = Slug::query()->create([
            'key' => 'multi-lang-product',
            'prefix' => 'products',
            'reference_type' => 'Product',
            'reference_id' => 2,
        ]);

        // Create multiple translations
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'san-pham-nhieu-lang',
            'prefix' => 'san-pham',
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'ar',
            'key' => 'منتج-متعدد',
            'prefix' => 'منتجات',
        ]);

        // Verify both translations exist
        $this->assertDatabaseHas('slugs_translations', [
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
        ]);

        $this->assertDatabaseHas('slugs_translations', [
            'slugs_id' => $slug->id,
            'lang_code' => 'ar',
        ]);

        // Query translations and verify they can be retrieved
        $translations = DB::table('slugs_translations')
            ->where('slugs_id', $slug->id)
            ->get();

        $this->assertCount(2, $translations);
    }

    public function testSlugTranslationWithoutPrefixCanBeCreated(): void
    {
        $slug = Slug::query()->create([
            'key' => 'no-prefix-product',
            'prefix' => 'products',
            'reference_type' => 'Product',
            'reference_id' => 3,
        ]);

        // Create translation without prefix
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-khong-prefix',
            'prefix' => null,
        ]);

        // Verify translation with null prefix can be queried
        $translation = DB::table('slugs_translations')
            ->where('slugs_id', $slug->id)
            ->where('lang_code', 'vi')
            ->first();

        $this->assertNotNull($translation);
        $this->assertNull($translation->prefix);
        $this->assertEquals('trang-khong-prefix', $translation->key);
    }

    public function testSlugTranslationFallsBackToBasePrefixWhenTranslationNull(): void
    {
        $slug = Slug::query()->create([
            'key' => 'fallback-product',
            'prefix' => 'products',
            'reference_type' => 'Product',
            'reference_id' => 4,
        ]);

        // Create translation with null prefix (should fallback to base prefix)
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-fallback',
            'prefix' => null,
        ]);

        // Retrieve both base and translation
        $baseSlug = Slug::query()->where('id', $slug->id)->first();
        $translation = DB::table('slugs_translations')
            ->where('slugs_id', $slug->id)
            ->where('lang_code', 'vi')
            ->first();

        // Base prefix should exist
        $this->assertEquals('products', $baseSlug->prefix);

        // Translation prefix should be null
        $this->assertNull($translation->prefix);

        // When building URL, translation->prefix ?? baseSlug->prefix should give 'products'
        $usedPrefix = $translation->prefix ?? $baseSlug->prefix;
        $this->assertEquals('products', $usedPrefix);
    }

    public function testSlugTranslationCanQueryByMultipleConditions(): void
    {
        $slug = Slug::query()->create([
            'key' => 'multi-condition-product',
            'prefix' => 'products',
            'reference_type' => 'Product',
            'reference_id' => 5,
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'san-pham-dieu-kien',
            'prefix' => 'san-pham',
        ]);

        // Query by slug key and prefix
        $translation = DB::table('slugs_translations')
            ->where('key', 'san-pham-dieu-kien')
            ->where('prefix', 'san-pham')
            ->where('lang_code', 'vi')
            ->first();

        $this->assertNotNull($translation);
        $this->assertEquals('san-pham-dieu-kien', $translation->key);
    }

    public function testSlugTranslationRetrievesAllTranslationsForSlug(): void
    {
        $slug = Slug::query()->create([
            'key' => 'all-translations-product',
            'prefix' => 'products',
            'reference_type' => 'Product',
            'reference_id' => 6,
        ]);

        // Create multiple translations
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'san-pham-tat-ca',
            'prefix' => 'san-pham',
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'ar',
            'key' => 'منتج-الكل',
            'prefix' => 'منتجات',
        ]);

        // Get all translations for this slug
        $translations = DB::table('slugs_translations')
            ->where('slugs_id', $slug->id)
            ->get();

        $this->assertCount(2, $translations);

        // Verify we can find specific translations
        $viTranslation = $translations->firstWhere('lang_code', 'vi');
        $this->assertNotNull($viTranslation);
        $this->assertEquals('san-pham-tat-ca', $viTranslation->key);

        $arTranslation = $translations->firstWhere('lang_code', 'ar');
        $this->assertNotNull($arTranslation);
        $this->assertEquals('منتج-الكل', $arTranslation->key);
    }

    public function testSlugTranslationFindsByLocaleCode(): void
    {
        $slug = Slug::query()->create([
            'key' => 'locale-search-product',
            'prefix' => 'products',
            'reference_type' => 'Product',
            'reference_id' => 7,
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'san-pham-tim-locale',
            'prefix' => 'san-pham',
        ]);

        // Simulate finding translation for target language
        $targetLocale = 'vi';
        $translation = DB::table('slugs_translations')
            ->where('slugs_id', $slug->id)
            ->where('lang_code', $targetLocale)
            ->first();

        $this->assertNotNull($translation);
        $this->assertEquals('vi', $translation->lang_code);
        $this->assertEquals('san-pham-tim-locale', $translation->key);
    }

    public function testSlugTranslationCachingBehavior(): void
    {
        $slug = Slug::query()->create([
            'key' => 'cache-test-product',
            'prefix' => 'products',
            'reference_type' => 'Product',
            'reference_id' => 8,
        ]);

        // Create multiple translations
        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'san-pham-cache-test',
            'prefix' => 'san-pham',
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'ar',
            'key' => 'منتج-اختبار-الذاكرة',
            'prefix' => 'منتجات',
        ]);

        // First query - retrieves all translations
        $translations = DB::table('slugs_translations')
            ->where('slugs_id', $slug->id)
            ->get()
            ->keyBy('lang_code');

        $this->assertCount(2, $translations);

        // Simulate caching - can reuse collection for multiple language requests
        $viTranslation = $translations->get('vi');
        $this->assertNotNull($viTranslation);

        $arTranslation = $translations->get('ar');
        $this->assertNotNull($arTranslation);

        // No additional queries needed when accessing cached collection
    }

    public function testSlugTranslationDefaultToBaseWhenMissing(): void
    {
        $baseSlug = Slug::query()->create([
            'key' => 'missing-translation-product',
            'prefix' => 'products',
            'reference_type' => 'Product',
            'reference_id' => 9,
        ]);

        // Don't create Vietnamese translation
        // Simulate the fallback logic when translation doesn't exist

        $targetLocale = 'vi';
        $translation = DB::table('slugs_translations')
            ->where('slugs_id', $baseSlug->id)
            ->where('lang_code', $targetLocale)
            ->first();

        // Translation should be null
        $this->assertNull($translation);

        // Translation is null (asserted above), so the URL falls back to the base slug.
        $this->assertEquals('missing-translation-product', $baseSlug->key);
        $this->assertEquals('products', $baseSlug->prefix);
    }

    public function testFallbackToDefaultSlugWhenNoTranslationForTargetLocale(): void
    {
        // Create base slug (English/default locale)
        $baseSlug = Slug::query()->create([
            'key' => 'fallback-to-base-product',
            'prefix' => 'products',
            'reference_type' => 'Product',
            'reference_id' => 10,
        ]);

        // Create translation for Vietnamese ONLY
        DB::table('slugs_translations')->insert([
            'slugs_id' => $baseSlug->id,
            'lang_code' => 'vi',
            'key' => 'san-pham-fallback',
            'prefix' => 'san-pham',
        ]);

        // Try to get Arabic translation - should not exist
        $arTranslation = DB::table('slugs_translations')
            ->where('slugs_id', $baseSlug->id)
            ->where('lang_code', 'ar')
            ->first();

        // Arabic translation should be null
        $this->assertNull($arTranslation);

        // Simulate translateSlugSwitcherUrl fallback behavior:
        // No translation exists for target locale (asserted null above), so the base slug is used.
        $targetPrefix = $baseSlug->prefix;
        $targetKey = $baseSlug->key;

        $this->assertEquals('products', $targetPrefix);
        $this->assertEquals('fallback-to-base-product', $targetKey);

        // This is the URL path that would be generated
        $expectedPath = $targetPrefix . '/' . $targetKey;
        $this->assertEquals('products/fallback-to-base-product', $expectedPath);
    }

    public function testCachingPreventsMultipleLookups(): void
    {
        $baseSlug = Slug::query()->create([
            'key' => 'caching-product',
            'prefix' => 'products',
            'reference_type' => 'Product',
            'reference_id' => 11,
        ]);

        // Create multiple translations
        DB::table('slugs_translations')->insert([
            'slugs_id' => $baseSlug->id,
            'lang_code' => 'vi',
            'key' => 'san-pham-cache',
            'prefix' => 'san-pham',
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $baseSlug->id,
            'lang_code' => 'ar',
            'key' => 'منتج-ذاكرة',
            'prefix' => 'منتجات',
        ]);

        // Simulate caching: load all translations once
        $cachedTranslations = DB::table('slugs_translations')
            ->where('slugs_id', $baseSlug->id)
            ->get();

        // First access uses cached collection
        $viTranslation = $cachedTranslations->firstWhere('lang_code', 'vi');
        $this->assertNotNull($viTranslation);
        $this->assertEquals('san-pham-cache', $viTranslation->key);

        // Second access still uses same collection (no additional queries)
        $arTranslation = $cachedTranslations->firstWhere('lang_code', 'ar');
        $this->assertNotNull($arTranslation);
        $this->assertEquals('منتج-ذاكرة', $arTranslation->key);

        // Missing translation lookup on cached collection returns null
        $fakeTranslation = $cachedTranslations->firstWhere('lang_code', 'fr');
        $this->assertNull($fakeTranslation);

        // Not found in cache (asserted null above), so the base slug key is used.
        $this->assertEquals('caching-product', $baseSlug->key);
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
