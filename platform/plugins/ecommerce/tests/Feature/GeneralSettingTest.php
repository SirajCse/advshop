<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\Base\Supports\BaseTestCase;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class GeneralSettingTest extends BaseTestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
    }

    protected function createAdminUser(): User
    {
        Schema::disableForeignKeyConstraints();
        User::query()->truncate();

        $user = new User();
        $user->forceFill([
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'super_user' => 1,
            'manage_supers' => 1,
        ]);
        $user->save();

        app(ActivateUserService::class)->activate($user);

        return $user;
    }

    public function test_can_access_general_settings_page(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->get(route('ecommerce.settings.general'));

        $response->assertOk();
    }

    public function test_can_save_admin_notification_emails(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->put(route('ecommerce.settings.general.update'), [
            'store_name' => 'Test Store',
            'admin_notification_email' => [
                ['0' => ['value' => 'admin1@store.com']],
                ['0' => ['value' => 'admin2@store.com']],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $savedEmails = json_decode(setting('ecommerce_admin_notification_email'), true);

        $this->assertIsArray($savedEmails);
        $this->assertContains('admin1@store.com', $savedEmails);
        $this->assertContains('admin2@store.com', $savedEmails);
    }

    public function test_can_save_empty_admin_notification_emails(): void
    {
        $this->actingAs($this->admin, 'web');

        Setting::forceSet('ecommerce_admin_notification_email', json_encode(['old@store.com']));
        Setting::save();

        $response = $this->put(route('ecommerce.settings.general.update'), [
            'store_name' => 'Test Store',
            'admin_notification_email' => [],
        ]);

        $response->assertSessionHasNoErrors();

        $savedEmails = json_decode(setting('ecommerce_admin_notification_email'), true);

        $this->assertEmpty($savedEmails);
    }

    public function test_filters_empty_email_values(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->put(route('ecommerce.settings.general.update'), [
            'store_name' => 'Test Store',
            'admin_notification_email' => [
                ['0' => ['value' => 'valid@store.com']],
                ['0' => ['value' => '']],
                ['0' => ['value' => 'another@store.com']],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $savedEmails = json_decode(setting('ecommerce_admin_notification_email'), true);

        $this->assertIsArray($savedEmails);
        $this->assertCount(2, $savedEmails);
        $this->assertContains('valid@store.com', $savedEmails);
        $this->assertContains('another@store.com', $savedEmails);
    }

    public function test_can_save_store_information(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->put(route('ecommerce.settings.general.update'), [
            'store_name' => 'My Test Store',
            'store_company' => 'Test Company Inc.',
            'store_phone' => '+1 234 567 890',
            'store_email' => 'contact@teststore.com',
            'store_address' => '123 Main Street',
            'store_vat_number' => 'VAT123456',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertEquals('My Test Store', setting('ecommerce_store_name'));
        $this->assertEquals('Test Company Inc.', setting('ecommerce_store_company'));
        $this->assertEquals('+1 234 567 890', setting('ecommerce_store_phone'));
        $this->assertEquals('contact@teststore.com', setting('ecommerce_store_email'));
        $this->assertEquals('123 Main Street', setting('ecommerce_store_address'));
        $this->assertEquals('VAT123456', setting('ecommerce_store_vat_number'));
    }
}
