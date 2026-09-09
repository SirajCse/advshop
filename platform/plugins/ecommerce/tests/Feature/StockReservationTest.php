<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Enums\StockStatusEnum;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Supports\OrderHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class StockReservationTest extends BaseTestCase
{
    use RefreshDatabase;

    protected OrderHelper $orderHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderHelper = app(OrderHelper::class);
    }

    protected function createProduct(array $attributes = []): Product
    {
        return Product::query()->create(array_merge([
            'name' => 'Test Product',
            'price' => 100,
            'quantity' => 10,
            'with_storehouse_management' => true,
            'allow_checkout_when_out_of_stock' => false,
            'stock_status' => StockStatusEnum::IN_STOCK,
            'status' => BaseStatusEnum::PUBLISHED,
        ], $attributes));
    }

    public function test_validate_and_reserve_stock_decrements_quantity(): void
    {
        $product = $this->createProduct(['quantity' => 5]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 3],
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $product->fresh()->quantity);
    }

    public function test_validate_and_reserve_stock_returns_reserved_items(): void
    {
        $product = $this->createProduct(['quantity' => 5]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 2],
        ]);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['reserved_items']);
        $this->assertEquals($product->id, $result['reserved_items'][0]['product_id']);
        $this->assertEquals(2, $result['reserved_items'][0]['qty']);
    }

    public function test_validate_and_reserve_stock_fails_when_insufficient_quantity(): void
    {
        $product = $this->createProduct(['quantity' => 2]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 5],
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals(2, $product->fresh()->quantity);
    }

    public function test_validate_and_reserve_stock_fails_for_out_of_stock_product(): void
    {
        $product = $this->createProduct(['quantity' => 0]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 1],
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('out of stock', $result['message']);
    }

    public function test_validate_and_reserve_stock_reserves_exact_quantity(): void
    {
        $product = $this->createProduct(['quantity' => 3]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 3],
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $product->fresh()->quantity);
    }

    public function test_validate_and_reserve_stock_handles_multiple_products(): void
    {
        $productA = $this->createProduct(['name' => 'Product A', 'quantity' => 5]);
        $productB = $this->createProduct(['name' => 'Product B', 'quantity' => 3]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $productA->id, 'qty' => 2],
            ['product_id' => $productB->id, 'qty' => 1],
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $productA->fresh()->quantity);
        $this->assertEquals(2, $productB->fresh()->quantity);
        $this->assertCount(2, $result['reserved_items']);
    }

    public function test_validate_and_reserve_stock_restores_on_partial_failure(): void
    {
        $productA = $this->createProduct(['name' => 'Product A', 'quantity' => 5]);
        $productB = $this->createProduct(['name' => 'Product B', 'quantity' => 1]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $productA->id, 'qty' => 2],
            ['product_id' => $productB->id, 'qty' => 5],
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals(5, $productA->fresh()->quantity);
        $this->assertEquals(1, $productB->fresh()->quantity);
    }

    public function test_validate_and_reserve_stock_skips_products_without_storehouse_management(): void
    {
        $product = $this->createProduct([
            'quantity' => 0,
            'with_storehouse_management' => false,
            'stock_status' => StockStatusEnum::IN_STOCK,
        ]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 1],
        ]);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['reserved_items']);
    }

    public function test_validate_and_reserve_stock_skips_products_allowing_oversell(): void
    {
        $product = $this->createProduct([
            'quantity' => 0,
            'allow_checkout_when_out_of_stock' => true,
        ]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 5],
        ]);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['reserved_items']);
        $this->assertEquals(0, $product->fresh()->quantity);
    }

    public function test_validate_and_reserve_stock_fires_quantity_updated_event(): void
    {
        Event::fake([ProductQuantityUpdatedEvent::class]);

        $product = $this->createProduct(['quantity' => 5]);

        $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 2],
        ]);

        Event::assertDispatched(ProductQuantityUpdatedEvent::class);
    }

    public function test_validate_and_reserve_stock_fails_below_minimum_order_quantity(): void
    {
        $product = $this->createProduct([
            'quantity' => 10,
            'minimum_order_quantity' => 3,
        ]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 1],
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals(10, $product->fresh()->quantity);
    }

    public function test_validate_and_reserve_stock_fails_above_maximum_order_quantity(): void
    {
        $product = $this->createProduct([
            'quantity' => 100,
            'maximum_order_quantity' => 5,
        ]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 10],
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals(100, $product->fresh()->quantity);
    }

    public function test_validate_and_reserve_stock_restores_on_min_quantity_failure(): void
    {
        $productA = $this->createProduct(['name' => 'Product A', 'quantity' => 5]);
        $productB = $this->createProduct([
            'name' => 'Product B',
            'quantity' => 10,
            'minimum_order_quantity' => 5,
        ]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $productA->id, 'qty' => 2],
            ['product_id' => $productB->id, 'qty' => 1],
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals(5, $productA->fresh()->quantity);
    }

    public function test_validate_and_reserve_stock_restores_on_max_quantity_failure(): void
    {
        $productA = $this->createProduct(['name' => 'Product A', 'quantity' => 5]);
        $productB = $this->createProduct([
            'name' => 'Product B',
            'quantity' => 10,
            'maximum_order_quantity' => 2,
        ]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $productA->id, 'qty' => 3],
            ['product_id' => $productB->id, 'qty' => 5],
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals(5, $productA->fresh()->quantity);
    }

    public function test_restore_reserved_stock_increments_quantity(): void
    {
        $product = $this->createProduct(['quantity' => 3]);

        $reservedItems = [
            ['product_id' => $product->id, 'qty' => 2],
        ];

        $this->orderHelper->restoreReservedStock($reservedItems);

        $this->assertEquals(5, $product->fresh()->quantity);
    }

    public function test_restore_reserved_stock_handles_multiple_products(): void
    {
        $productA = $this->createProduct(['name' => 'Product A', 'quantity' => 1]);
        $productB = $this->createProduct(['name' => 'Product B', 'quantity' => 0]);

        $reservedItems = [
            ['product_id' => $productA->id, 'qty' => 3],
            ['product_id' => $productB->id, 'qty' => 2],
        ];

        $this->orderHelper->restoreReservedStock($reservedItems);

        $this->assertEquals(4, $productA->fresh()->quantity);
        $this->assertEquals(2, $productB->fresh()->quantity);
    }

    public function test_restore_reserved_stock_fires_quantity_updated_event(): void
    {
        Event::fake([ProductQuantityUpdatedEvent::class]);

        $product = $this->createProduct(['quantity' => 0]);

        $this->orderHelper->restoreReservedStock([
            ['product_id' => $product->id, 'qty' => 1],
        ]);

        Event::assertDispatched(ProductQuantityUpdatedEvent::class);
    }

    public function test_restore_reserved_stock_noop_for_empty_array(): void
    {
        $this->orderHelper->restoreReservedStock([]);

        $this->assertTrue(true);
    }

    public function test_restore_reserved_stock_skips_missing_products(): void
    {
        $this->orderHelper->restoreReservedStock([
            ['product_id' => 99999, 'qty' => 5],
        ]);

        $this->assertTrue(true);
    }

    public function test_sequential_reservations_prevent_overselling(): void
    {
        $product = $this->createProduct(['quantity' => 1]);

        $result1 = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 1],
        ]);

        $result2 = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 1],
        ]);

        $this->assertTrue($result1['success']);
        $this->assertFalse($result2['success']);
        $this->assertEquals(0, $product->fresh()->quantity);
    }

    public function test_decrease_product_quantity_does_not_double_decrement_after_reservation(): void
    {
        $product = $this->createProduct(['quantity' => 5]);

        $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 5],
        ]);

        $this->assertEquals(0, $product->fresh()->quantity);

        $order = Order::query()->create([
            'amount' => 500,
            'sub_total' => 500,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'qty' => 5,
            'price' => 100,
        ]);

        $this->orderHelper->decreaseProductQuantity($order);

        $this->assertEquals(0, $product->fresh()->quantity);
    }

    public function test_reserve_then_restore_returns_to_original_quantity(): void
    {
        $product = $this->createProduct(['quantity' => 7]);

        $result = $this->orderHelper->validateAndReserveStock([
            ['product_id' => $product->id, 'qty' => 4],
        ]);

        $this->assertEquals(3, $product->fresh()->quantity);

        $this->orderHelper->restoreReservedStock($result['reserved_items']);

        $this->assertEquals(7, $product->fresh()->quantity);
    }
}
