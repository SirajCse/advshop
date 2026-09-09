<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderWeightTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_order_product_weight_stores_unit_weight_and_total_is_correct(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'weight' => 500, // 500g per unit
        ]);

        $order = Order::query()->create([
            'amount' => 300,
            'sub_total' => 300,
        ]);

        // Store unit weight (the fix), not weight * qty (the old bug)
        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'qty' => 3,
            'weight' => 500, // unit weight, not 1500
            'price' => 100,
        ]);

        $orderProduct = OrderProduct::query()->where('order_id', $order->id)->first();

        $this->assertEquals(500, $orderProduct->weight, 'Weight should be unit weight (500), not total (1500)');
        $this->assertEquals(1500, $order->fresh()->products_weight, 'Order total weight should be 500 * 3 = 1500');
    }

    public function test_order_weight_calculation_with_multiple_products(): void
    {
        $product1 = Product::query()->create([
            'name' => 'Product 1',
            'price' => 100,
            'weight' => 200,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product 2',
            'price' => 200,
            'weight' => 500,
        ]);

        $order = Order::query()->create([
            'amount' => 800,
            'sub_total' => 800,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'product_name' => 'Product 1',
            'qty' => 2,
            'weight' => 200,
            'price' => 100,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'product_name' => 'Product 2',
            'qty' => 3,
            'weight' => 500,
            'price' => 200,
        ]);

        // (200 * 2) + (500 * 3) = 400 + 1500 = 1900
        $this->assertEquals(1900, $order->fresh()->products_weight);
    }

    public function test_order_weight_ignores_zero_weight_products(): void
    {
        $physical = Product::query()->create([
            'name' => 'Physical Product',
            'price' => 100,
            'weight' => 300,
        ]);

        $digital = Product::query()->create([
            'name' => 'Digital Product',
            'price' => 50,
            'weight' => 0,
        ]);

        $order = Order::query()->create([
            'amount' => 250,
            'sub_total' => 250,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $physical->id,
            'product_name' => 'Physical Product',
            'qty' => 2,
            'weight' => 300,
            'price' => 100,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $digital->id,
            'product_name' => 'Digital Product',
            'qty' => 1,
            'weight' => 0,
            'price' => 50,
        ]);

        // Only physical: 300 * 2 = 600, digital weight (0) ignored
        $this->assertEquals(600, $order->fresh()->products_weight);
    }
}
