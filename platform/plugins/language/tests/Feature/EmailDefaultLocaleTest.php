<?php

namespace Botble\Language\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Base\Supports\EmailHandler;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmailDefaultLocaleTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::forceSet('email_default_locale', '');
        Setting::save();
    }

    public function test_returns_app_locale_when_no_setting_and_no_language_plugin(): void
    {
        config(['app.locale' => 'en']);

        $locale = EmailHandler::getDefaultEmailLocale();

        $this->assertEquals('en', $locale);
    }

    public function test_returns_configured_locale_when_setting_is_set(): void
    {
        Setting::forceSet('email_default_locale', 'ar');
        Setting::save();

        $locale = EmailHandler::getDefaultEmailLocale();

        $this->assertEquals('ar', $locale);
    }

    public function test_returns_app_locale_when_setting_is_empty(): void
    {
        Setting::forceSet('email_default_locale', '');
        Setting::save();

        config(['app.locale' => 'fr']);

        $locale = EmailHandler::getDefaultEmailLocale();

        // Without language plugin active, should fall back to config app.locale
        if (! function_exists('is_plugin_active') || ! is_plugin_active('language')) {
            $this->assertEquals('fr', $locale);
        } else {
            // With language plugin, returns its default or config fallback
            $this->assertNotEmpty($locale);
        }
    }

    public function test_returns_nonempty_string_always(): void
    {
        Setting::forceSet('email_default_locale', '');
        Setting::save();

        $locale = EmailHandler::getDefaultEmailLocale();

        $this->assertNotEmpty($locale);
    }

    public function test_setting_null_falls_back_to_default(): void
    {
        Setting::forceSet('email_default_locale', null);
        Setting::save();

        config(['app.locale' => 'de']);

        $locale = EmailHandler::getDefaultEmailLocale();

        // Should not return null or empty
        $this->assertNotEmpty($locale);
    }

    public function test_respects_configured_locale_over_app_default(): void
    {
        config(['app.locale' => 'en']);

        Setting::forceSet('email_default_locale', 'ja');
        Setting::save();

        $locale = EmailHandler::getDefaultEmailLocale();

        $this->assertEquals('ja', $locale);
    }
}
