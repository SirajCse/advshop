<?php

namespace Botble\LanguageAdvanced\Tests\Feature;

use Botble\Language\Facades\Language;
use Botble\Language\LanguageManager;
use Botble\Language\Models\Language as LanguageModel;
use Botble\Language\Models\LanguageMeta;
use Botble\LanguageAdvanced\Providers\HookServiceProvider;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Supports\SettingStore;
use Botble\Slug\Models\Slug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TranslateSlugSwitcherUrlQueryParamsTest extends TestCase
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

    public function testTranslateSlugSwitcherUrlPreservesQueryParamsWhenSwitchingLanguage(): void
    {
        $slug = Slug::query()->create([
            'key' => 'jobs',
            'prefix' => '',
            'reference_type' => 'Botble\\Page\\Models\\Page',
            'reference_id' => 1,
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'ar',
            'key' => 'وظائف',
            'prefix' => '',
        ]);

        $this->simulateRequest('http://localhost/ar/وظائف', ['city_id' => '5'], 'وظائف');

        // Set the Facade locale to Arabic (resolveCurrentSlug uses Language::getCurrentLocale())
        Language::setLocale('ar');

        [$hookProvider, $manager] = $this->createProviderAndManager();
        $manager->setLocale('ar');

        $result = $hookProvider->translateSlugSwitcherUrl(
            'http://localhost/jobs',
            'en',
            'en_US',
            $manager
        );

        $this->assertStringContainsString('city_id=5', $result, 'Query params should be preserved when switching language');
        $this->assertStringContainsString('jobs', $result, 'URL should contain the translated slug');
    }

    public function testTranslateSlugSwitcherUrlPreservesMultipleQueryParams(): void
    {
        $slug = Slug::query()->create([
            'key' => 'jobs',
            'prefix' => '',
            'reference_type' => 'Botble\\Page\\Models\\Page',
            'reference_id' => 1,
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'ar',
            'key' => 'وظائف',
            'prefix' => '',
        ]);

        $this->simulateRequest('http://localhost/ar/وظائف', [
            'city_id' => '5',
            'keyword' => 'developer',
        ], 'وظائف');

        Language::setLocale('ar');

        [$hookProvider, $manager] = $this->createProviderAndManager();
        $manager->setLocale('ar');

        $result = $hookProvider->translateSlugSwitcherUrl(
            'http://localhost/jobs',
            'en',
            'en_US',
            $manager
        );

        $this->assertStringContainsString('city_id=5', $result);
        $this->assertStringContainsString('keyword=developer', $result);
    }

    public function testTranslateSlugSwitcherUrlWithoutQueryParamsReturnsCleanUrl(): void
    {
        $slug = Slug::query()->create([
            'key' => 'about-us',
            'prefix' => '',
            'reference_type' => 'Botble\\Page\\Models\\Page',
            'reference_id' => 2,
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'ar',
            'key' => 'من-نحن',
            'prefix' => '',
        ]);

        $this->simulateRequest('http://localhost/ar/من-نحن', [], 'من-نحن');

        Language::setLocale('ar');

        [$hookProvider, $manager] = $this->createProviderAndManager();
        $manager->setLocale('ar');

        $result = $hookProvider->translateSlugSwitcherUrl(
            'http://localhost/about-us',
            'en',
            'en_US',
            $manager
        );

        $this->assertStringNotContainsString('?', $result, 'URL without query params should not contain ?');
    }

    public function testTranslateSlugSwitcherUrlReturnsOriginalUrlWhenNoSlugFound(): void
    {
        $this->simulateRequest('http://localhost/ar/nonexistent', ['city_id' => '5'], 'nonexistent');

        Language::setLocale('ar');

        [$hookProvider, $manager] = $this->createProviderAndManager();

        $originalUrl = 'http://localhost/en/some-page?city_id=5';

        $result = $hookProvider->translateSlugSwitcherUrl(
            $originalUrl,
            'en',
            'en_US',
            $manager
        );

        $this->assertEquals($originalUrl, $result, 'Should return original URL when no slug record found');
    }

    public function testTranslateSlugSwitcherUrlPreservesQueryParamsFromDefaultToNonDefault(): void
    {
        $slug = Slug::query()->create([
            'key' => 'jobs',
            'prefix' => '',
            'reference_type' => 'Botble\\Page\\Models\\Page',
            'reference_id' => 1,
        ]);

        DB::table('slugs_translations')->insert([
            'slugs_id' => $slug->id,
            'lang_code' => 'ar',
            'key' => 'وظائف',
            'prefix' => '',
        ]);

        // Simulate being on English (default) page with query params
        $this->simulateRequest('http://localhost/jobs', ['city_id' => '3'], 'jobs');

        Language::setLocale('en');

        [$hookProvider, $manager] = $this->createProviderAndManager();
        $manager->setLocale('en');

        // Switch to Arabic
        $result = $hookProvider->translateSlugSwitcherUrl(
            'http://localhost/ar/jobs',
            'ar',
            'ar',
            $manager
        );

        $this->assertStringContainsString('city_id=3', $result, 'Query params should be preserved when switching from default to non-default language');
    }

    /**
     * Create fresh HookServiceProvider and LanguageManager after request binding.
     * LanguageManager captures the request at construction time, so it must be
     * re-created after the test binds a new request to the container.
     */
    protected function createProviderAndManager(): array
    {
        $manager = new LanguageManager();
        $hookProvider = new HookServiceProvider($this->app);

        return [$hookProvider, $manager];
    }

    protected function simulateRequest(string $url, array $query, string $slugParam): void
    {
        $request = Request::create($url, 'GET', $query);
        $this->app->instance('request', $request);

        $route = new \Illuminate\Routing\Route('GET', '{slug}', fn () => null);
        $route->bind($request);
        $route->setParameter('slug', $slugParam);

        $router = Route::getFacadeRoot();
        $reflection = new \ReflectionProperty($router, 'current');
        $reflection->setAccessible(true);
        $reflection->setValue($router, $route);
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
            'lang_name' => 'Arabic',
            'lang_locale' => 'ar',
            'lang_is_default' => false,
            'lang_code' => 'ar',
            'lang_is_rtl' => true,
            'lang_flag' => 'sa',
            'lang_order' => 1,
        ]);
    }
}
