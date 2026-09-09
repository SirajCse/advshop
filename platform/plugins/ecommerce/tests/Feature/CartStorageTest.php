<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Cart\Exceptions\CartAlreadyStoredException;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartStorageTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::instance('cart')->destroy();
        DB::table('ec_cart')->truncate();
    }

    protected function createProduct(string $name = 'Test Product', float $price = 100): Product
    {
        return Product::query()->create([
            'name' => $name,
            'price' => $price,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    public function test_can_store_cart_to_database(): void
    {
        $product = $this->createProduct();
        $identifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('cart')->store($identifier);

        $this->assertDatabaseHas('ec_cart', [
            'identifier' => $identifier,
            'instance' => 'cart',
        ]);
    }

    public function test_can_restore_cart_from_database(): void
    {
        $product = $this->createProduct();
        $identifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            2,
            $product->price
        );

        Cart::instance('cart')->store($identifier);
        Cart::instance('cart')->destroy();

        $this->assertEquals(0, Cart::instance('cart')->content()->count());

        Cart::instance('cart')->restore($identifier);

        $this->assertEquals(1, Cart::instance('cart')->content()->count());
        $this->assertEquals(2, Cart::instance('cart')->count());
    }

    public function test_store_throws_exception_for_duplicate_identifier(): void
    {
        $product = $this->createProduct();
        $identifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('cart')->store($identifier);

        $this->expectException(CartAlreadyStoredException::class);

        Cart::instance('cart')->store($identifier);
    }

    public function test_update_or_store_creates_new_record(): void
    {
        $product = $this->createProduct();
        $identifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('cart')->updateOrStore($identifier);

        $this->assertDatabaseHas('ec_cart', [
            'identifier' => $identifier,
            'instance' => 'cart',
        ]);
    }

    public function test_update_or_store_updates_existing_record(): void
    {
        $product1 = $this->createProduct('Product 1', 100);
        $product2 = $this->createProduct('Product 2', 200);
        $identifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product1->id,
            $product1->name,
            1,
            $product1->price
        );

        Cart::instance('cart')->updateOrStore($identifier);

        Cart::instance('cart')->add(
            $product2->id,
            $product2->name,
            2,
            $product2->price
        );

        Cart::instance('cart')->updateOrStore($identifier);

        $count = DB::table('ec_cart')
            ->where('identifier', $identifier)
            ->where('instance', 'cart')
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_update_or_store_does_not_throw_exception_for_duplicate(): void
    {
        $product = $this->createProduct();
        $identifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('cart')->updateOrStore($identifier);
        Cart::instance('cart')->updateOrStore($identifier);
        Cart::instance('cart')->updateOrStore($identifier);

        $count = DB::table('ec_cart')
            ->where('identifier', $identifier)
            ->where('instance', 'cart')
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_different_instances_can_have_same_identifier(): void
    {
        $product = $this->createProduct();
        $identifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('wishlist')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('cart')->updateOrStore($identifier);
        Cart::instance('wishlist')->updateOrStore($identifier);

        $this->assertDatabaseHas('ec_cart', [
            'identifier' => $identifier,
            'instance' => 'cart',
        ]);

        $this->assertDatabaseHas('ec_cart', [
            'identifier' => $identifier,
            'instance' => 'wishlist',
        ]);

        $count = DB::table('ec_cart')
            ->where('identifier', $identifier)
            ->count();

        $this->assertEquals(2, $count);

        Cart::instance('wishlist')->destroy();
    }

    public function test_stored_cart_with_identifier_exists_checks_instance(): void
    {
        $product = $this->createProduct();
        $identifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('cart')->store($identifier);

        Cart::instance('wishlist')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('wishlist')->store($identifier);

        $count = DB::table('ec_cart')
            ->where('identifier', $identifier)
            ->count();

        $this->assertEquals(2, $count);

        Cart::instance('wishlist')->destroy();
    }

    public function test_restore_and_update_or_store_cycle(): void
    {
        $product = $this->createProduct();
        $identifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('cart')->updateOrStore($identifier);

        Cart::instance('cart')->destroy();

        Cart::instance('cart')->restore($identifier);

        $this->assertEquals(1, Cart::instance('cart')->count());

        Cart::instance('cart')->updateOrStore($identifier);

        $count = DB::table('ec_cart')
            ->where('identifier', $identifier)
            ->where('instance', 'cart')
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_update_or_store_preserves_cart_content(): void
    {
        $product1 = $this->createProduct('Product 1', 100);
        $product2 = $this->createProduct('Product 2', 200);
        $identifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product1->id,
            $product1->name,
            2,
            $product1->price
        );

        Cart::instance('cart')->add(
            $product2->id,
            $product2->name,
            3,
            $product2->price
        );

        Cart::instance('cart')->updateOrStore($identifier);

        Cart::instance('cart')->destroy();

        Cart::instance('cart')->restore($identifier);

        $this->assertEquals(2, Cart::instance('cart')->content()->count());
        $this->assertEquals(5, Cart::instance('cart')->count());
        $this->assertEquals(800, Cart::instance('cart')->rawSubTotal());
    }

    public function test_update_or_store_quietly_does_not_dispatch_events(): void
    {
        $product = $this->createProduct();
        $identifier = (string) Str::uuid();
        $eventFired = false;

        Cart::getEventDispatcher()->listen('cart.stored', function () use (&$eventFired): void {
            $eventFired = true;
        });

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('cart')->updateOrStoreQuietly($identifier);

        $this->assertFalse($eventFired);

        $this->assertDatabaseHas('ec_cart', [
            'identifier' => $identifier,
            'instance' => 'cart',
        ]);
    }

    public function test_multiple_update_or_store_maintains_latest_content(): void
    {
        $product = $this->createProduct();
        $identifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('cart')->updateOrStore($identifier);

        $content = Cart::instance('cart')->content();
        $rowId = $content->first()->rowId;
        Cart::instance('cart')->update($rowId, 5);

        $this->assertEquals(5, Cart::instance('cart')->count());

        Cart::instance('cart')->updateOrStore($identifier);

        Cart::instance('cart')->destroy();
        Cart::instance('cart')->restore($identifier);

        $this->assertEquals(5, Cart::instance('cart')->count());
    }
}
