<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::instance('cart')->destroy();
    }

    public function test_can_add_product_to_cart(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        $this->assertEquals(1, Cart::instance('cart')->content()->count());
        $this->assertEquals(100, Cart::instance('cart')->rawSubTotal());
    }

    public function test_can_add_multiple_quantity(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 50,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            3,
            $product->price
        );

        $this->assertEquals(1, Cart::instance('cart')->content()->count());
        $this->assertEquals(3, Cart::instance('cart')->count());
        $this->assertEquals(150, Cart::instance('cart')->rawSubTotal());
    }

    public function test_can_update_cart_item_quantity(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $cartItem = Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('cart')->update($cartItem->rowId, 5);

        $this->assertEquals(1, Cart::instance('cart')->content()->count());
        $this->assertEquals(5, Cart::instance('cart')->count());
        $this->assertEquals(500, Cart::instance('cart')->rawSubTotal());
    }

    public function test_can_remove_item_from_cart(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $cartItem = Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('cart')->remove($cartItem->rowId);

        $this->assertEquals(0, Cart::instance('cart')->content()->count());
    }

    public function test_can_clear_cart(): void
    {
        $product1 = Product::query()->create([
            'name' => 'Product 1',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product 2',
            'price' => 50,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Cart::instance('cart')->add($product1->id, $product1->name, 1, $product1->price);
        Cart::instance('cart')->add($product2->id, $product2->name, 2, $product2->price);

        $this->assertEquals(2, Cart::instance('cart')->content()->count());

        Cart::instance('cart')->destroy();

        $this->assertEquals(0, Cart::instance('cart')->content()->count());
    }

    public function test_can_get_cart_content(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            2,
            $product->price
        );

        $content = Cart::instance('cart')->content();

        $this->assertCount(1, $content);
        $this->assertEquals('Test Product', $content->first()->name);
        $this->assertEquals(2, $content->first()->qty);
        $this->assertEquals(100, $content->first()->price);
    }

    public function test_adding_same_product_increases_quantity(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Cart::instance('cart')->add($product->id, $product->name, 1, $product->price);
        Cart::instance('cart')->add($product->id, $product->name, 2, $product->price);

        $this->assertEquals(1, Cart::instance('cart')->content()->count());
        $this->assertEquals(3, Cart::instance('cart')->count());
        $this->assertEquals(300, Cart::instance('cart')->rawSubTotal());
    }

    public function test_cart_with_multiple_products(): void
    {
        $product1 = Product::query()->create([
            'name' => 'Product 1',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product 2',
            'price' => 50,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product3 = Product::query()->create([
            'name' => 'Product 3',
            'price' => 75,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Cart::instance('cart')->add($product1->id, $product1->name, 2, $product1->price);
        Cart::instance('cart')->add($product2->id, $product2->name, 1, $product2->price);
        Cart::instance('cart')->add($product3->id, $product3->name, 3, $product3->price);

        $this->assertEquals(3, Cart::instance('cart')->content()->count());
        $this->assertEquals(6, Cart::instance('cart')->count());
        $this->assertEquals(475, Cart::instance('cart')->rawSubTotal());
    }

    public function test_can_use_different_cart_instances(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Cart::instance('cart')->add($product->id, $product->name, 1, $product->price);
        Cart::instance('wishlist')->add($product->id, $product->name, 1, $product->price);

        $this->assertEquals(1, Cart::instance('cart')->content()->count());
        $this->assertEquals(1, Cart::instance('wishlist')->content()->count());

        Cart::instance('cart')->destroy();

        $this->assertEquals(0, Cart::instance('cart')->content()->count());
        $this->assertEquals(1, Cart::instance('wishlist')->content()->count());

        Cart::instance('wishlist')->destroy();
    }

    public function test_cart_item_has_correct_row_id(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $cartItem = Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        $this->assertNotEmpty($cartItem->rowId);
        $this->assertIsString($cartItem->rowId);
    }

    public function test_can_get_cart_item_by_row_id(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $cartItem = Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        $retrievedItem = Cart::instance('cart')->get($cartItem->rowId);

        $this->assertEquals($cartItem->rowId, $retrievedItem->rowId);
        $this->assertEquals('Test Product', $retrievedItem->name);
    }

    public function test_update_with_zero_quantity_removes_item(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $cartItem = Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price
        );

        Cart::instance('cart')->update($cartItem->rowId, 0);

        $this->assertEquals(0, Cart::instance('cart')->content()->count());
    }

    public function test_cart_with_options(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $options = [
            'size' => 'Large',
            'color' => 'Red',
        ];

        $cartItem = Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            $product->price,
            $options
        );

        $this->assertEquals('Large', $cartItem->options->size);
        $this->assertEquals('Red', $cartItem->options->color);
    }

    public function test_empty_cart_has_zero_subtotal(): void
    {
        Cart::instance('cart')->destroy();

        $this->assertEquals(0, Cart::instance('cart')->content()->count());
        $this->assertEquals(0, Cart::instance('cart')->rawSubTotal());
    }

    // Regression: a second "Buy Now" of the same product after returning from the
    // checkout page must merge into the existing cart line, not create a duplicate.
    // refresh() used to overwrite the taxRate option with the taxRate property (which
    // checkout sets to a location-adjusted value), flipping the md5(serialize(options))
    // rowId so the re-added product no longer matched - producing two identical lines
    // and a duplicated product in the begin_checkout dataLayer.
    public function test_refresh_keeps_row_id_stable_when_checkout_sets_tax_rate(): void
    {
        $product = Product::query()->create([
            'name' => 'Italian Pasta Collection',
            'price' => 8.99,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // First "Buy Now": handleAddCart stores the product's tax percentage as an option.
        $addOptions = ['taxRate' => 0, 'image' => $product->image];
        Cart::instance('cart')->add($product->id, $product->name, 2, $product->price, $addOptions);

        $this->assertEquals(1, Cart::instance('cart')->content()->count());
        $originalRowId = Cart::instance('cart')->content()->keys()->first();

        // Checkout applies a location-based tax rate to the line via setTax(), which sets
        // the taxRate *property* only - the option keeps its original value.
        Cart::instance('cart')->setTax($originalRowId, 15);

        // refresh() runs on the cart/checkout page.
        Cart::instance('cart')->refresh();

        $this->assertEquals(1, Cart::instance('cart')->content()->count());
        $this->assertEquals($originalRowId, Cart::instance('cart')->content()->keys()->first());

        // Second "Buy Now" of the same product after returning from checkout must merge.
        Cart::instance('cart')->add($product->id, $product->name, 2, $product->price, $addOptions);

        $this->assertEquals(1, Cart::instance('cart')->content()->count());
        $this->assertEquals(4, Cart::instance('cart')->count());
    }
}
