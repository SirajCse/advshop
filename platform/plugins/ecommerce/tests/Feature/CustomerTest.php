<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class CustomerTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_can_create_customer(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertDatabaseHas('ec_customers', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_can_update_customer(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $customer->update(['name' => 'Jane Doe']);

        $this->assertDatabaseHas('ec_customers', [
            'id' => $customer->id,
            'name' => 'Jane Doe',
        ]);
    }

    public function test_can_delete_customer(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $customerId = $customer->id;
        $customer->delete();

        $this->assertDatabaseMissing('ec_customers', [
            'id' => $customerId,
        ]);
    }

    public function test_customer_password_is_hashed(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertTrue(Hash::check('password123', $customer->password));
    }

    public function test_customer_status_activated(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $this->assertEquals(CustomerStatusEnum::ACTIVATED, $customer->status);
    }

    public function test_customer_status_locked(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'status' => CustomerStatusEnum::LOCKED,
        ]);

        $this->assertEquals(CustomerStatusEnum::LOCKED, $customer->status);
    }

    public function test_customer_with_phone(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'phone' => '1234567890',
        ]);

        $this->assertEquals('1234567890', $customer->phone);
    }

    public function test_can_find_customer_by_unique_email(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'unique@example.com',
            'password' => bcrypt('password123'),
        ]);

        $customer = Customer::query()->where('email', 'unique@example.com')->first();

        $this->assertNotNull($customer);
        $this->assertEquals('John Doe', $customer->name);
        $this->assertEquals('unique@example.com', $customer->email);
    }

    public function test_can_find_customer_by_email(): void
    {
        Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'findme@example.com',
            'password' => bcrypt('password123'),
        ]);

        $customer = Customer::query()->where('email', 'findme@example.com')->first();

        $this->assertNotNull($customer);
        $this->assertEquals('John Doe', $customer->name);
    }

    public function test_can_filter_customers_by_status(): void
    {
        Customer::query()->create([
            'name' => 'Active Customer',
            'email' => 'active@example.com',
            'password' => bcrypt('password'),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        Customer::query()->create([
            'name' => 'Locked Customer',
            'email' => 'locked@example.com',
            'password' => bcrypt('password'),
            'status' => CustomerStatusEnum::LOCKED,
        ]);

        $activeCustomers = Customer::query()->where('status', CustomerStatusEnum::ACTIVATED)->get();
        $lockedCustomers = Customer::query()->where('status', CustomerStatusEnum::LOCKED)->get();

        $this->assertCount(1, $activeCustomers);
        $this->assertCount(1, $lockedCustomers);
    }

    public function test_can_search_customer_by_name(): void
    {
        Customer::query()->create([
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        Customer::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $customers = Customer::query()
            ->where('name', 'like', '%John%')
            ->get();

        $this->assertCount(1, $customers);
        $this->assertEquals('John Smith', $customers->first()->name);
    }

    public function test_customer_with_private_notes(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'private_notes' => 'VIP customer - Priority support',
        ]);

        $this->assertEquals('VIP customer - Priority support', $customer->private_notes);
    }

    public function test_customer_avatar_url_attribute(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertNotEmpty($customer->avatar_url);
    }

    public function test_customer_with_avatar(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'avatar' => 'customers/avatar.jpg',
        ]);

        $this->assertEquals('customers/avatar.jpg', $customer->avatar);
    }

    public function test_multiple_customers(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Customer::query()->create([
                'name' => "Customer {$i}",
                'email' => "customer{$i}@example.com",
                'password' => bcrypt('password'),
            ]);
        }

        $customers = Customer::query()->get();

        $this->assertCount(5, $customers);
    }

    public function test_customer_upload_folder_attribute(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertStringContainsString('customers', $customer->upload_folder);
    }
}
