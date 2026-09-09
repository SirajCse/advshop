<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartCustomerPersistenceTest extends BaseTestCase
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

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'test-' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_can_store_cart_for_customer(): void
    {
        $product = $this->createProduct();
        $customer = $this->createCustomer();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('cart')->storeForCustomer($customer->id);

        $this->assertDatabaseHas('ec_cart', [
            'customer_id' => $customer->id,
            'instance' => 'cart',
        ]);
    }

    public function test_can_restore_cart_for_customer(): void
    {
        $product = $this->createProduct();
        $customer = $this->createCustomer();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            2,
            $product->price
        );

        Cart::instance('cart')->storeForCustomer($customer->id);
        Cart::instance('cart')->destroy();

        $this->assertEquals(0, Cart::instance('cart')->content()->count());

        Cart::instance('cart')->restoreForCustomer($customer->id);

        $this->assertEquals(1, Cart::instance('cart')->content()->count());
        $this->assertEquals(2, Cart::instance('cart')->count());
    }

    public function test_can_merge_guest_cart_with_customer_cart(): void
    {
        $product1 = $this->createProduct('Guest Product', 50);
        $product2 = $this->createProduct('Customer Product', 100);
        $customer = $this->createCustomer();
        $guestIdentifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product1->id,
            $product1->name,
            1,
            $product1->price
        );
        Cart::instance('cart')->store($guestIdentifier);
        Cart::instance('cart')->destroy();

        Cart::instance('cart')->add(
            $product2->id,
            $product2->name,
            1,
            $product2->price
        );
        Cart::instance('cart')->storeForCustomer($customer->id);
        Cart::instance('cart')->destroy();

        Cart::instance('cart')->restoreForCustomer($customer->id);
        Cart::instance('cart')->mergeGuestCart($guestIdentifier, $customer->id);

        $this->assertEquals(2, Cart::instance('cart')->content()->count());
    }

    public function test_link_guest_cart_to_customer(): void
    {
        $product = $this->createProduct();
        $customer = $this->createCustomer();
        $guestIdentifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );
        Cart::instance('cart')->store($guestIdentifier);

        $result = Cart::instance('cart')->linkGuestCartToCustomer($guestIdentifier, $customer->id);

        $this->assertTrue($result);
        $this->assertDatabaseHas('ec_cart', [
            'identifier' => $guestIdentifier,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_customer_cart_exists_check(): void
    {
        $product = $this->createProduct();
        $customer = $this->createCustomer();

        $this->assertFalse(Cart::instance('cart')->customerCartExists($customer->id));

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );
        Cart::instance('cart')->storeForCustomer($customer->id);

        $this->assertTrue(Cart::instance('cart')->customerCartExists($customer->id));
    }

    public function test_delete_customer_cart(): void
    {
        $product = $this->createProduct();
        $customer = $this->createCustomer();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );
        Cart::instance('cart')->storeForCustomer($customer->id);

        $this->assertTrue(Cart::instance('cart')->customerCartExists($customer->id));

        Cart::instance('cart')->deleteCustomerCart($customer->id);

        $this->assertFalse(Cart::instance('cart')->customerCartExists($customer->id));
    }

    public function test_get_customer_cart(): void
    {
        $product = $this->createProduct();
        $customer = $this->createCustomer();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            3,
            $product->price
        );
        Cart::instance('cart')->storeForCustomer($customer->id);

        $storedCart = Cart::instance('cart')->getCustomerCart($customer->id);

        $this->assertNotNull($storedCart);
        $this->assertEquals($customer->id, $storedCart->customer_id);
        $this->assertEquals('cart', $storedCart->instance);
    }

    public function test_cart_syncs_on_customer_login(): void
    {
        $product = $this->createProduct();
        $customer = $this->createCustomer();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            2,
            $product->price
        );
        Cart::instance('cart')->storeForCustomer($customer->id);
        Cart::instance('cart')->destroy();

        event(new Login('customer', $customer, false));

        $this->assertFalse(Cart::instance('cart')->isEmpty());
        $this->assertEquals(2, Cart::instance('cart')->count());
    }

    public function test_cart_persists_on_customer_logout(): void
    {
        $product = $this->createProduct();
        $customer = $this->createCustomer();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            3,
            $product->price
        );

        event(new Logout('customer', $customer));

        $this->assertDatabaseHas('ec_cart', [
            'customer_id' => $customer->id,
            'instance' => 'cart',
        ]);
    }

    public function test_cart_links_on_registration_with_guest_cart(): void
    {
        $product = $this->createProduct();
        $guestIdentifier = (string) Str::uuid();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );
        Cart::instance('cart')->store($guestIdentifier);
        session(['cart_identifier' => $guestIdentifier]);

        $customer = $this->createCustomer();

        event(new Registered($customer));

        $this->assertDatabaseHas('ec_cart', [
            'identifier' => $guestIdentifier,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_cart_stores_for_new_customer_without_guest_cart(): void
    {
        $product = $this->createProduct();
        $customer = $this->createCustomer();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        event(new Registered($customer));

        $this->assertDatabaseHas('ec_cart', [
            'customer_id' => $customer->id,
            'instance' => 'cart',
        ]);
    }

    public function test_non_customer_login_is_ignored(): void
    {
        $adminUser = new \stdClass();
        $adminUser->id = 999;

        event(new Login('web', $adminUser, false));

        $this->assertTrue(true);
    }

    public function test_store_for_customer_quietly_does_not_dispatch_events(): void
    {
        $product = $this->createProduct();
        $customer = $this->createCustomer();
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

        Cart::instance('cart')->storeForCustomerQuietly($customer->id);

        $this->assertFalse($eventFired);

        $this->assertDatabaseHas('ec_cart', [
            'customer_id' => $customer->id,
            'instance' => 'cart',
        ]);
    }

    public function test_restore_for_customer_quietly_does_not_dispatch_events(): void
    {
        $product = $this->createProduct();
        $customer = $this->createCustomer();
        $eventFired = false;

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );
        Cart::instance('cart')->storeForCustomer($customer->id);
        Cart::instance('cart')->destroy();

        Cart::getEventDispatcher()->listen('cart.restored', function () use (&$eventFired): void {
            $eventFired = true;
        });

        Cart::instance('cart')->restoreForCustomerQuietly($customer->id);

        $this->assertFalse($eventFired);
        $this->assertEquals(1, Cart::instance('cart')->count());
    }

    public function test_customer_cart_uses_customer_id_as_identifier(): void
    {
        $product = $this->createProduct();
        $customer = $this->createCustomer();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );
        Cart::instance('cart')->storeForCustomer($customer->id);

        $this->assertDatabaseHas('ec_cart', [
            'identifier' => (string) $customer->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_customer_deletion_cleans_up_carts(): void
    {
        $product = $this->createProduct();
        $customer = $this->createCustomer();

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );
        Cart::instance('cart')->storeForCustomer($customer->id);

        $this->assertDatabaseHas('ec_cart', [
            'customer_id' => $customer->id,
        ]);

        $customer->delete();

        $this->assertDatabaseMissing('ec_cart', [
            'customer_id' => $customer->id,
        ]);
    }
}
