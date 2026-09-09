<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Payment\Supports\PaymentHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

/**
 * Covers the payment/order amount divergence discovered on live customers
 * (see cakepearls.com case — charged amount matched Razorpay but admin "Paid amount"
 * drifted from the order total because the local payment row was never reconciled).
 *
 * Fix surface:
 *   - platform/plugins/payment/src/Supports/PaymentHelper.php (storeLocalPayment)
 *   - platform/plugins/ecommerce/database/migrations/2026_04_24_000001_reconcile_payment_amount_with_order_total.php
 */
class PaymentAmountReconciliationTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function createOrder(float $amount, ?int $paymentId = null): Order
    {
        $order = Order::query()->create([
            'amount' => $amount,
            'sub_total' => $amount,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'payment_fee' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        // payment_id is not in Order::$fillable; set explicitly so Eloquent persists it.
        if ($paymentId !== null) {
            $order->payment_id = $paymentId;
            $order->save();
        }

        return $order;
    }

    protected function createPayment(array $overrides = []): Payment
    {
        return Payment::query()->create(array_merge([
            'amount' => 100,
            'currency' => 'USD',
            'charge_id' => 'ch_' . uniqid(),
            'order_id' => 0,
            'payment_channel' => PaymentMethodEnum::BANK_TRANSFER,
            'status' => PaymentStatusEnum::PENDING,
        ], $overrides));
    }

    // ========================================
    // storeLocalPayment — new row creation
    // ========================================

    public function test_creates_new_row_with_supplied_amount_when_no_existing_row(): void
    {
        $order = $this->createOrder(250);
        $chargeId = 'ch_new_' . uniqid();

        PaymentHelper::storeLocalPayment([
            'amount' => 250,
            'currency' => 'USD',
            'charge_id' => $chargeId,
            'order_id' => $order->id,
            'payment_channel' => PaymentMethodEnum::BANK_TRANSFER,
            'status' => PaymentStatusEnum::COMPLETED,
        ]);

        $payment = Payment::query()->where('charge_id', $chargeId)->first();

        $this->assertNotNull($payment);
        $this->assertEquals(250, (float) $payment->amount);
        $this->assertEquals('USD', $payment->currency);
        $this->assertSame(PaymentStatusEnum::COMPLETED, $payment->status->getValue());
    }

    // ========================================
    // storeLocalPayment — reconciliation on existing row (the core fix)
    // ========================================

    public function test_reconciles_amount_on_existing_row_when_it_diverges_from_order_total(): void
    {
        $order = $this->createOrder(220);
        $chargeId = 'ch_mismatch_' . uniqid();

        // Simulate the webhook-first scenario: row already exists with a stale amount
        $existing = $this->createPayment([
            'charge_id' => $chargeId,
            'order_id' => $order->id,
            'amount' => 420,
            'status' => PaymentStatusEnum::PENDING,
        ]);

        PaymentHelper::storeLocalPayment([
            'amount' => 220,
            'currency' => 'USD',
            'charge_id' => $chargeId,
            'order_id' => $order->id,
            'payment_channel' => PaymentMethodEnum::BANK_TRANSFER,
            'status' => PaymentStatusEnum::COMPLETED,
        ]);

        $existing->refresh();

        $this->assertEquals(220, (float) $existing->amount, 'amount should be realigned to the order total');
        $this->assertSame(PaymentStatusEnum::COMPLETED, $existing->status->getValue(), 'status should also be bumped');
    }

    public function test_reconciles_currency_on_existing_row_when_it_diverges(): void
    {
        $order = $this->createOrder(100);
        $chargeId = 'ch_curr_' . uniqid();

        $existing = $this->createPayment([
            'charge_id' => $chargeId,
            'order_id' => $order->id,
            'amount' => 100,
            'currency' => 'USD',
            'status' => PaymentStatusEnum::PENDING,
        ]);

        PaymentHelper::storeLocalPayment([
            'amount' => 100,
            'currency' => 'INR',
            'charge_id' => $chargeId,
            'order_id' => $order->id,
            'payment_channel' => PaymentMethodEnum::BANK_TRANSFER,
            'status' => PaymentStatusEnum::COMPLETED,
        ]);

        $existing->refresh();
        $this->assertEquals('INR', $existing->currency);
    }

    public function test_no_unnecessary_save_when_amount_and_status_already_match(): void
    {
        $order = $this->createOrder(150);
        $chargeId = 'ch_idem_' . uniqid();

        $existing = $this->createPayment([
            'charge_id' => $chargeId,
            'order_id' => $order->id,
            'amount' => 150,
            'currency' => 'USD',
            'status' => PaymentStatusEnum::COMPLETED,
        ]);

        $updatedBefore = $existing->updated_at;
        sleep(1); // ensure ts diff would be visible if save fired

        PaymentHelper::storeLocalPayment([
            'amount' => 150,
            'currency' => 'USD',
            'charge_id' => $chargeId,
            'order_id' => $order->id,
            'payment_channel' => PaymentMethodEnum::BANK_TRANSFER,
            'status' => PaymentStatusEnum::COMPLETED,
        ]);

        $existing->refresh();
        $this->assertEquals(
            $updatedBefore->toDateTimeString(),
            $existing->updated_at->toDateTimeString(),
            'nothing dirty → no save → updated_at unchanged'
        );
    }

    // ========================================
    // storeLocalPayment — partial-refund guard
    // ========================================

    public function test_does_not_reconcile_amount_when_partially_refunded(): void
    {
        $order = $this->createOrder(100);
        $chargeId = 'ch_refunded_' . uniqid();

        $existing = $this->createPayment([
            'charge_id' => $chargeId,
            'order_id' => $order->id,
            'amount' => 500,
            'refunded_amount' => 25, // partial refund recorded
            'status' => PaymentStatusEnum::COMPLETED,
        ]);

        PaymentHelper::storeLocalPayment([
            'amount' => 100,
            'currency' => 'USD',
            'charge_id' => $chargeId,
            'order_id' => $order->id,
            'payment_channel' => PaymentMethodEnum::BANK_TRANSFER,
            'status' => PaymentStatusEnum::COMPLETED,
        ]);

        $existing->refresh();
        $this->assertEquals(500, (float) $existing->amount, 'amount must be preserved when refunded_amount > 0');
    }

    /**
     * Regression guard: refunded_amount is a DECIMAL column with no cast, so Laravel
     * returns "0.00" for unrefunded rows. A naive `! $payment->refunded_amount` check
     * evaluates to false for "0.00" (truthy string) and would skip reconciliation for
     * every unrefunded row — reintroducing the bug.
     */
    public function test_reconciles_when_refunded_amount_is_decimal_zero_string(): void
    {
        $order = $this->createOrder(300);
        $chargeId = 'ch_zerostr_' . uniqid();

        $existing = $this->createPayment([
            'charge_id' => $chargeId,
            'order_id' => $order->id,
            'amount' => 999,
            'refunded_amount' => 0,
            'status' => PaymentStatusEnum::PENDING,
        ]);

        // Verify the column returns "0.00" (string) so the regression guard is meaningful
        $raw = DB::table('payments')->where('id', $existing->id)->value('refunded_amount');
        $this->assertTrue(
            $raw === null || (is_string($raw) ? (float) $raw === 0.0 : $raw == 0),
            'refunded_amount should be NULL or numerically zero'
        );

        PaymentHelper::storeLocalPayment([
            'amount' => 300,
            'currency' => 'USD',
            'charge_id' => $chargeId,
            'order_id' => $order->id,
            'payment_channel' => PaymentMethodEnum::BANK_TRANSFER,
            'status' => PaymentStatusEnum::COMPLETED,
        ]);

        $existing->refresh();
        $this->assertEquals(300, (float) $existing->amount, 'amount must reconcile even when refunded_amount is DECIMAL "0.00"');
    }

    // ========================================
    // Migration SQL — bulk reconciliation
    // ========================================

    public function test_migration_sql_realigns_completed_payments_with_order_totals(): void
    {
        // Seed 4 orders with linked payments whose amount drifted
        $cases = [
            ['order' => 2235, 'payment' => 2100],
            ['order' => 220, 'payment' => 420],
            ['order' => 530, 'payment' => 405],
            ['order' => 1035, 'payment' => 385],
        ];

        $pairs = [];
        foreach ($cases as $case) {
            $payment = $this->createPayment([
                'amount' => $case['payment'],
                'status' => PaymentStatusEnum::COMPLETED,
                'charge_id' => 'ch_mig_' . uniqid(),
            ]);
            $order = $this->createOrder($case['order'], $payment->id);
            $payment->order_id = $order->id;
            $payment->save();
            $pairs[] = [$order, $payment];
        }

        // Also create one partially-refunded row that must be preserved
        $refundedPayment = $this->createPayment([
            'amount' => 777,
            'refunded_amount' => 100,
            'status' => PaymentStatusEnum::COMPLETED,
            'charge_id' => 'ch_refund_' . uniqid(),
        ]);
        $refundedOrder = $this->createOrder(500, $refundedPayment->id);
        $refundedPayment->order_id = $refundedOrder->id;
        $refundedPayment->save();

        // And one non-completed row that must be preserved
        $pendingPayment = $this->createPayment([
            'amount' => 888,
            'status' => PaymentStatusEnum::PENDING,
            'charge_id' => 'ch_pending_' . uniqid(),
        ]);
        $pendingOrder = $this->createOrder(200, $pendingPayment->id);
        $pendingPayment->order_id = $pendingOrder->id;
        $pendingPayment->save();

        $prefix = DB::getTablePrefix();

        DB::statement("
            UPDATE `{$prefix}payments` p
            INNER JOIN `{$prefix}ec_orders` o ON p.id = o.payment_id
            SET p.amount = o.amount
            WHERE p.amount <> o.amount
              AND p.status = 'completed'
              AND (p.refunded_amount IS NULL OR p.refunded_amount = 0)
        ");

        foreach ($pairs as [$order, $payment]) {
            $payment->refresh();
            $this->assertEquals((float) $order->amount, (float) $payment->amount, "order #{$order->id} payment amount should realign to order total");
        }

        $refundedPayment->refresh();
        $this->assertEquals(777, (float) $refundedPayment->amount, 'partially refunded row must not be touched');

        $pendingPayment->refresh();
        $this->assertEquals(888, (float) $pendingPayment->amount, 'non-completed row must not be touched');
    }
}
