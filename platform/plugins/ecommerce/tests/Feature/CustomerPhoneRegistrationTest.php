<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerPhoneRegistrationTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        setting()->forceSet('ecommerce_login_option', 'phone')->save();
        setting()->forceSet('ecommerce_is_enabled_customer_registration', 1)->save();
    }

    public function test_cannot_register_with_duplicate_phone_exact_match(): void
    {
        Customer::query()->create([
            'name' => 'Existing Customer',
            'email' => 'existing@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password123'),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $response = $this->post(route('customer.register.post'), [
            'name' => 'New Customer',
            'phone' => '+84123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_cannot_register_with_duplicate_phone_without_country_code(): void
    {
        Customer::query()->create([
            'name' => 'Existing Customer',
            'email' => 'existing@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password123'),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $response = $this->post(route('customer.register.post'), [
            'name' => 'New Customer',
            'phone' => '123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_cannot_register_when_stored_phone_ends_with_input_digits(): void
    {
        Customer::query()->create([
            'name' => 'Existing Customer',
            'email' => 'existing@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password123'),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $response = $this->post(route('customer.register.post'), [
            'name' => 'New Customer',
            'phone' => '84123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_can_register_with_different_phone_number(): void
    {
        Customer::query()->create([
            'name' => 'Existing Customer',
            'email' => 'existing@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password123'),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $response = $this->post(route('customer.register.post'), [
            'name' => 'New Customer',
            'phone' => '+84987654321',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionDoesntHaveErrors('phone');
    }
}
