<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        DB::transaction(function (): void {
            if (Schema::hasTable('payments') && is_plugin_active('payment')) {
                $this->fixDuplicatePayments();
                $this->fixOrphanedPayments();
            }

            $this->syncShipmentPrices();
            $this->cleanupIncompleteOrderDiscounts();
        });
    }

    protected function fixDuplicatePayments(): void
    {
        $prefix = DB::getTablePrefix();

        DB::statement("
            DELETE p1 FROM `{$prefix}payments` p1
            INNER JOIN `{$prefix}payments` p2
                ON p1.order_id = p2.order_id
                AND p1.payment_channel = p2.payment_channel
                AND p1.id > p2.id
            WHERE p1.order_id IS NOT NULL
        ");
    }

    protected function fixOrphanedPayments(): void
    {
        DB::table('payments')
            ->whereNull('order_id')
            ->whereNotNull('metadata')
            ->oldest('id')
            ->chunk(500, function ($payments) {
                $updates = [];

                foreach ($payments as $payment) {
                    try {
                        $metadata = is_string($payment->metadata)
                            ? json_decode($payment->metadata, true)
                            : $payment->metadata;

                        if (! is_array($metadata)) {
                            continue;
                        }

                        $orderId = $metadata['order_id']
                            ?? $metadata['notes']['order_id']
                            ?? null;

                        if ($orderId) {
                            $updates[$payment->id] = $orderId;
                        }
                    } catch (Exception $e) {
                        Log::error('Failed to parse orphaned payment metadata: ' . $e->getMessage(), [
                            'payment_id' => $payment->id,
                        ]);
                    }
                }

                if (empty($updates)) {
                    return;
                }

                // Verify order IDs exist in bulk
                $validOrderIds = DB::table('ec_orders')
                    ->whereIn('id', array_values($updates))
                    ->pluck('id')
                    ->flip()
                    ->toArray();

                $cases = [];
                $ids = [];

                foreach ($updates as $paymentId => $orderId) {
                    if (isset($validOrderIds[$orderId])) {
                        $cases[] = 'WHEN ' . (int) $paymentId . ' THEN ' . (int) $orderId;
                        $ids[] = (int) $paymentId;
                    }
                }

                if (! empty($ids)) {
                    $idsStr = implode(',', $ids);
                    DB::statement(
                        "UPDATE `" . DB::getTablePrefix() . "payments` SET `order_id` = CASE `id` "
                        . implode(' ', $cases) . " END WHERE `id` IN ({$idsStr})"
                    );
                }
            });
    }

    protected function syncShipmentPrices(): void
    {
        if (! Schema::hasTable('ec_shipments')) {
            return;
        }

        $prefix = DB::getTablePrefix();

        DB::statement("
            UPDATE `{$prefix}ec_shipments` s
            INNER JOIN `{$prefix}ec_orders` o ON s.order_id = o.id
            SET s.price = o.shipping_amount
            WHERE s.price != o.shipping_amount
        ");
    }

    protected function cleanupIncompleteOrderDiscounts(): void
    {
        if (! Schema::hasTable('ec_discount_customers') || ! Schema::hasTable('ec_discounts')) {
            return;
        }

        DB::table('ec_orders')
            ->where('is_finished', false)
            ->whereNotNull('coupon_code')
            ->whereNotNull('user_id')
            ->select('id', 'user_id', 'coupon_code')
            ->oldest('id')
            ->chunk(500, function ($orders) {
                foreach ($orders as $order) {
                    try {
                        $deletedCount = DB::table('ec_discount_customers')
                            ->where('customer_id', $order->user_id)
                            ->whereIn('discount_id', function ($query) use ($order): void {
                                $query->select('id')
                                    ->from('ec_discounts')
                                    ->where('code', $order->coupon_code);
                            })
                            ->delete();

                        if ($deletedCount > 0) {
                            DB::table('ec_discounts')
                                ->where('code', $order->coupon_code)
                                ->where('total_used', '>', 0)
                                ->decrement('total_used', $deletedCount);
                        }
                    } catch (Exception $e) {
                        Log::error('Failed to cleanup discount for incomplete order: ' . $e->getMessage(), [
                            'order_id' => $order->id,
                            'coupon_code' => $order->coupon_code,
                        ]);
                    }
                }
            });
    }
};
