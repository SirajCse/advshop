<?php

namespace Botble\Ecommerce\Tests\Unit;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasPhoneNumberTraitTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_where_phone_finds_exact_match(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::query()->wherePhone('+84123456789')->first();

        $this->assertNotNull($customer);
        $this->assertEquals('John Doe', $customer->name);
    }

    public function test_where_phone_finds_match_without_country_code(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::query()->wherePhone('123456789')->first();

        $this->assertNotNull($customer);
        $this->assertEquals('John Doe', $customer->name);
    }

    public function test_where_phone_finds_match_without_plus_sign(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::query()->wherePhone('84123456789')->first();

        $this->assertNotNull($customer);
        $this->assertEquals('John Doe', $customer->name);
    }

    public function test_where_phone_returns_null_for_non_matching_phone(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::query()->wherePhone('999888777')->first();

        $this->assertNull($customer);
    }

    public function test_where_phone_returns_null_for_empty_phone(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::query()->wherePhone('')->first();

        $this->assertNull($customer);
    }

    public function test_where_phone_returns_null_for_null_phone(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::query()->wherePhone(null)->first();

        $this->assertNull($customer);
    }

    public function test_where_phone_with_input_containing_special_chars(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+15551234567',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::query()->wherePhone('(555) 123-4567')->first();

        $this->assertNotNull($customer);
        $this->assertEquals('John Doe', $customer->name);
    }

    public function test_where_phone_with_input_containing_spaces(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::query()->wherePhone('123 456 789')->first();

        $this->assertNotNull($customer);
        $this->assertEquals('John Doe', $customer->name);
    }

    public function test_normalize_phone_removes_non_digits(): void
    {
        $this->assertEquals('84123456789', Customer::normalizePhone('+84 123-456-789'));
        $this->assertEquals('15551234567', Customer::normalizePhone('+1 (555) 123-4567'));
        $this->assertEquals('123456789', Customer::normalizePhone('123456789'));
        $this->assertEquals('', Customer::normalizePhone(''));
        $this->assertEquals('', Customer::normalizePhone(null));
    }

    public function test_address_model_has_where_phone_scope(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        Address::query()->create([
            'name' => 'John Doe',
            'phone' => '+84123456789',
            'email' => 'john@example.com',
            'country' => 'VN',
            'state' => 'HCM',
            'city' => 'Ho Chi Minh',
            'address' => '123 Main St',
            'customer_id' => $customer->id,
        ]);

        $address = Address::query()->wherePhone('123456789')->first();

        $this->assertNotNull($address);
        $this->assertEquals('John Doe', $address->name);
    }

    public function test_where_phone_with_custom_column(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $found = Customer::query()->wherePhone('+84123456789', 'phone')->first();

        $this->assertNotNull($found);
        $this->assertEquals($customer->id, $found->id);
    }

    public function test_where_phone_finds_among_multiple_customers(): void
    {
        Customer::query()->create([
            'name' => 'Customer 1',
            'email' => 'customer1@example.com',
            'phone' => '+84111111111',
            'password' => bcrypt('password'),
        ]);

        Customer::query()->create([
            'name' => 'Customer 2',
            'email' => 'customer2@example.com',
            'phone' => '+84222222222',
            'password' => bcrypt('password'),
        ]);

        Customer::query()->create([
            'name' => 'Customer 3',
            'email' => 'customer3@example.com',
            'phone' => '+84333333333',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::query()->wherePhone('222222222')->first();

        $this->assertNotNull($customer);
        $this->assertEquals('Customer 2', $customer->name);
    }

    public function test_where_phone_matches_local_format_with_international_stored(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84988606927',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::query()->wherePhone('0988606927')->first();

        $this->assertNotNull($customer);
        $this->assertEquals('John Doe', $customer->name);
    }

    public function test_where_phone_matches_international_format_with_local_stored(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '0988606927',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::query()->wherePhone('+84988606927')->first();

        $this->assertNotNull($customer);
        $this->assertEquals('John Doe', $customer->name);
    }
}
