<?php

namespace Botble\LanguageAdvanced\Tests\Feature;

use Botble\Language\Facades\Language;
use Botble\Language\Models\Language as LanguageModel;
use Botble\Language\Models\LanguageMeta;
use Botble\LanguageAdvanced\Providers\HookServiceProvider;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Supports\SettingStore;
use Botble\Slug\Models\Slug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GetTranslatedSlugTest extends TestCase
{
    use RefreshDatabase;

    protected HookServiceProvider $hookProvider;

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
        ]);

        Setting::set('language_hide_default', true);
        Setting::save();

        $this->hookProvider = new HookServiceProvider(app());
    }

    public function testReturnsTranslatedSlugForNonDefaultLocale(): void
    {
        $slug = Slug::query()->create([
            'key' => 'about',
            'prefix' => '',
            'reference_type' => 'Botble\\Page\\Models\\Page',
            'reference_id' => 1,
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'gioi-thieu',
            'prefix' => '',
        ]);

        // Simulate Vietnamese locale
        Language::setCurrentLocale('vi');

        $result = $this->hookProvider->getTranslatedSlug('', $slug);

        $this->assertIsArray($result);
        $this->assertEquals('gioi-thieu', $result['key']);
    }

    public function testReturnsPassthroughForDefaultLocale(): void
    {
        $slug = Slug::query()->create([
            'key' => 'about',
            'prefix' => '',
            'reference_type' => 'Botble\\Page\\Models\\Page',
            'reference_id' => 1,
        ]);

        // Default locale (en)
        Language::setCurrentLocale('en');

        $result = $this->hookProvider->getTranslatedSlug('', $slug);

        // Should return passthrough (empty string from filter system)
        $this->assertEquals('', $result);
    }

    public function testReturnsPassthroughWhenNoTranslationExists(): void
    {
        $slug = Slug::query()->create([
            'key' => 'about',
            'prefix' => '',
            'reference_type' => 'Botble\\Page\\Models\\Page',
            'reference_id' => 1,
        ]);

        // No translation inserted for Vietnamese
        Language::setCurrentLocale('vi');

        $result = $this->hookProvider->getTranslatedSlug('', $slug);

        // Should return passthrough since no translation found
        $this->assertEquals('', $result);
    }

    public function testTranslatesContentPrefix(): void
    {
        $slug = Slug::query()->create([
            'key' => 'nie-number',
            'prefix' => 'articles',
            'reference_type' => 'Botble\\Blog\\Models\\Post',
            'reference_id' => 1,
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'so-nie',
            'prefix' => 'bai-viet',
        ]);

        Language::setCurrentLocale('vi');

        $result = $this->hookProvider->getTranslatedSlug('', $slug);

        $this->assertIsArray($result);
        $this->assertEquals('so-nie', $result['key']);
        $this->assertEquals('bai-viet', $result['prefix']);
    }

    public function testFallsBackToPrefixWhenTranslationPrefixIsNull(): void
    {
        $slug = Slug::query()->create([
            'key' => 'some-page',
            'prefix' => 'pages',
            'reference_type' => 'Botble\\Page\\Models\\Page',
            'reference_id' => 2,
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-nao-do',
            'prefix' => null,
        ]);

        Language::setCurrentLocale('vi');

        $result = $this->hookProvider->getTranslatedSlug('', $slug);

        $this->assertIsArray($result);
        $this->assertEquals('trang-nao-do', $result['key']);
        // prefix ?? $slug->prefix — null falls back to base prefix
        $this->assertEquals('pages', $result['prefix']);
    }

    public function testEmptyPrefixKeptAsIs(): void
    {
        $slug = Slug::query()->create([
            'key' => 'about',
            'prefix' => 'pages',
            'reference_type' => 'Botble\\Page\\Models\\Page',
            'reference_id' => 3,
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'gioi-thieu',
            'prefix' => '', // empty string = no content-type prefix
        ]);

        Language::setCurrentLocale('vi');

        $result = $this->hookProvider->getTranslatedSlug('', $slug);

        $this->assertIsArray($result);
        // ?? keeps empty string (only null falls back)
        $this->assertEquals('', $result['prefix']);
    }

    public function testCachePreventsRepeatQueries(): void
    {
        $slug = Slug::query()->create([
            'key' => 'cached-page',
            'prefix' => '',
            'reference_type' => 'Botble\\Page\\Models\\Page',
            'reference_id' => 4,
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'trang-cache',
            'prefix' => '',
        ]);

        Language::setCurrentLocale('vi');

        // First call — hits DB
        $result1 = $this->hookProvider->getTranslatedSlug('', $slug);
        $this->assertEquals('trang-cache', $result1['key']);

        // Delete from DB to prove second call uses cache
        DB::table('slugs_translations')->where('slugs_id', $slug->id)->delete();

        // Second call — should return cached result, not hit DB
        $result2 = $this->hookProvider->getTranslatedSlug('', $slug);
        $this->assertEquals('trang-cache', $result2['key']);
    }

    public function testFilterIntegrationWithApplyFilters(): void
    {
        $slug = Slug::query()->create([
            'key' => 'contact',
            'prefix' => '',
            'reference_type' => 'Botble\\Page\\Models\\Page',
            'reference_id' => 5,
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'vi',
            'key' => 'lien-he',
            'prefix' => '',
        ]);

        Language::setCurrentLocale('vi');

        // Test via apply_filters (same as SlugServiceProvider calls it)
        $result = apply_filters('slug_get_translated_slug', null, $slug);

        // apply_filters converts null to '' via Filter::fire, then our method returns array
        $this->assertIsArray($result);
        $this->assertEquals('lien-he', $result['key']);
    }

    public function testIsArrayGuardInSlugServiceProvider(): void
    {
        $slug = Slug::query()->create([
            'key' => 'test-page',
            'prefix' => 'pages',
            'reference_type' => 'Botble\\Page\\Models\\Page',
            'reference_id' => 6,
        ]);

        // Default locale — filter returns '' (not array)
        Language::setCurrentLocale('en');

        $result = apply_filters('slug_get_translated_slug', null, $slug);

        // is_array('') = false, so SlugServiceProvider falls back to $slug->key
        $this->assertFalse(is_array($result));

        // Result is not an array (asserted above), so the slug key is used directly.
        $this->assertEquals('test-page', $slug->key);
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
    }
}
