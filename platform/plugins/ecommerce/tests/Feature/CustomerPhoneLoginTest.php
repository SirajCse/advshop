<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerPhoneLoginTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        setting()->forceSet('ecommerce_login_option', 'phone')->save();
    }

    public function test_customer_can_login_with_exact_phone_number(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password123'),
            'status' => CustomerStatusEnum::ACTIVATED,
            'confirmed_at' => now(),
        ]);

        $response = $this->post(route('customer.login.post'), [
            'email' => '+84123456789',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_customer_can_login_with_phone_without_country_code(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password123'),
            'status' => CustomerStatusEnum::ACTIVATED,
            'confirmed_at' => now(),
        ]);

        $response = $this->post(route('customer.login.post'), [
            'email' => '123456789',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_customer_can_login_with_phone_without_plus_sign(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password123'),
            'status' => CustomerStatusEnum::ACTIVATED,
            'confirmed_at' => now(),
        ]);

        $response = $this->post(route('customer.login.post'), [
            'email' => '84123456789',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_customer_cannot_login_with_wrong_password(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password123'),
            'status' => CustomerStatusEnum::ACTIVATED,
            'confirmed_at' => now(),
        ]);

        $response = $this->post(route('customer.login.post'), [
            'email' => '123456789',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest('customer');
    }

    public function test_customer_cannot_login_with_non_existing_phone(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password123'),
            'status' => CustomerStatusEnum::ACTIVATED,
            'confirmed_at' => now(),
        ]);

        $response = $this->post(route('customer.login.post'), [
            'email' => '999888777',
            'password' => 'password123',
        ]);

        $this->assertGuest('customer');
    }

    public function test_locked_customer_cannot_login(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password123'),
            'status' => CustomerStatusEnum::LOCKED,
            'confirmed_at' => now(),
        ]);

        $response = $this->post(route('customer.login.post'), [
            'email' => '123456789',
            'password' => 'password123',
        ]);

        $this->assertGuest('customer');
    }

    public function test_customer_can_login_with_local_format_when_stored_international(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84988606927',
            'password' => bcrypt('password123'),
            'status' => CustomerStatusEnum::ACTIVATED,
            'confirmed_at' => now(),
        ]);

        $response = $this->post(route('customer.login.post'), [
            'email' => '0988606927',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($customer, 'customer');
    }
}
