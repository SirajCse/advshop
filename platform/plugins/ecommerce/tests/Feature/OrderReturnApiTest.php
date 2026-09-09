<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderReturnReasonEnum;
use Botble\Ecommerce\Enums\OrderReturnStatusEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\OrderReturn;
use Botble\Ecommerce\Supports\OrderReturnHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderReturnApiTest extends BaseTestCase
{
    use RefreshDatabase;

    protected OrderReturnHelper $orderReturnHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderReturnHelper = new OrderReturnHelper();
    }

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createCompletedOrder(Customer $customer, float $amount = 100): Order
    {
        return Order::query()->create([
            'user_id' => $customer->id,
            'amount' => $amount,
            'sub_total' => $amount,
            'shipping_amount' => 0,
            'status' => OrderStatusEnum::COMPLETED,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
            'completed_at' => now(),
        ]);
    }

    protected function createOrderProduct(Order $order, array $overrides = []): OrderProduct
    {
        return OrderProduct::query()->create(array_merge([
            'order_id' => $order->id,
            'product_id' => 1,
            'product_name' => 'Test Product',
            'product_image' => 'test.jpg',
            'qty' => 2,
            'price' => 50,
        ], $overrides));
    }

    public function test_get_returnable_items_returns_all_order_products(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createCompletedOrder($customer);

        $this->createOrderProduct($order, [
            'product_name' => 'Product A',
            'product_id' => 1,
            'qty' => 3,
            'price' => 30,
        ]);

        $this->createOrderProduct($order, [
            'product_name' => 'Product B',
            'product_id' => 2,
            'qty' => 1,
            'price' => 70,
        ]);

        $items = $this->orderReturnHelper->getReturnableItems($order);

        $this->assertCount(2, $items);

        $this->assertEquals('Product A', $items[0]['product_name']);
        $this->assertEquals(3, $items[0]['qty']);
        $this->assertEquals(30, $items[0]['price']);

        $this->assertEquals('Product B', $items[1]['product_name']);
        $this->assertEquals(1, $items[1]['qty']);
        $this->assertEquals(70, $items[1]['price']);
    }

    public function test_get_returnable_items_returns_correct_keys(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createCompletedOrder($customer);
        $orderProduct = $this->createOrderProduct($order);

        $items = $this->orderReturnHelper->getReturnableItems($order);

        $this->assertCount(1, $items);

        $expectedKeys = ['order_item_id', 'product_id', 'product_name', 'product_image', 'price', 'qty'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $items[0]);
        }

        $this->assertEquals($orderProduct->id, $items[0]['order_item_id']);
        $this->assertEquals($orderProduct->product_id, $items[0]['product_id']);
        $this->assertEquals('Test Product', $items[0]['product_name']);
        $this->assertEquals('test.jpg', $items[0]['product_image']);
        $this->assertEquals(50, $items[0]['price']);
        $this->assertEquals(2, $items[0]['qty']);
    }

    public function test_get_returnable_items_returns_empty_for_order_without_products(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createCompletedOrder($customer);

        $items = $this->orderReturnHelper->getReturnableItems($order);

        $this->assertIsArray($items);
        $this->assertEmpty($items);
    }

    public function test_get_returnable_items_with_multiple_quantities(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createCompletedOrder($customer);

        $this->createOrderProduct($order, [
            'product_name' => 'Bulk Product',
            'qty' => 10,
            'price' => 25,
        ]);

        $items = $this->orderReturnHelper->getReturnableItems($order);

        $this->assertCount(1, $items);
        $this->assertEquals(10, $items[0]['qty']);
    }

    public function test_get_return_reasons_returns_non_empty_array(): void
    {
        $reasons = $this->orderReturnHelper->getReturnReasons();

        $this->assertIsArray($reasons);
        $this->assertNotEmpty($reasons);
    }

    public function test_get_return_reasons_excludes_empty_none_value(): void
    {
        $reasons = $this->orderReturnHelper->getReturnReasons();

        $values = array_column($reasons, 'value');
        $this->assertNotContains('', $values);
    }

    public function test_get_return_reasons_has_value_and_label_keys(): void
    {
        $reasons = $this->orderReturnHelper->getReturnReasons();

        foreach ($reasons as $reason) {
            $this->assertArrayHasKey('value', $reason);
            $this->assertArrayHasKey('label', $reason);
            $this->assertNotEmpty($reason['value']);
            $this->assertNotEmpty($reason['label']);
        }
    }

    public function test_get_return_reasons_contains_known_reasons(): void
    {
        $reasons = $this->orderReturnHelper->getReturnReasons();

        $values = array_column($reasons, 'value');

        $this->assertContains(OrderReturnReasonEnum::DAMAGED, $values);
        $this->assertContains(OrderReturnReasonEnum::DEFECTIVE, $values);
        $this->assertContains(OrderReturnReasonEnum::INCORRECT_ITEM, $values);
        $this->assertContains(OrderReturnReasonEnum::NO_LONGER_WANT, $values);
        $this->assertContains(OrderReturnReasonEnum::ARRIVED_LATE, $values);
        $this->assertContains(OrderReturnReasonEnum::NOT_AS_DESCRIBED, $values);
        $this->assertContains(OrderReturnReasonEnum::OTHER, $values);
    }

    public function test_order_cannot_be_returned_when_not_completed(): void
    {
        $customer = $this->createCustomer();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 100,
            'sub_total' => 100,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $this->assertFalse($order->canBeReturned());
    }

    public function test_order_cannot_be_returned_when_canceled(): void
    {
        $customer = $this->createCustomer();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 100,
            'sub_total' => 100,
            'status' => OrderStatusEnum::CANCELED,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $this->assertFalse($order->canBeReturned());
    }

    public function test_order_cannot_be_returned_when_return_already_exists(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createCompletedOrder($customer);

        OrderReturn::query()->create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'return_status' => OrderReturnStatusEnum::PENDING,
        ]);

        $this->assertFalse($order->fresh()->canBeReturned());
    }

    public function test_cancel_return_order_updates_status(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createCompletedOrder($customer);

        $orderReturn = OrderReturn::query()->create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'return_status' => OrderReturnStatusEnum::PENDING,
        ]);

        [$status] = $this->orderReturnHelper->cancelReturnOrder($orderReturn, 'Changed my mind');

        $this->assertTrue($status);
        $this->assertEquals(
            OrderReturnStatusEnum::CANCELED,
            $orderReturn->fresh()->return_status
        );
    }
}
