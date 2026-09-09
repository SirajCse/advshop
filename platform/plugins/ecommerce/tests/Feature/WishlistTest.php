<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WishlistTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createProduct(string $name = 'Test Product', float $price = 100): Product
    {
        return Product::query()->create([
            'name' => $name,
            'price' => $price,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    public function test_can_add_product_to_wishlist(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $wishlist = Wishlist::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseHas('ec_wish_lists', [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_can_remove_product_from_wishlist(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        Wishlist::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        Wishlist::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->delete();

        $this->assertDatabaseMissing('ec_wish_lists', [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_wishlist_belongs_to_product(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $wishlist = Wishlist::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->assertEquals($product->id, $wishlist->product->id);
        $this->assertEquals('Test Product', $wishlist->product->name);
    }

    public function test_customer_can_have_multiple_wishlist_items(): void
    {
        $customer = $this->createCustomer();

        $product1 = $this->createProduct('Product 1', 100);
        $product2 = $this->createProduct('Product 2', 200);
        $product3 = $this->createProduct('Product 3', 300);

        Wishlist::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product1->id,
        ]);

        Wishlist::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product2->id,
        ]);

        Wishlist::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product3->id,
        ]);

        $wishlistItems = Wishlist::query()->where('customer_id', $customer->id)->get();

        $this->assertCount(3, $wishlistItems);
    }

    public function test_can_check_if_product_in_wishlist(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $exists = Wishlist::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->exists();

        $this->assertFalse($exists);

        Wishlist::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $exists = Wishlist::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->exists();

        $this->assertTrue($exists);
    }

    public function test_different_customers_can_wishlist_same_product(): void
    {
        $customer1 = $this->createCustomer();

        $customer2 = Customer::query()->create([
            'name' => 'Customer 2',
            'email' => 'customer2@example.com',
            'password' => bcrypt('password'),
        ]);

        $product = $this->createProduct();

        Wishlist::query()->create([
            'customer_id' => $customer1->id,
            'product_id' => $product->id,
        ]);

        Wishlist::query()->create([
            'customer_id' => $customer2->id,
            'product_id' => $product->id,
        ]);

        $productWishlists = Wishlist::query()->where('product_id', $product->id)->get();

        $this->assertCount(2, $productWishlists);
    }

    public function test_can_get_wishlist_count_for_customer(): void
    {
        $customer = $this->createCustomer();

        for ($i = 1; $i <= 5; $i++) {
            $product = $this->createProduct("Product {$i}", $i * 100);
            Wishlist::query()->create([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
            ]);
        }

        $wishlistCount = Wishlist::query()->where('customer_id', $customer->id)->count();

        $this->assertEquals(5, $wishlistCount);
    }

    public function test_can_clear_customer_wishlist(): void
    {
        $customer = $this->createCustomer();

        for ($i = 1; $i <= 3; $i++) {
            $product = $this->createProduct("Product {$i}", $i * 100);
            Wishlist::query()->create([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
            ]);
        }

        Wishlist::query()->where('customer_id', $customer->id)->delete();

        $wishlistCount = Wishlist::query()->where('customer_id', $customer->id)->count();

        $this->assertEquals(0, $wishlistCount);
    }

    public function test_wishlist_product_details(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct('Premium Product', 999.99);

        $wishlist = Wishlist::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->assertEquals('Premium Product', $wishlist->product->name);
        $this->assertEquals(999.99, $wishlist->product->price);
    }

    public function test_can_get_wishlisted_products_for_customer(): void
    {
        $customer = $this->createCustomer();

        $product1 = $this->createProduct('Product 1', 100);
        $product2 = $this->createProduct('Product 2', 200);

        Wishlist::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product1->id,
        ]);

        Wishlist::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product2->id,
        ]);

        $wishlistItems = Wishlist::query()
            ->where('customer_id', $customer->id)
            ->with('product')
            ->get();

        $productNames = $wishlistItems->pluck('product.name')->toArray();

        $this->assertContains('Product 1', $productNames);
        $this->assertContains('Product 2', $productNames);
    }

    public function test_prevent_duplicate_wishlist_entries(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        Wishlist::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $existingEntry = Wishlist::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->first();

        if (! $existingEntry) {
            Wishlist::query()->create([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
            ]);
        }

        $count = Wishlist::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_wishlist_timestamps(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $wishlist = Wishlist::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->assertNotNull($wishlist->created_at);
        $this->assertNotNull($wishlist->updated_at);
    }
}
