<?php

namespace Botble\Ecommerce\Tests\Unit;

use Botble\Ecommerce\Supports\EcommerceHelper;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class AdminNotificationEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::forceSet('admin_email', json_encode(['default@admin.com']));
        Setting::save();
    }

    public function test_returns_ecommerce_admin_emails_when_configured(): void
    {
        $emails = ['ecommerce@store.com', 'orders@store.com'];

        Setting::forceSet('ecommerce_admin_notification_email', json_encode($emails));
        Setting::save();

        $result = App::make(EcommerceHelper::class)->getAdminNotificationEmails();

        $this->assertIsArray($result);
        $this->assertEquals($emails, $result);
    }

    public function test_falls_back_to_global_admin_emails_when_not_configured(): void
    {
        Setting::forceSet('admin_email', json_encode(['fallback@admin.com']));
        Setting::forceSet('ecommerce_admin_notification_email', '');
        Setting::save();

        $result = App::make(EcommerceHelper::class)->getAdminNotificationEmails();

        $this->assertIsArray($result);
        $this->assertEquals(['fallback@admin.com'], $result);
    }

    public function test_falls_back_to_global_admin_emails_when_empty_array(): void
    {
        Setting::forceSet('admin_email', json_encode(['fallback@admin.com']));
        Setting::forceSet('ecommerce_admin_notification_email', json_encode([]));
        Setting::save();

        $result = App::make(EcommerceHelper::class)->getAdminNotificationEmails();

        $this->assertIsArray($result);
        $this->assertEquals(['fallback@admin.com'], $result);
    }

    public function test_filters_out_empty_values(): void
    {
        $emails = ['valid@store.com', '', null, 'another@store.com'];

        Setting::forceSet('ecommerce_admin_notification_email', json_encode($emails));
        Setting::save();

        $result = App::make(EcommerceHelper::class)->getAdminNotificationEmails();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContains('valid@store.com', $result);
        $this->assertContains('another@store.com', $result);
    }

    public function test_handles_single_email(): void
    {
        $emails = ['single@store.com'];

        Setting::forceSet('ecommerce_admin_notification_email', json_encode($emails));
        Setting::save();

        $result = App::make(EcommerceHelper::class)->getAdminNotificationEmails();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(['single@store.com'], $result);
    }

    public function test_handles_multiple_emails(): void
    {
        $emails = ['first@store.com', 'second@store.com', 'third@store.com', 'fourth@store.com'];

        Setting::forceSet('ecommerce_admin_notification_email', json_encode($emails));
        Setting::save();

        $result = App::make(EcommerceHelper::class)->getAdminNotificationEmails();

        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertEquals($emails, $result);
    }
}
