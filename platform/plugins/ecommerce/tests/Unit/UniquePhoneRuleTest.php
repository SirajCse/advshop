<?php

namespace Botble\Ecommerce\Tests\Unit;

use Botble\Base\Rules\UniquePhoneRule;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class UniquePhoneRuleTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_passes_when_phone_not_exists(): void
    {
        $validator = Validator::make(
            ['phone' => '+84123456789'],
            ['phone' => UniquePhoneRule::make(Customer::class)]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_fails_when_phone_exact_match_exists(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $validator = Validator::make(
            ['phone' => '+84123456789'],
            ['phone' => UniquePhoneRule::make(Customer::class)]
        );

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    public function test_fails_when_phone_without_country_code_exists(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $validator = Validator::make(
            ['phone' => '123456789'],
            ['phone' => UniquePhoneRule::make(Customer::class)]
        );

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    public function test_fails_when_stored_phone_ends_with_input_digits(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $validator = Validator::make(
            ['phone' => '84123456789'],
            ['phone' => UniquePhoneRule::make(Customer::class)]
        );

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    public function test_passes_when_ignoring_specific_id(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $validator = Validator::make(
            ['phone' => '+84123456789'],
            ['phone' => UniquePhoneRule::make(Customer::class)->ignore($customer->id)]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_fails_when_ignoring_different_id(): void
    {
        $customer1 = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $customer2 = Customer::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+84999888777',
            'password' => bcrypt('password'),
        ]);

        $validator = Validator::make(
            ['phone' => '+84123456789'],
            ['phone' => UniquePhoneRule::make(Customer::class)->ignore($customer2->id)]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_passes_when_phone_is_empty(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $validator = Validator::make(
            ['phone' => ''],
            ['phone' => UniquePhoneRule::make(Customer::class)]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_passes_when_phone_is_null(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+84123456789',
            'password' => bcrypt('password'),
        ]);

        $validator = Validator::make(
            ['phone' => null],
            ['phone' => UniquePhoneRule::make(Customer::class)]
        );

        $this->assertTrue($validator->passes());
    }
}
