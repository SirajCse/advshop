<?php

namespace Botble\Marketplace\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Enums\StoreStatusEnum;
use Botble\Marketplace\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_can_create_store(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        $store = Store::query()->create([
            'name' => 'Test Store',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
        ]);

        $this->assertDatabaseHas('mp_stores', [
            'name' => 'Test Store',
            'customer_id' => $customer->id,
        ]);
    }

    public function test_can_update_store(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        $store = Store::query()->create([
            'name' => 'Test Store',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
        ]);

        $store->update(['name' => 'Updated Store Name']);

        $this->assertDatabaseHas('mp_stores', [
            'id' => $store->id,
            'name' => 'Updated Store Name',
        ]);
    }

    public function test_store_belongs_to_customer(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        $store = Store::query()->create([
            'name' => 'Test Store',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals($customer->id, $store->customer->id);
        $this->assertEquals('Test Vendor', $store->customer->name);
    }

    public function test_store_status_published(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        $store = Store::query()->create([
            'name' => 'Published Store',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(StoreStatusEnum::PUBLISHED, $store->status);
    }

    public function test_store_status_pending(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        $store = Store::query()->create([
            'name' => 'Pending Store',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PENDING,
        ]);

        $this->assertEquals(StoreStatusEnum::PENDING, $store->status);
    }

    public function test_store_status_blocked(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        $store = Store::query()->create([
            'name' => 'Blocked Store',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::BLOCKED,
        ]);

        $this->assertEquals(StoreStatusEnum::BLOCKED, $store->status);
    }

    public function test_store_with_contact_info(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        $store = Store::query()->create([
            'name' => 'Contact Store',
            'email' => 'store@example.com',
            'phone' => '1234567890',
            'address' => '123 Test Street',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('store@example.com', $store->email);
        $this->assertEquals('1234567890', $store->phone);
        $this->assertEquals('123 Test Street', $store->address);
    }

    public function test_store_with_description(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        $store = Store::query()->create([
            'name' => 'Descriptive Store',
            'description' => 'This is a test store description',
            'content' => 'Full content about the store',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('This is a test store description', $store->description);
        $this->assertEquals('Full content about the store', $store->content);
    }

    public function test_store_with_company_info(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        $store = Store::query()->create([
            'name' => 'Company Store',
            'company' => 'Test Company Inc.',
            'tax_id' => 'TAX123456',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('Test Company Inc.', $store->company);
        $this->assertEquals('TAX123456', $store->tax_id);
    }

    public function test_store_is_verified(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        $verifiedStore = Store::query()->create([
            'name' => 'Verified Store',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        $unverifiedStore = Store::query()->create([
            'name' => 'Unverified Store',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
            'is_verified' => false,
        ]);

        $this->assertTrue($verifiedStore->is_verified);
        $this->assertFalse($unverifiedStore->is_verified);
    }

    public function test_can_filter_stores_by_status(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        Store::query()->create([
            'name' => 'Published Store',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
        ]);

        Store::query()->create([
            'name' => 'Pending Store',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PENDING,
        ]);

        Store::query()->create([
            'name' => 'Blocked Store',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::BLOCKED,
        ]);

        $publishedStores = Store::query()->where('status', StoreStatusEnum::PUBLISHED)->get();
        $pendingStores = Store::query()->where('status', StoreStatusEnum::PENDING)->get();
        $blockedStores = Store::query()->where('status', StoreStatusEnum::BLOCKED)->get();

        $this->assertCount(1, $publishedStores);
        $this->assertCount(1, $pendingStores);
        $this->assertCount(1, $blockedStores);
    }

    public function test_can_search_store_by_name(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        Store::query()->create([
            'name' => 'Electronics Store',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
        ]);

        Store::query()->create([
            'name' => 'Fashion Boutique',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
        ]);

        $stores = Store::query()
            ->where('name', 'like', '%Electronics%')
            ->get();

        $this->assertCount(1, $stores);
        $this->assertEquals('Electronics Store', $stores->first()->name);
    }
}
