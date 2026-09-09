<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Enums\StockStatusEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class CancelPendingOrdersCommandTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        Setting::set('ecommerce_auto_cancel_pending_orders_enabled', '1');
        Setting::set('ecommerce_auto_cancel_pending_orders_threshold_minutes', 30);
    }

    protected function createProduct(int $quantity = 10): Product
    {
        return Product::query()->create([
            'name' => 'Test Product ' . uniqid(),
            'price' => 100,
            'quantity' => $quantity,
            'with_storehouse_management' => true,
            'allow_checkout_when_out_of_stock' => false,
            'stock_status' => StockStatusEnum::IN_STOCK,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    protected function createOrder(
        Product $product,
        int $qty,
        string $createdAt,
        bool $isFinished = false,
        string $status = OrderStatusEnum::PENDING,
        ?int $paymentId = null,
    ): Order {
        $order = Order::query()->create([
            'amount' => $product->price * $qty,
            'sub_total' => $product->price * $qty,
            'status' => $status,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => $isFinished,
        ]);

        // created_at and payment_id are not in $fillable - assign directly.
        $order->forceFill([
            'created_at' => $createdAt,
            'payment_id' => $paymentId,
        ])->save();

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_image' => 'product.jpg',
            'qty' => $qty,
            'price' => $product->price,
        ]);

        return $order->fresh();
    }

    protected function createPayment(string $channel, int $orderId): Payment
    {
        return Payment::query()->create([
            'currency' => 'USD',
            'amount' => 100,
            'order_id' => $orderId,
            'charge_id' => 'TEST_' . uniqid(),
            'payment_channel' => $channel,
            'status' => PaymentStatusEnum::PENDING,
        ]);
    }

    public function test_does_nothing_when_feature_disabled(): void
    {
        Setting::set('ecommerce_auto_cancel_pending_orders_enabled', '0');

        $product = $this->createProduct(quantity: 5);
        $order = $this->createOrder(
            product: $product,
            qty: 2,
            createdAt: now()->subHour()->toDateTimeString(),
        );

        $this->artisan('cms:ecommerce:cancel-pending-orders')
            ->expectsOutputToContain('Auto-cancel for pending orders is disabled')
            ->assertSuccessful();

        $this->assertEquals(OrderStatusEnum::PENDING, $order->fresh()->status->getValue());
        $this->assertEquals(5, $product->fresh()->quantity);
    }

    public function test_cancels_abandoned_online_payment_order_and_restores_stock(): void
    {
        $product = $this->createProduct(quantity: 5);
        $order = $this->createOrder(
            product: $product,
            qty: 2,
            createdAt: now()->subHour()->toDateTimeString(),
        );

        $this->artisan('cms:ecommerce:cancel-pending-orders')->assertSuccessful();

        $this->assertEquals(OrderStatusEnum::CANCELED, $order->fresh()->status->getValue());
        $this->assertEquals(7, $product->fresh()->quantity);
    }

    public function test_skips_cod_orders(): void
    {
        $product = $this->createProduct(quantity: 5);
        $order = $this->createOrder(
            product: $product,
            qty: 2,
            createdAt: now()->subHour()->toDateTimeString(),
        );
        $payment = $this->createPayment(PaymentMethodEnum::COD, $order->id);
        $order->forceFill(['payment_id' => $payment->id])->save();

        $this->artisan('cms:ecommerce:cancel-pending-orders')->assertSuccessful();

        $this->assertEquals(OrderStatusEnum::PENDING, $order->fresh()->status->getValue());
        $this->assertEquals(5, $product->fresh()->quantity);
    }

    public function test_skips_bank_transfer_orders(): void
    {
        $product = $this->createProduct(quantity: 5);
        $order = $this->createOrder(
            product: $product,
            qty: 2,
            createdAt: now()->subHour()->toDateTimeString(),
        );
        $payment = $this->createPayment(PaymentMethodEnum::BANK_TRANSFER, $order->id);
        $order->forceFill(['payment_id' => $payment->id])->save();

        $this->artisan('cms:ecommerce:cancel-pending-orders')->assertSuccessful();

        $this->assertEquals(OrderStatusEnum::PENDING, $order->fresh()->status->getValue());
        $this->assertEquals(5, $product->fresh()->quantity);
    }

    public function test_skips_orders_younger_than_threshold(): void
    {
        $product = $this->createProduct(quantity: 5);
        $order = $this->createOrder(
            product: $product,
            qty: 2,
            createdAt: now()->subMinutes(10)->toDateTimeString(),
        );

        $this->artisan('cms:ecommerce:cancel-pending-orders')->assertSuccessful();

        $this->assertEquals(OrderStatusEnum::PENDING, $order->fresh()->status->getValue());
        $this->assertEquals(5, $product->fresh()->quantity);
    }

    public function test_skips_finished_orders(): void
    {
        $product = $this->createProduct(quantity: 5);
        $order = $this->createOrder(
            product: $product,
            qty: 2,
            createdAt: now()->subHour()->toDateTimeString(),
            isFinished: true,
        );

        $this->artisan('cms:ecommerce:cancel-pending-orders')->assertSuccessful();

        $this->assertEquals(OrderStatusEnum::PENDING, $order->fresh()->status->getValue());
        $this->assertEquals(5, $product->fresh()->quantity);
    }

    public function test_does_not_touch_already_cancelled_orders(): void
    {
        $product = $this->createProduct(quantity: 5);
        $order = $this->createOrder(
            product: $product,
            qty: 2,
            createdAt: now()->subHour()->toDateTimeString(),
            status: OrderStatusEnum::CANCELED,
        );

        $this->artisan('cms:ecommerce:cancel-pending-orders')->assertSuccessful();

        $this->assertEquals(OrderStatusEnum::CANCELED, $order->fresh()->status->getValue());
        $this->assertEquals(5, $product->fresh()->quantity);
    }

    public function test_threshold_override_option(): void
    {
        Setting::set('ecommerce_auto_cancel_pending_orders_threshold_minutes', 1440);

        $product = $this->createProduct(quantity: 5);
        $order = $this->createOrder(
            product: $product,
            qty: 2,
            createdAt: now()->subMinutes(45)->toDateTimeString(),
        );

        $this->artisan('cms:ecommerce:cancel-pending-orders', ['--threshold' => 30])
            ->assertSuccessful();

        $this->assertEquals(OrderStatusEnum::CANCELED, $order->fresh()->status->getValue());
        $this->assertEquals(7, $product->fresh()->quantity);
    }

    public function test_processes_multiple_eligible_orders_in_one_run(): void
    {
        $productA = $this->createProduct(quantity: 10);
        $productB = $this->createProduct(quantity: 10);

        $orderA = $this->createOrder(
            product: $productA,
            qty: 3,
            createdAt: now()->subHour()->toDateTimeString(),
        );

        $orderB = $this->createOrder(
            product: $productB,
            qty: 4,
            createdAt: now()->subHours(2)->toDateTimeString(),
        );

        $this->artisan('cms:ecommerce:cancel-pending-orders')->assertSuccessful();

        $this->assertEquals(OrderStatusEnum::CANCELED, $orderA->fresh()->status->getValue());
        $this->assertEquals(OrderStatusEnum::CANCELED, $orderB->fresh()->status->getValue());
        $this->assertEquals(13, $productA->fresh()->quantity);
        $this->assertEquals(14, $productB->fresh()->quantity);
    }

    public function test_records_cancellation_reason(): void
    {
        $product = $this->createProduct(quantity: 5);
        $order = $this->createOrder(
            product: $product,
            qty: 2,
            createdAt: now()->subHour()->toDateTimeString(),
        );

        $this->artisan('cms:ecommerce:cancel-pending-orders')->assertSuccessful();

        $this->assertEquals('payment-issues', $order->fresh()->cancellation_reason);
    }
}
