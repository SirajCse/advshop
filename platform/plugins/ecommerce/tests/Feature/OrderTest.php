<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends BaseTestCase
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

    public function test_can_create_order(): void
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

        $this->assertDatabaseHas('ec_orders', [
            'user_id' => $customer->id,
            'amount' => 100,
        ]);
    }

    public function test_order_has_unique_code(): void
    {
        $customer = $this->createCustomer();

        $order1 = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 100,
            'sub_total' => 100,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $order2 = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 200,
            'sub_total' => 200,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $this->assertNotEmpty($order1->code);
        $this->assertNotEmpty($order2->code);
        $this->assertNotEquals($order1->code, $order2->code);
    }

    public function test_order_status_pending(): void
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

        $this->assertEquals(OrderStatusEnum::PENDING, $order->status);
    }

    public function test_order_status_processing(): void
    {
        $customer = $this->createCustomer();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 100,
            'sub_total' => 100,
            'status' => OrderStatusEnum::PROCESSING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $this->assertEquals(OrderStatusEnum::PROCESSING, $order->status);
    }

    public function test_order_status_completed(): void
    {
        $customer = $this->createCustomer();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 100,
            'sub_total' => 100,
            'status' => OrderStatusEnum::COMPLETED,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
            'completed_at' => now(),
        ]);

        $this->assertEquals(OrderStatusEnum::COMPLETED, $order->status);
        $this->assertNotNull($order->completed_at);
    }

    public function test_order_status_canceled(): void
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

        $this->assertEquals(OrderStatusEnum::CANCELED, $order->status);
    }

    public function test_order_belongs_to_customer(): void
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

        $this->assertEquals($customer->id, $order->user->id);
        $this->assertEquals('Test Customer', $order->user->name);
    }

    public function test_order_with_discount(): void
    {
        $customer = $this->createCustomer();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 80,
            'sub_total' => 100,
            'discount_amount' => 20,
            'coupon_code' => 'SAVE20',
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $this->assertEquals(80, $order->amount);
        $this->assertEquals(100, $order->sub_total);
        $this->assertEquals(20, $order->discount_amount);
        $this->assertEquals('SAVE20', $order->coupon_code);
    }

    public function test_order_with_tax(): void
    {
        $customer = $this->createCustomer();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 110,
            'sub_total' => 100,
            'tax_amount' => 10,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $this->assertEquals(110, $order->amount);
        $this->assertEquals(10, $order->tax_amount);
    }

    public function test_order_with_shipping(): void
    {
        $customer = $this->createCustomer();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 115,
            'sub_total' => 100,
            'shipping_amount' => 15,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $this->assertEquals(115, $order->amount);
        $this->assertEquals(15, $order->shipping_amount);
    }

    public function test_order_can_be_canceled_when_pending(): void
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

        $this->assertTrue($order->canBeCanceled());
    }

    public function test_order_cannot_be_canceled_when_already_canceled(): void
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

        $this->assertFalse($order->canBeCanceled());
    }

    public function test_can_update_order_status(): void
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

        $order->update(['status' => OrderStatusEnum::PROCESSING]);

        $this->assertEquals(OrderStatusEnum::PROCESSING, $order->fresh()->status);
    }

    public function test_order_is_confirmed(): void
    {
        $customer = $this->createCustomer();

        $confirmedOrder = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 100,
            'sub_total' => 100,
            'status' => OrderStatusEnum::PROCESSING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
            'is_confirmed' => true,
        ]);

        $unconfirmedOrder = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 100,
            'sub_total' => 100,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
            'is_confirmed' => false,
        ]);

        $this->assertTrue($confirmedOrder->is_confirmed);
        $this->assertFalse($unconfirmedOrder->is_confirmed);
    }

    public function test_can_filter_orders_by_status(): void
    {
        $customer = $this->createCustomer();

        Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 100,
            'sub_total' => 100,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 200,
            'sub_total' => 200,
            'status' => OrderStatusEnum::PROCESSING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 300,
            'sub_total' => 300,
            'status' => OrderStatusEnum::COMPLETED,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
            'completed_at' => now(),
        ]);

        $pendingOrders = Order::query()->where('status', OrderStatusEnum::PENDING)->get();
        $processingOrders = Order::query()->where('status', OrderStatusEnum::PROCESSING)->get();
        $completedOrders = Order::query()->where('status', OrderStatusEnum::COMPLETED)->get();

        $this->assertCount(1, $pendingOrders);
        $this->assertCount(1, $processingOrders);
        $this->assertCount(1, $completedOrders);
    }

    public function test_order_with_description(): void
    {
        $customer = $this->createCustomer();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 100,
            'sub_total' => 100,
            'description' => 'Please deliver before 5pm',
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $this->assertEquals('Please deliver before 5pm', $order->description);
    }

    public function test_order_amount_format(): void
    {
        $customer = $this->createCustomer();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 1000,
            'sub_total' => 1000,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $this->assertNotEmpty($order->amount_format);
    }

    public function test_can_filter_orders_by_customer(): void
    {
        $customer1 = Customer::query()->create([
            'name' => 'Customer 1',
            'email' => 'customer1@example.com',
            'password' => bcrypt('password'),
        ]);

        $customer2 = Customer::query()->create([
            'name' => 'Customer 2',
            'email' => 'customer2@example.com',
            'password' => bcrypt('password'),
        ]);

        Order::query()->create([
            'user_id' => $customer1->id,
            'amount' => 100,
            'sub_total' => 100,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        Order::query()->create([
            'user_id' => $customer1->id,
            'amount' => 200,
            'sub_total' => 200,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        Order::query()->create([
            'user_id' => $customer2->id,
            'amount' => 300,
            'sub_total' => 300,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $customer1Orders = Order::query()->where('user_id', $customer1->id)->get();
        $customer2Orders = Order::query()->where('user_id', $customer2->id)->get();

        $this->assertCount(2, $customer1Orders);
        $this->assertCount(1, $customer2Orders);
    }

    public function test_order_free_shipping(): void
    {
        $customer = $this->createCustomer();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 100,
            'sub_total' => 100,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'coupon_code' => 'FREESHIP',
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $this->assertTrue($order->is_free_shipping);
    }

    public function test_order_with_private_notes(): void
    {
        $customer = $this->createCustomer();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 100,
            'sub_total' => 100,
            'private_notes' => 'VIP customer - handle with care',
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $this->assertEquals('VIP customer - handle with care', $order->private_notes);
    }
}
