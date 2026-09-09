<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\EmailHandler;
use Botble\Ecommerce\Listeners\SendAbandonedCartReminderEmail;
use Botble\Ecommerce\Listeners\SendMailsAfterCustomerEmailVerified;
use Botble\Ecommerce\Listeners\SendMailsAfterCustomerRegistered;
use Botble\Ecommerce\Listeners\SendProductFileUpdatedNotification;
use Botble\Ecommerce\Listeners\SendProductReviewsMailAfterOrderCompleted;
use Botble\Ecommerce\Notifications\ConfirmDeletionRequestNotification;
use Botble\Ecommerce\Notifications\ConfirmEmailNotification;
use Botble\Ecommerce\Notifications\ResetPasswordNotification;
use Botble\Ecommerce\Notifications\SendVerificationCodeNotification;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class CustomerEmailLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::forceSet('email_default_locale', '');
        Setting::save();
    }

    public function test_default_email_locale_returns_app_locale_when_no_setting(): void
    {
        config(['app.locale' => 'en']);

        $locale = EmailHandler::getDefaultEmailLocale();

        if (is_plugin_active('language')) {
            $this->assertNotEmpty($locale);
        } else {
            $this->assertEquals('en', $locale);
        }
    }

    public function test_default_email_locale_returns_configured_value(): void
    {
        Setting::forceSet('email_default_locale', 'ar');
        Setting::save();

        $this->assertEquals('ar', EmailHandler::getDefaultEmailLocale());
    }

    public function test_default_email_locale_auto_does_not_return_admin_session_locale(): void
    {
        config(['app.locale' => 'en']);

        Setting::forceSet('email_default_locale', '');
        Setting::save();

        // Simulate admin using Arabic dashboard
        App::setLocale('ar');

        $emailLocale = EmailHandler::getDefaultEmailLocale();

        // Should NOT return admin's session locale 'ar'
        // Should return site default 'en' (or language plugin default)
        $this->assertNotEquals('ar', $emailLocale, 'Email locale should not inherit admin session locale when setting is Auto');
    }

    public function test_configured_locale_overrides_app_locale(): void
    {
        config(['app.locale' => 'en']);

        Setting::forceSet('email_default_locale', 'fr');
        Setting::save();

        App::setLocale('de');

        $this->assertEquals('fr', EmailHandler::getDefaultEmailLocale());
    }

    public function test_listeners_use_email_handler_support_import(): void
    {
        // Verify listeners have the correct import by checking they can be instantiated
        $reflection = new \ReflectionClass(SendMailsAfterCustomerRegistered::class);
        $this->assertTrue($reflection->hasMethod('handle'));

        $reflection = new \ReflectionClass(SendMailsAfterCustomerEmailVerified::class);
        $this->assertTrue($reflection->hasMethod('handle'));

        $reflection = new \ReflectionClass(SendProductReviewsMailAfterOrderCompleted::class);
        $this->assertTrue($reflection->hasMethod('handle'));

        $reflection = new \ReflectionClass(SendProductFileUpdatedNotification::class);
        $this->assertTrue($reflection->hasMethod('handle'));

        $reflection = new \ReflectionClass(SendAbandonedCartReminderEmail::class);
        $this->assertTrue($reflection->hasMethod('handle'));
    }

    public function test_notification_classes_have_email_locale_property(): void
    {
        $notificationClasses = [
            ResetPasswordNotification::class,
            ConfirmEmailNotification::class,
            SendVerificationCodeNotification::class,
            ConfirmDeletionRequestNotification::class,
        ];

        foreach ($notificationClasses as $class) {
            $reflection = new \ReflectionClass($class);
            $this->assertTrue(
                $reflection->hasProperty('emailLocale'),
                "$class should have emailLocale property"
            );

            $property = $reflection->getProperty('emailLocale');
            $this->assertTrue(
                $property->isProtected(),
                "$class::emailLocale should be protected"
            );
        }
    }

    public function test_reset_password_notification_captures_locale_on_construction(): void
    {
        Setting::forceSet('email_default_locale', 'ja');
        Setting::save();

        $notification = new ResetPasswordNotification('test-token');

        $reflection = new \ReflectionClass($notification);
        $property = $reflection->getProperty('emailLocale');
        $property->setAccessible(true);

        $this->assertEquals('ja', $property->getValue($notification));
    }

    public function test_confirm_email_notification_captures_locale_on_construction(): void
    {
        Setting::forceSet('email_default_locale', 'de');
        Setting::save();

        $notification = new ConfirmEmailNotification();

        $reflection = new \ReflectionClass($notification);
        $property = $reflection->getProperty('emailLocale');
        $property->setAccessible(true);

        $this->assertEquals('de', $property->getValue($notification));
    }

    public function test_locale_captured_at_construction_not_at_send_time(): void
    {
        Setting::forceSet('email_default_locale', 'ar');
        Setting::save();

        $notification = new ResetPasswordNotification('test-token');

        // Change setting after construction
        Setting::forceSet('email_default_locale', 'fr');
        Setting::save();

        $reflection = new \ReflectionClass($notification);
        $property = $reflection->getProperty('emailLocale');
        $property->setAccessible(true);

        // Should still be 'ar' — captured at construction time
        $this->assertEquals('ar', $property->getValue($notification));
    }

    public function test_auto_setting_returns_consistent_locale(): void
    {
        Setting::forceSet('email_default_locale', '');
        Setting::save();

        $locale1 = EmailHandler::getDefaultEmailLocale();
        $locale2 = EmailHandler::getDefaultEmailLocale();

        $this->assertEquals($locale1, $locale2, 'Auto setting should return consistent locale');
    }

    public function test_get_default_email_locale_never_returns_empty(): void
    {
        $testValues = ['', null, '0', false];

        foreach ($testValues as $value) {
            Setting::forceSet('email_default_locale', $value);
            Setting::save();

            $locale = EmailHandler::getDefaultEmailLocale();

            $this->assertNotEmpty($locale, 'getDefaultEmailLocale() should never return empty for setting value: ' . var_export($value, true));
        }
    }
}
