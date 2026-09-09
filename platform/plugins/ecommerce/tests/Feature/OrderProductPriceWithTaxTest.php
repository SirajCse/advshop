<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderProductPriceWithTaxTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Test that price_with_tax returns price as-is when price_includes_tax is true.
     * This prevents double-counting tax for tax-inclusive products.
     * Scenario: price=199 (tax-inclusive), tax_amount=9, options.price_includes_tax=true
     * Expected: price_with_tax = 199 (not 199+9=208)
     */
    public function test_price_with_tax_does_not_add_tax_when_price_includes_tax_true(): void
    {
        $order = $this->createOrder();

        $orderProduct = OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => 1,
            'product_name' => 'Tax-Inclusive Product',
            'product_image' => 'product.jpg',
            'qty' => 1,
            'price' => 199,
            'tax_amount' => 9,
            'options' => ['price_includes_tax' => true],
        ]);

        // price_with_tax should return 199, not 199+9=208
        $this->assertEquals(199, $orderProduct->price_with_tax);
    }

    /**
     * Test that total_price_with_tax multiplies correctly when price_includes_tax is true.
     * Scenario: price=199 (tax-inclusive), qty=2, tax_amount=9 per item
     * Expected: total_price_with_tax = 199 * 2 = 398
     */
    public function test_total_price_with_tax_multiplies_correctly_when_price_includes_tax_true(): void
    {
        $order = $this->createOrder();

        $orderProduct = OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => 1,
            'product_name' => 'Tax-Inclusive Product',
            'product_image' => 'product.jpg',
            'qty' => 2,
            'price' => 199,
            'tax_amount' => 9,
            'options' => ['price_includes_tax' => true],
        ]);

        $this->assertEquals(199, $orderProduct->price_with_tax);
        $this->assertEquals(398, $orderProduct->total_price_with_tax);
    }

    /**
     * Test that price_with_tax adds tax when price_includes_tax is false.
     * Scenario: price=100 (tax-exclusive), tax_amount=10, options.price_includes_tax=false
     * Expected: price_with_tax = 100 + 10 = 110
     */
    public function test_price_with_tax_adds_tax_when_price_includes_tax_false(): void
    {
        $order = $this->createOrder();

        $orderProduct = OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => 1,
            'product_name' => 'Tax-Exclusive Product',
            'product_image' => 'product.jpg',
            'qty' => 1,
            'price' => 100,
            'tax_amount' => 10,
            'options' => ['price_includes_tax' => false],
        ]);

        $this->assertEquals(110, $orderProduct->price_with_tax);
    }

    /**
     * Test backward compatibility: when options is null (legacy orders),
     * price_with_tax should add tax.
     * Scenario: price=100, tax_amount=10, options=null
     * Expected: price_with_tax = 100 + 10 = 110
     */
    public function test_price_with_tax_adds_tax_when_options_null(): void
    {
        $order = $this->createOrder();

        $orderProduct = OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => 1,
            'product_name' => 'Legacy Product',
            'product_image' => 'product.jpg',
            'qty' => 1,
            'price' => 100,
            'tax_amount' => 10,
            'options' => null,
        ]);

        $this->assertEquals(110, $orderProduct->price_with_tax);
    }

    /**
     * Test backward compatibility: when price_includes_tax flag is missing from options,
     * price_with_tax should add tax (default behavior).
     * Scenario: price=100, tax_amount=10, options={'other':'value'} (no price_includes_tax key)
     * Expected: price_with_tax = 100 + 10 = 110
     */
    public function test_price_with_tax_adds_tax_when_flag_missing_from_options(): void
    {
        $order = $this->createOrder();

        $orderProduct = OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => 1,
            'product_name' => 'Product Without Flag',
            'product_image' => 'product.jpg',
            'qty' => 1,
            'price' => 100,
            'tax_amount' => 10,
            'options' => ['other_key' => 'value'],
        ]);

        $this->assertEquals(110, $orderProduct->price_with_tax);
    }

    /**
     * Test that total_price_with_tax multiplies correctly when price_includes_tax is false.
     * Scenario: price=100, tax_amount=10, qty=3, options.price_includes_tax=false
     * Expected: total_price_with_tax = (100 + 10) * 3 = 330
     */
    public function test_total_price_with_tax_multiplies_correctly_when_price_includes_tax_false(): void
    {
        $order = $this->createOrder();

        $orderProduct = OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => 1,
            'product_name' => 'Tax-Exclusive Product',
            'product_image' => 'product.jpg',
            'qty' => 3,
            'price' => 100,
            'tax_amount' => 10,
            'options' => ['price_includes_tax' => false],
        ]);

        $this->assertEquals(110, $orderProduct->price_with_tax);
        $this->assertEquals(330, $orderProduct->total_price_with_tax);
    }

    /**
     * Helper method to create an order for testing.
     */
    private function createOrder(): Order
    {
        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);

        return Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 0,
            'sub_total' => 0,
            'shipping_amount' => 0,
            'status' => OrderStatusEnum::COMPLETED,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
            'completed_at' => now(),
        ]);
    }
}
