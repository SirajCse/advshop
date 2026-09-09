<?php

namespace Botble\Language\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Language\Facades\Language;
use Botble\Language\Http\Middleware\ApiLanguageMiddleware;
use Botble\Language\Http\Middleware\LocaleSessionRedirect;
use Botble\Language\Http\Middleware\LocalizationRedirectFilter;
use Botble\Language\Models\Language as LanguageModel;
use Botble\Language\Models\LanguageMeta;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LanguageMiddlewareTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(SettingStore::class)->forgetAll();

        LanguageModel::query()->delete();
        LanguageMeta::query()->delete();

        $this->createLanguages();
        $this->setLocales();
    }

    protected function createLanguages(): void
    {
        LanguageModel::query()->create([
            'lang_name' => 'Arabic',
            'lang_locale' => 'ar',
            'lang_is_default' => true,
            'lang_code' => 'ar',
            'lang_is_rtl' => true,
            'lang_flag' => 'sa',
            'lang_order' => 0,
        ]);

        LanguageModel::query()->create([
            'lang_name' => 'English',
            'lang_locale' => 'en',
            'lang_is_default' => false,
            'lang_code' => 'en_US',
            'lang_is_rtl' => false,
            'lang_flag' => 'us',
            'lang_order' => 1,
        ]);
    }

    protected function setLocales(): void
    {
        Language::setSupportedLocales([
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

        Language::setDefaultLocale();
    }

    public function testApiMiddlewareSetsLocaleFromQueryParam(): void
    {
        $request = Request::create('/api/v1/products?language=en', 'GET');
        $middleware = new ApiLanguageMiddleware();

        $middleware->handle($request, function () {
            $this->assertEquals('en', app()->getLocale());

            return new Response();
        });
    }

    public function testApiMiddlewareSetsLocaleFromHeader(): void
    {
        $request = Request::create('/api/v1/products', 'GET');
        $request->headers->set('X-LANGUAGE', 'en');
        $middleware = new ApiLanguageMiddleware();

        $middleware->handle($request, function () {
            $this->assertEquals('en', app()->getLocale());

            return new Response();
        });
    }

    public function testApiMiddlewareQueryParamTakesPrecedence(): void
    {
        $request = Request::create('/api/v1/products?language=ar', 'GET');
        $request->headers->set('X-LANGUAGE', 'en');
        $middleware = new ApiLanguageMiddleware();

        $middleware->handle($request, function () {
            $this->assertEquals('ar', app()->getLocale());

            return new Response();
        });
    }

    public function testApiMiddlewareIgnoresUnsupportedLocale(): void
    {
        $originalLocale = app()->getLocale();
        $request = Request::create('/api/v1/products?language=xx', 'GET');
        $middleware = new ApiLanguageMiddleware();

        $middleware->handle($request, function () use ($originalLocale) {
            $this->assertEquals($originalLocale, app()->getLocale());

            return new Response();
        });
    }

    public function testApiMiddlewarePassesThroughWithoutLanguageParam(): void
    {
        $originalLocale = app()->getLocale();
        $request = Request::create('/api/v1/products', 'GET');
        $middleware = new ApiLanguageMiddleware();

        $middleware->handle($request, function () use ($originalLocale) {
            $this->assertEquals($originalLocale, app()->getLocale());

            return new Response();
        });
    }

    public function testLocalizationRedirectFilterRedirectsDefaultLocaleWhenHidden(): void
    {
        Setting::set('language_hide_default', true);
        Setting::save();
        $this->setLocales();

        $request = Request::create('/ar/some-page', 'GET');
        $middleware = new LocalizationRedirectFilter();

        $response = $middleware->handle($request, function () {
            return new Response('ok');
        });

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testLocalizationRedirectFilterDoesNotRedirectNonDefaultLocale(): void
    {
        Setting::set('language_hide_default', true);
        Setting::save();
        $this->setLocales();

        $request = Request::create('/en/some-page', 'GET');
        $middleware = new LocalizationRedirectFilter();

        $response = $middleware->handle($request, function () {
            return new Response('ok');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLocalizationRedirectFilterDoesNotRedirectWhenHideDefaultDisabled(): void
    {
        Setting::set('language_hide_default', false);
        Setting::save();
        $this->setLocales();

        $request = Request::create('/ar/some-page', 'GET');
        $middleware = new LocalizationRedirectFilter();

        $response = $middleware->handle($request, function () {
            return new Response('ok');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLocaleSessionRedirectSetsSessionFromUrl(): void
    {
        $request = Request::create('/en/some-page', 'GET');
        $middleware = new LocaleSessionRedirect();

        $middleware->handle($request, function () {
            $this->assertEquals('en', session('language'));

            return new Response('ok');
        });
    }

    public function testLocaleSessionRedirectTracksPreviousLanguage(): void
    {
        session(['language' => 'ar']);

        $request = Request::create('/en/some-page', 'GET');
        $middleware = new LocaleSessionRedirect();

        $middleware->handle($request, function () {
            $this->assertEquals('ar', session('previous_language'));
            $this->assertEquals('en', session('language'));

            return new Response('ok');
        });
    }

    public function testLocaleSessionRedirectAutoDetectsWhenDefaultLocaleVisible(): void
    {
        Setting::set('language_hide_default', false);
        Setting::set('language_auto_detect_user_language', true);
        Setting::save();
        $this->setLocales();

        session()->forget('language');

        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9');
        $middleware = new LocaleSessionRedirect();

        $middleware->handle($request, function () {
            $this->assertEquals('en', session('language'));

            return new Response('ok');
        });
    }

    public function testLocaleSessionRedirectAutoDetectsWhenDefaultLocaleHidden(): void
    {
        Setting::set('language_hide_default', true);
        Setting::set('language_auto_detect_user_language', true);
        Setting::save();
        $this->setLocales();

        session()->forget('language');

        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9');
        $middleware = new LocaleSessionRedirect();

        $response = $middleware->handle($request, function () {
            return new Response('ok');
        });

        $this->assertEquals('en', session('language'));
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testLocaleSessionRedirectSkipsAutoDetectWhenSettingDisabled(): void
    {
        Setting::set('language_hide_default', false);
        Setting::set('language_auto_detect_user_language', false);
        Setting::save();
        $this->setLocales();

        session()->forget('language');

        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9');
        $middleware = new LocaleSessionRedirect();

        $middleware->handle($request, function () {
            $this->assertEquals('ar', session('language'));

            return new Response('ok');
        });
    }
}
