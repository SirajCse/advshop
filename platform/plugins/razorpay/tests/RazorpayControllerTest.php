<?php

namespace Botble\Razorpay\Tests;

use Botble\Ecommerce\Enums\OrderAddressTypeEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Razorpay\Http\Controllers\RazorpayController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Tests\TestCase;

class RazorpayControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected RazorpayController $controller;

    protected string $uid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uid = uniqid();

        if (! defined('RAZORPAY_PAYMENT_METHOD_NAME')) {
            define('RAZORPAY_PAYMENT_METHOD_NAME', 'razorpay');
        }

        // Anonymous subclass exposes all protected methods for unit testing
        $this->controller = new class extends RazorpayController
        {
            public function testAreOrdersAlreadyFinished(array|string|null $orderId): bool
            {
                return $this->areOrdersAlreadyFinished($orderId);
            }

            public function testResolveOrderId(
                Request $request,
                string $token,
                ?object $paymentData = null,
                ?array $orderData = null
            ): array|string|null {
                return $this->resolveOrderId($request, $token, $paymentData, $orderData);
            }

            public function testResolveOrderIdFromWebhook(
                array $paymentEntity,
                array $orderData,
                ?int $existingPaymentOrderId = null
            ): array|string|null {
                return $this->resolveOrderIdFromWebhook($paymentEntity, $orderData, $existingPaymentOrderId);
            }

            public function testGetCustomerInfoFromOrder(array|string|null $orderId): array
            {
                return $this->getCustomerInfoFromOrder($orderId);
            }

            public function testLinkPaymentWithOrder($chargeId, $orderId, $status = null): void
            {
                $this->linkPaymentWithOrder($chargeId, $orderId, $status);
            }

            public function testSaveOrUpdatePayment(
                string $chargeId,
                array|string|null $orderId,
                float $amount,
                string $currency,
                ?string $status,
                ?int $customerId = null,
                ?string $customerType = null,
                string $context = 'callback'
            ): void {
                $this->saveOrUpdatePayment($chargeId, $orderId, $amount, $currency, $status, $customerId, $customerType, $context);
            }

            public function testFinalizeOrders(array|string|null $orderId, string $chargeId): void
            {
                $this->finalizeOrders($orderId, $chargeId);
            }

            public function testBackfillOrderContactFromGateway(
                array|string|null $orderId,
                ?string $email,
                ?string $phone,
                ?string $name = null
            ): void {
                $this->backfillOrderContactFromGateway($orderId, $email, $phone, $name);
            }

            public function testFlagUnderpaidOrder(array|string|null $orderId, float $capturedAmount): void
            {
                $this->flagUnderpaidOrder($orderId, $capturedAmount);
            }

            public function testVerifyWebhookSignature(string $content, string $signature, string $secret): bool
            {
                return $this->verifyWebhookSignature($content, $signature, $secret);
            }

            public function testDeterminePaymentStatus(array $paymentEntity, array $orderData, Api $api, string $chargeId): string
            {
                return $this->determinePaymentStatus($paymentEntity, $orderData, $api, $chargeId);
            }
        };
    }

    /** Generate a unique charge ID scoped to this test run. */
    protected function chargeId(string $label = ''): string
    {
        return 'pay_' . $label . '_' . $this->uid;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    protected function createOrder(array $attributes = []): Order
    {
        return Order::query()->create(array_merge([
            'amount' => 1000,
            'sub_total' => 1000,
            'is_finished' => false,
            'token' => 'test-token-' . uniqid(),
        ], $attributes));
    }

    protected function createPayment(array $attributes = []): Payment
    {
        return Payment::query()->create(array_merge([
            'charge_id' => 'pay_' . uniqid(),
            'amount' => 1000,
            'currency' => 'INR',
            'payment_channel' => RAZORPAY_PAYMENT_METHOD_NAME,
            'status' => PaymentStatusEnum::PENDING,
        ], $attributes));
    }

    // ═════════════════════════════════════════════════════════════════════
    //  areOrdersAlreadyFinished
    // ═════════════════════════════════════════════════════════════════════

    public function test_are_orders_finished_null_returns_false(): void
    {
        $this->assertFalse($this->controller->testAreOrdersAlreadyFinished(null));
    }

    public function test_are_orders_finished_empty_array_returns_false(): void
    {
        $this->assertFalse($this->controller->testAreOrdersAlreadyFinished([]));
    }

    public function test_are_orders_finished_all_finished_returns_true(): void
    {
        $o1 = $this->createOrder(['is_finished' => true]);
        $o2 = $this->createOrder(['is_finished' => true]);

        $this->assertTrue($this->controller->testAreOrdersAlreadyFinished([$o1->id, $o2->id]));
    }

    public function test_are_orders_finished_mixed_returns_false(): void
    {
        $o1 = $this->createOrder(['is_finished' => true]);
        $o2 = $this->createOrder(['is_finished' => false]);

        $this->assertFalse($this->controller->testAreOrdersAlreadyFinished([$o1->id, $o2->id]));
    }

    public function test_are_orders_finished_single_string_id(): void
    {
        $order = $this->createOrder(['is_finished' => true]);

        $this->assertTrue($this->controller->testAreOrdersAlreadyFinished((string) $order->id));
    }

    public function test_are_orders_finished_single_unfinished(): void
    {
        $order = $this->createOrder(['is_finished' => false]);

        $this->assertFalse($this->controller->testAreOrdersAlreadyFinished([$order->id]));
    }

    // ═════════════════════════════════════════════════════════════════════
    //  resolveOrderId (callback flow)
    // ═════════════════════════════════════════════════════════════════════

    public function test_resolve_order_id_from_request_input(): void
    {
        $request = Request::create('/', 'POST', ['order_id' => '5']);

        $this->assertEquals('5', $this->controller->testResolveOrderId($request, 'any'));
    }

    public function test_resolve_order_id_array_from_request(): void
    {
        $request = Request::create('/', 'POST', ['order_id' => [3, 4]]);

        $this->assertEquals([3, 4], $this->controller->testResolveOrderId($request, 'any'));
    }

    public function test_resolve_order_id_from_payment_notes_single(): void
    {
        $paymentData = (object) ['notes' => (object) ['order_id' => '7']];
        $request = Request::create('/', 'POST');

        $this->assertEquals('7', $this->controller->testResolveOrderId($request, 'any', $paymentData));
    }

    public function test_resolve_order_id_from_payment_notes_csv(): void
    {
        $paymentData = (object) ['notes' => (object) ['order_id' => '7,8,9']];
        $request = Request::create('/', 'POST');

        $this->assertEquals(['7', '8', '9'], $this->controller->testResolveOrderId($request, 'any', $paymentData));
    }

    public function test_resolve_order_id_from_payment_notes_order_token(): void
    {
        $tok = 'note-token-' . $this->uid;
        $order = $this->createOrder(['token' => $tok]);
        $paymentData = (object) ['notes' => (object) ['order_token' => $tok]];
        $request = Request::create('/', 'POST');

        $this->assertEquals([$order->id], $this->controller->testResolveOrderId($request, 'other', $paymentData));
    }

    public function test_resolve_order_id_from_receipt(): void
    {
        $tok = 'receipt-tok-' . $this->uid;
        $order = $this->createOrder(['token' => $tok]);
        $request = Request::create('/', 'POST');

        $result = $this->controller->testResolveOrderId($request, 'other', null, ['receipt' => $tok]);

        $this->assertEquals([$order->id], $result);
    }

    public function test_resolve_order_id_from_token_fallback(): void
    {
        $tok = 'fallback-tok-' . $this->uid;
        $order = $this->createOrder(['token' => $tok]);
        $request = Request::create('/', 'POST');

        $this->assertEquals([$order->id], $this->controller->testResolveOrderId($request, $tok));
    }

    public function test_resolve_order_id_returns_null_when_nothing_matches(): void
    {
        $request = Request::create('/', 'POST');

        $this->assertNull($this->controller->testResolveOrderId($request, 'nonexistent'));
    }

    // ═════════════════════════════════════════════════════════════════════
    //  resolveOrderIdFromWebhook
    // ═════════════════════════════════════════════════════════════════════

    public function test_webhook_resolve_prefers_existing_payment_order_id(): void
    {
        $result = $this->controller->testResolveOrderIdFromWebhook(
            ['notes' => []],
            ['receipt' => 'x'],
            42
        );

        $this->assertEquals(42, $result);
    }

    public function test_webhook_resolve_from_receipt(): void
    {
        $tok = 'wh-receipt-' . $this->uid;
        $order = $this->createOrder(['token' => $tok]);

        $result = $this->controller->testResolveOrderIdFromWebhook(
            ['notes' => []],
            ['receipt' => $tok]
        );

        $this->assertEquals([$order->id], $result);
    }

    public function test_webhook_resolve_from_notes_order_token(): void
    {
        $tok = 'wh-note-tok-' . $this->uid;
        $order = $this->createOrder(['token' => $tok]);

        $result = $this->controller->testResolveOrderIdFromWebhook(
            ['notes' => ['order_token' => $tok]],
            ['receipt' => 'nonexistent']
        );

        $this->assertEquals([$order->id], $result);
    }

    public function test_webhook_resolve_from_notes_order_id_single(): void
    {
        $result = $this->controller->testResolveOrderIdFromWebhook(
            ['notes' => ['order_id' => '15']],
            ['receipt' => 'nonexistent']
        );

        $this->assertEquals(['15'], $result);
    }

    public function test_webhook_resolve_from_notes_order_id_csv(): void
    {
        $result = $this->controller->testResolveOrderIdFromWebhook(
            ['notes' => ['order_id' => '15,16']],
            ['receipt' => 'nonexistent']
        );

        $this->assertEquals(['15', '16'], $result);
    }

    public function test_webhook_resolve_returns_null_when_nothing_matches(): void
    {
        $result = $this->controller->testResolveOrderIdFromWebhook(
            ['notes' => []],
            ['receipt' => 'nonexistent']
        );

        $this->assertNull($result);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  getCustomerInfoFromOrder
    // ═════════════════════════════════════════════════════════════════════

    public function test_customer_info_null_order_returns_empty(): void
    {
        $this->assertEmpty($this->controller->testGetCustomerInfoFromOrder(null));
    }

    public function test_customer_info_empty_array_returns_empty(): void
    {
        $this->assertEmpty($this->controller->testGetCustomerInfoFromOrder([]));
    }

    public function test_customer_info_guest_order_returns_empty(): void
    {
        $order = $this->createOrder(['user_id' => 0]);

        $this->assertEmpty($this->controller->testGetCustomerInfoFromOrder([$order->id]));
    }

    public function test_customer_info_logged_in_returns_data(): void
    {
        $order = $this->createOrder(['user_id' => 42]);

        $result = $this->controller->testGetCustomerInfoFromOrder([$order->id]);

        $this->assertEquals(42, $result['customer_id']);
        $this->assertArrayHasKey('customer_type', $result);
    }

    public function test_customer_info_multiple_orders_uses_first(): void
    {
        $o1 = $this->createOrder(['user_id' => 10]);
        $o2 = $this->createOrder(['user_id' => 20]);

        $result = $this->controller->testGetCustomerInfoFromOrder([$o1->id, $o2->id]);

        $this->assertEquals(10, $result['customer_id']);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  linkPaymentWithOrder
    // ═════════════════════════════════════════════════════════════════════

    public function test_link_orphaned_payment_to_order(): void
    {
        $payment = $this->createPayment(['charge_id' => $this->chargeId('orphan'), 'order_id' => null]);
        $order = $this->createOrder();

        $this->controller->testLinkPaymentWithOrder($this->chargeId('orphan'), [$order->id], PaymentStatusEnum::COMPLETED);

        $payment->refresh();
        $this->assertEquals($order->id, $payment->order_id);
        $this->assertEquals(PaymentStatusEnum::COMPLETED, $payment->status);
    }

    public function test_link_does_not_overwrite_existing_order_id(): void
    {
        $payment = $this->createPayment([
            'charge_id' => $this->chargeId('linked'),
            'order_id' => 99,
            'status' => PaymentStatusEnum::COMPLETED,
        ]);

        $this->controller->testLinkPaymentWithOrder($this->chargeId('linked'), [100], PaymentStatusEnum::COMPLETED);

        $payment->refresh();
        $this->assertEquals(99, $payment->order_id);
    }

    public function test_link_only_updates_status_when_pending(): void
    {
        $payment = $this->createPayment([
            'charge_id' => $this->chargeId('status'),
            'order_id' => null,
            'status' => PaymentStatusEnum::COMPLETED,
        ]);

        $order = $this->createOrder();

        // Status is already COMPLETED, passing a different status shouldn't change it
        $this->controller->testLinkPaymentWithOrder($this->chargeId('status'), [$order->id], PaymentStatusEnum::PENDING);

        $payment->refresh();
        // order_id should be set, but status stays COMPLETED (not downgraded to PENDING)
        $this->assertEquals($order->id, $payment->order_id);
        $this->assertEquals(PaymentStatusEnum::COMPLETED, $payment->status);
    }

    public function test_link_noop_for_null_charge_id(): void
    {
        $this->controller->testLinkPaymentWithOrder(null, [1], PaymentStatusEnum::COMPLETED);
        $this->assertTrue(true); // no exception
    }

    public function test_link_noop_for_null_order_id(): void
    {
        $this->controller->testLinkPaymentWithOrder($this->chargeId('x'), null, PaymentStatusEnum::COMPLETED);
        $this->assertTrue(true);
    }

    public function test_link_uses_first_element_of_array_order_id(): void
    {
        $payment = $this->createPayment(['charge_id' => $this->chargeId('arr'), 'order_id' => null]);

        $this->controller->testLinkPaymentWithOrder($this->chargeId('arr'), [50, 51], PaymentStatusEnum::COMPLETED);

        $payment->refresh();
        $this->assertEquals(50, $payment->order_id);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  saveOrUpdatePayment
    // ═════════════════════════════════════════════════════════════════════

    public function test_save_creates_new_payment(): void
    {
        $order = $this->createOrder();

        $this->controller->testSaveOrUpdatePayment(
            $this->chargeId('new'),
            [$order->id],
            1000,
            'INR',
            PaymentStatusEnum::COMPLETED,
            42,
            'Botble\Ecommerce\Models\Customer'
        );

        $payment = Payment::query()->where('charge_id', $this->chargeId('new'))->first();
        $this->assertNotNull($payment);
        $this->assertEquals($order->id, $payment->order_id);
        $this->assertEquals(1000, $payment->amount);
        $this->assertEquals(PaymentStatusEnum::COMPLETED, $payment->status);
        $this->assertEquals(42, $payment->customer_id);
    }

    public function test_save_updates_existing_status(): void
    {
        $order = $this->createOrder();
        $this->createPayment([
            'charge_id' => $this->chargeId('upd'),
            'status' => PaymentStatusEnum::PENDING,
            'order_id' => $order->id,
        ]);

        $this->controller->testSaveOrUpdatePayment(
            $this->chargeId('upd'), [$order->id], 1000, 'INR', PaymentStatusEnum::COMPLETED
        );

        $count = Payment::query()->where('charge_id', $this->chargeId('upd'))->count();
        $this->assertEquals(1, $count);

        $payment = Payment::query()->where('charge_id', $this->chargeId('upd'))->first();
        $this->assertEquals(PaymentStatusEnum::COMPLETED, $payment->status);
    }

    public function test_save_updates_zero_amount(): void
    {
        $order = $this->createOrder();
        $this->createPayment([
            'charge_id' => $this->chargeId('zero'),
            'amount' => 0,
            'order_id' => $order->id,
        ]);

        $this->controller->testSaveOrUpdatePayment(
            $this->chargeId('zero'), [$order->id], 500, 'INR', PaymentStatusEnum::COMPLETED
        );

        $payment = Payment::query()->where('charge_id', $this->chargeId('zero'))->first();
        $this->assertEquals(500, $payment->amount);
    }

    public function test_save_fills_missing_customer_info(): void
    {
        $order = $this->createOrder();
        $this->createPayment([
            'charge_id' => $this->chargeId('cust'),
            'order_id' => $order->id,
            'customer_id' => null,
        ]);

        $this->controller->testSaveOrUpdatePayment(
            $this->chargeId('cust'), [$order->id], 1000, 'INR', PaymentStatusEnum::COMPLETED, 55, 'Customer'
        );

        $payment = Payment::query()->where('charge_id', $this->chargeId('cust'))->first();
        $this->assertEquals(55, $payment->customer_id);
        $this->assertEquals('Customer', $payment->customer_type);
    }

    public function test_save_does_not_overwrite_existing_customer(): void
    {
        $order = $this->createOrder();
        $this->createPayment([
            'charge_id' => $this->chargeId('no_overwrite'),
            'order_id' => $order->id,
            'customer_id' => 10,
            'customer_type' => 'Original',
        ]);

        $this->controller->testSaveOrUpdatePayment(
            $this->chargeId('no_overwrite'), [$order->id], 1000, 'INR', PaymentStatusEnum::COMPLETED, 99, 'Attacker'
        );

        $payment = Payment::query()->where('charge_id', $this->chargeId('no_overwrite'))->first();
        $this->assertEquals(10, $payment->customer_id);
        $this->assertEquals('Original', $payment->customer_type);
    }

    public function test_save_refreshes_stale_charge_id_on_successful_retry(): void
    {
        // A failed first attempt left a payment row holding its (failed) charge id
        // on the order. The successful retry must overwrite it with the real charge.
        $order = $this->createOrder();
        $this->createPayment([
            'charge_id' => $this->chargeId('failed_attempt'),
            'amount' => 0,
            'status' => PaymentStatusEnum::FAILED,
            'order_id' => $order->id,
        ]);

        $this->controller->testSaveOrUpdatePayment(
            $this->chargeId('successful_retry'), [$order->id], 1370, 'INR', PaymentStatusEnum::COMPLETED
        );

        // Still one row (matched by order_id), now carrying the successful charge + amount.
        $this->assertEquals(0, Payment::query()->where('charge_id', $this->chargeId('failed_attempt'))->count());

        $payment = Payment::query()->where('charge_id', $this->chargeId('successful_retry'))->first();
        $this->assertNotNull($payment);
        $this->assertEquals($order->id, $payment->order_id);
        $this->assertEquals(1370, $payment->amount);
        $this->assertEquals(PaymentStatusEnum::COMPLETED, $payment->status);
    }

    public function test_backfill_fills_blank_shipping_address(): void
    {
        $order = $this->createOrder();
        OrderAddress::query()->create([
            'order_id' => $order->id,
            'type' => OrderAddressTypeEnum::SHIPPING,
            'name' => '',
            'email' => '',
            'phone' => '',
        ]);

        $this->controller->testBackfillOrderContactFromGateway(
            [$order->id], 'latikasharma50@gmail.com', '+919953093027', 'Latika Sharma'
        );

        $address = OrderAddress::query()
            ->where('order_id', $order->id)
            ->where('type', OrderAddressTypeEnum::SHIPPING)
            ->first();

        $this->assertEquals('Latika Sharma', $address->name);
        $this->assertEquals('latikasharma50@gmail.com', $address->email);
        $this->assertEquals('+919953093027', $address->phone);
        // No duplicate shipping rows created.
        $this->assertEquals(1, OrderAddress::query()
            ->where('order_id', $order->id)
            ->where('type', OrderAddressTypeEnum::SHIPPING)
            ->count());
    }

    public function test_backfill_creates_shipping_row_when_missing(): void
    {
        $order = $this->createOrder();

        $this->controller->testBackfillOrderContactFromGateway(
            [$order->id], 'buyer@example.com', '+919999999999', 'Buyer Name'
        );

        $address = OrderAddress::query()
            ->where('order_id', $order->id)
            ->where('type', OrderAddressTypeEnum::SHIPPING)
            ->first();

        $this->assertNotNull($address);
        $this->assertEquals('buyer@example.com', $address->email);
    }

    public function test_backfill_does_not_overwrite_existing_contact(): void
    {
        $order = $this->createOrder();
        OrderAddress::query()->create([
            'order_id' => $order->id,
            'type' => OrderAddressTypeEnum::SHIPPING,
            'name' => 'Real Buyer',
            'email' => 'real@buyer.com',
            'phone' => '+910000000000',
        ]);

        $this->controller->testBackfillOrderContactFromGateway(
            [$order->id], 'attacker@evil.com', '+911111111111', 'Attacker'
        );

        $address = OrderAddress::query()
            ->where('order_id', $order->id)
            ->where('type', OrderAddressTypeEnum::SHIPPING)
            ->first();

        $this->assertEquals('Real Buyer', $address->name);
        $this->assertEquals('real@buyer.com', $address->email);
        $this->assertEquals('+910000000000', $address->phone);
    }

    public function test_backfill_noop_when_no_contact(): void
    {
        $order = $this->createOrder();

        $this->controller->testBackfillOrderContactFromGateway([$order->id], null, null, null);

        $this->assertEquals(0, OrderAddress::query()->where('order_id', $order->id)->count());
    }

    public function test_underpaid_order_records_captured_amount_and_flags(): void
    {
        // Order total is 2,620 but Razorpay only captured 1,370 (the failed-then-retry case).
        $payment = $this->createPayment(['charge_id' => $this->chargeId('underpaid'), 'amount' => 2620]);
        $order = $this->createOrder(['amount' => 2620, 'sub_total' => 2620]);
        $order->payment_id = $payment->id;
        $order->save();

        $this->controller->testFlagUnderpaidOrder([$order->id], 1370.0);

        // Payment now reflects the true captured amount, so the order is not fully paid.
        $this->assertEquals(1370, (float) $payment->fresh()->amount);

        // A review flag was recorded in the order's private notes.
        $this->assertStringContainsString('Razorpay captured', (string) $order->fresh()->private_notes);
    }

    public function test_underpaid_flag_is_idempotent(): void
    {
        $payment = $this->createPayment(['charge_id' => $this->chargeId('idem'), 'amount' => 2620]);
        $order = $this->createOrder(['amount' => 2620, 'sub_total' => 2620]);
        $order->payment_id = $payment->id;
        $order->save();

        // Callback + webhook can both run.
        $this->controller->testFlagUnderpaidOrder([$order->id], 1370.0);
        $this->controller->testFlagUnderpaidOrder([$order->id], 1370.0);

        // Note appended only once.
        $this->assertEquals(1, substr_count((string) $order->fresh()->private_notes, 'Razorpay captured'));
    }

    public function test_fully_paid_order_not_flagged(): void
    {
        $payment = $this->createPayment(['charge_id' => $this->chargeId('fullpaid'), 'amount' => 1000]);
        $order = $this->createOrder(['amount' => 1000, 'sub_total' => 1000]);
        $order->payment_id = $payment->id;
        $order->save();

        $this->controller->testFlagUnderpaidOrder([$order->id], 1000.0);

        $this->assertEquals(1000, (float) $payment->fresh()->amount);
        $this->assertStringNotContainsString('Razorpay captured', (string) $order->fresh()->private_notes);
    }

    public function test_underpaid_guard_skips_split_orders(): void
    {
        $p1 = $this->createPayment(['charge_id' => $this->chargeId('split1'), 'amount' => 2000]);
        $p2 = $this->createPayment(['charge_id' => $this->chargeId('split2'), 'amount' => 2000]);
        $o1 = $this->createOrder(['amount' => 2000, 'sub_total' => 2000]);
        $o1->payment_id = $p1->id;
        $o1->save();
        $o2 = $this->createOrder(['amount' => 2000, 'sub_total' => 2000]);
        $o2->payment_id = $p2->id;
        $o2->save();

        $this->controller->testFlagUnderpaidOrder([$o1->id, $o2->id], 2000.0);

        // Split charges are left untouched.
        $this->assertEquals(2000, (float) $p1->fresh()->amount);
        $this->assertEquals(2000, (float) $p2->fresh()->amount);
        $this->assertStringNotContainsString('Razorpay captured', (string) $o1->fresh()->private_notes);
        $this->assertStringNotContainsString('Razorpay captured', (string) $o2->fresh()->private_notes);
    }

    public function test_save_handles_multiple_order_ids(): void
    {
        $o1 = $this->createOrder(['amount' => 300, 'sub_total' => 300]);
        $o2 = $this->createOrder(['amount' => 700, 'sub_total' => 700]);

        $this->controller->testSaveOrUpdatePayment(
            $this->chargeId('multi'), [$o1->id, $o2->id], 1000, 'INR', PaymentStatusEnum::COMPLETED
        );

        $payments = Payment::query()->where('charge_id', $this->chargeId('multi'))->get();
        $this->assertCount(2, $payments);

        $this->assertEquals(300, $payments->firstWhere('order_id', $o1->id)->amount);
        $this->assertEquals(700, $payments->firstWhere('order_id', $o2->id)->amount);
    }

    public function test_save_with_null_order_id(): void
    {
        $this->controller->testSaveOrUpdatePayment(
            $this->chargeId('null_order'), null, 1000, 'INR', PaymentStatusEnum::PENDING
        );

        $payment = Payment::query()->where('charge_id', $this->chargeId('null_order'))->first();
        $this->assertNotNull($payment);
        $this->assertNull($payment->order_id);
    }

    public function test_save_fills_missing_order_id(): void
    {
        $order = $this->createOrder();

        // Create payment without order_id, matched by charge_id
        $this->createPayment([
            'charge_id' => $this->chargeId('fill_order'),
            'order_id' => null,
        ]);

        $this->controller->testSaveOrUpdatePayment(
            $this->chargeId('fill_order'), [$order->id], 1000, 'INR', PaymentStatusEnum::COMPLETED
        );

        $payment = Payment::query()->where('charge_id', $this->chargeId('fill_order'))->first();
        $this->assertEquals($order->id, $payment->order_id);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  finalizeOrders
    // ═════════════════════════════════════════════════════════════════════

    public function test_finalize_marks_orders_finished(): void
    {
        $order = $this->createOrder(['is_finished' => false]);

        $this->controller->testFinalizeOrders([$order->id], $this->chargeId('fin'));

        $order->refresh();
        $this->assertTrue((bool) $order->is_finished);
    }

    public function test_finalize_skips_already_finished(): void
    {
        $order = $this->createOrder(['is_finished' => true]);

        // Should not throw or change anything
        $this->controller->testFinalizeOrders([$order->id], $this->chargeId('fin2'));

        $order->refresh();
        $this->assertTrue((bool) $order->is_finished);
    }

    public function test_finalize_noop_for_null(): void
    {
        $this->controller->testFinalizeOrders(null, $this->chargeId('fin3'));
        $this->assertTrue(true);
    }

    public function test_finalize_noop_for_empty_array(): void
    {
        $this->controller->testFinalizeOrders([], $this->chargeId('fin4'));
        $this->assertTrue(true);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  verifyWebhookSignature
    // ═════════════════════════════════════════════════════════════════════

    public function test_webhook_signature_valid(): void
    {
        $content = '{"event":"payment.captured"}';
        $secret = 'test_secret';
        $signature = hash_hmac('sha256', $content, $secret);

        $this->assertTrue($this->controller->testVerifyWebhookSignature($content, $signature, $secret));
    }

    public function test_webhook_signature_invalid(): void
    {
        $this->assertFalse(
            $this->controller->testVerifyWebhookSignature('content', 'wrong_signature', 'secret')
        );
    }

    public function test_webhook_signature_tampered_content(): void
    {
        $secret = 'test_secret';
        $signature = hash_hmac('sha256', 'original', $secret);

        $this->assertFalse($this->controller->testVerifyWebhookSignature('tampered', $signature, $secret));
    }

    // ═════════════════════════════════════════════════════════════════════
    //  determinePaymentStatus
    // ═════════════════════════════════════════════════════════════════════

    public function test_status_captured_returns_completed(): void
    {
        $api = $this->createMock(Api::class);

        $result = $this->controller->testDeterminePaymentStatus(
            ['status' => 'captured', 'amount' => 10000],
            ['status' => 'paid'],
            $api,
            $this->chargeId('x')
        );

        $this->assertEquals(PaymentStatusEnum::COMPLETED, $result);
    }

    public function test_status_failed_returns_failed(): void
    {
        $api = $this->createMock(Api::class);

        $result = $this->controller->testDeterminePaymentStatus(
            ['status' => 'failed', 'amount' => 10000],
            ['status' => 'attempted'],
            $api,
            $this->chargeId('x')
        );

        $this->assertEquals(PaymentStatusEnum::FAILED, $result);
    }

    public function test_status_refunded_returns_refunded(): void
    {
        $api = $this->createMock(Api::class);

        $result = $this->controller->testDeterminePaymentStatus(
            ['status' => 'refunded', 'amount' => 10000],
            ['status' => 'attempted'],
            $api,
            $this->chargeId('x')
        );

        $this->assertEquals(PaymentStatusEnum::REFUNDED, $result);
    }

    public function test_status_created_returns_pending(): void
    {
        $api = $this->createMock(Api::class);

        $result = $this->controller->testDeterminePaymentStatus(
            ['status' => 'created', 'amount' => 10000],
            ['status' => 'created'],
            $api,
            $this->chargeId('x')
        );

        $this->assertEquals(PaymentStatusEnum::PENDING, $result);
    }

    public function test_status_order_paid_returns_completed(): void
    {
        $api = $this->createMock(Api::class);

        // Even if payment status is unusual, order.status=paid → COMPLETED
        $result = $this->controller->testDeterminePaymentStatus(
            ['status' => 'created', 'amount' => 10000],
            ['status' => 'paid'],
            $api,
            $this->chargeId('x')
        );

        $this->assertEquals(PaymentStatusEnum::COMPLETED, $result);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Race condition scenarios
    // ═════════════════════════════════════════════════════════════════════

    public function test_race_finished_orders_skip_reprocessing(): void
    {
        $order = $this->createOrder(['is_finished' => true]);
        $this->createPayment([
            'charge_id' => $this->chargeId('race'),
            'status' => PaymentStatusEnum::COMPLETED,
            'order_id' => $order->id,
        ]);

        $this->assertTrue($this->controller->testAreOrdersAlreadyFinished([$order->id]));

        // Second call (simulating callback after webhook) should not create duplicate
        $this->controller->testSaveOrUpdatePayment(
            $this->chargeId('race'), [$order->id], 1000, 'INR', PaymentStatusEnum::COMPLETED
        );

        $this->assertEquals(1, Payment::query()->where('charge_id', $this->chargeId('race'))->count());
    }

    public function test_race_orphan_link_then_save_no_duplicate(): void
    {
        $order = $this->createOrder();

        // Webhook created orphaned payment
        $this->createPayment([
            'charge_id' => $this->chargeId('orphan_race'),
            'status' => PaymentStatusEnum::COMPLETED,
            'order_id' => null,
        ]);

        // Callback: link first, then save
        $this->controller->testLinkPaymentWithOrder($this->chargeId('orphan_race'), [$order->id], PaymentStatusEnum::COMPLETED);
        $this->controller->testSaveOrUpdatePayment(
            $this->chargeId('orphan_race'), [$order->id], 1000, 'INR', PaymentStatusEnum::COMPLETED
        );

        $this->assertEquals(1, Payment::query()->where('charge_id', $this->chargeId('orphan_race'))->count());

        $payment = Payment::query()->where('charge_id', $this->chargeId('orphan_race'))->first();
        $this->assertEquals($order->id, $payment->order_id);
    }

    public function test_race_callback_first_webhook_second(): void
    {
        $order = $this->createOrder();

        // Callback creates payment and finalizes
        $this->controller->testSaveOrUpdatePayment(
            $this->chargeId('cb_first'), [$order->id], 1000, 'INR', PaymentStatusEnum::COMPLETED, 5, 'Customer'
        );
        $this->controller->testFinalizeOrders([$order->id], $this->chargeId('cb_first'));

        // Webhook arrives later — order is finished
        $this->assertTrue($this->controller->testAreOrdersAlreadyFinished([$order->id]));

        // Webhook save should not create duplicate
        $this->controller->testSaveOrUpdatePayment(
            $this->chargeId('cb_first'), [$order->id], 1000, 'INR', PaymentStatusEnum::COMPLETED
        );

        $this->assertEquals(1, Payment::query()->where('charge_id', $this->chargeId('cb_first'))->count());
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Customer info priority (callback security)
    // ═════════════════════════════════════════════════════════════════════

    public function test_customer_info_order_user_takes_priority(): void
    {
        $order = $this->createOrder(['user_id' => 42]);

        // Order has user_id=42, should be preferred over any request params
        $info = $this->controller->testGetCustomerInfoFromOrder([$order->id]);
        $this->assertEquals(42, $info['customer_id']);
    }

    public function test_customer_info_guest_falls_back(): void
    {
        $order = $this->createOrder(['user_id' => 0]);

        $info = $this->controller->testGetCustomerInfoFromOrder([$order->id]);
        $this->assertEmpty($info);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Webhook endpoint (HTTP integration tests)
    // ═════════════════════════════════════════════════════════════════════

    public function test_webhook_400_without_payment_entity(): void
    {
        $response = $this->postJson('/payment/razorpay/webhook', [
            'event' => 'payment.captured',
            'payload' => [],
        ]);

        $response->assertStatus(400);
    }

    public function test_webhook_400_without_order_id_in_entity(): void
    {
        $response = $this->postJson('/payment/razorpay/webhook', [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => $this->chargeId('test'),
                        'order_id' => null,
                    ],
                ],
            ],
        ]);

        $response->assertStatus(400);
    }

    public function test_webhook_refund_event_returns_200(): void
    {
        $response = $this->postJson('/payment/razorpay/webhook', [
            'event' => 'refund.created',
            'payload' => [],
        ]);

        $response->assertStatus(200);
        $response->assertSee('Refund event received');
    }

    public function test_webhook_unhandled_event_returns_200(): void
    {
        $response = $this->postJson('/payment/razorpay/webhook', [
            'event' => 'some.unknown.event',
            'payload' => [],
        ]);

        $response->assertStatus(200);
        $response->assertSee('Event type not handled');
    }

    public function test_webhook_invalid_signature_returns_400(): void
    {
        $settingKey = get_payment_setting_key('webhook_secret', RAZORPAY_PAYMENT_METHOD_NAME);
        setting()->set([$settingKey => 'test_secret'])->save();

        $response = $this->postJson('/payment/razorpay/webhook', [
            'event' => 'payment.captured',
            'payload' => [],
        ], [
            'X-Razorpay-Signature' => 'invalid_signature',
        ]);

        $response->assertStatus(400);
    }

    public function test_webhook_valid_signature_passes(): void
    {
        $secret = 'test_webhook_secret_' . uniqid();
        $settingKey = get_payment_setting_key('webhook_secret', RAZORPAY_PAYMENT_METHOD_NAME);
        setting()->set([$settingKey => $secret])->save();

        $body = json_encode([
            'event' => 'refund.created',
            'payload' => [],
        ]);

        $signature = hash_hmac('sha256', $body, $secret);

        $response = $this->call('POST', '/payment/razorpay/webhook', [], [], [], [
            'HTTP_X-Razorpay-Signature' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $body);

        $response->assertStatus(200);
    }
}
