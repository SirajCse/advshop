<?php

namespace Botble\Ecommerce\Commands;

use Botble\Ecommerce\Enums\OrderCancellationReasonEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class CancelPendingOrdersCommand extends Command
{
    protected $signature = 'cms:ecommerce:cancel-pending-orders
                            {--threshold= : Override the configured threshold in minutes}';

    protected $description = 'Cancel pending unfinished orders paid by online gateways that exceed the configured age threshold';

    public function handle(): int
    {
        if (! get_ecommerce_setting('auto_cancel_pending_orders_enabled', false)) {
            $this->components->warn('Auto-cancel for pending orders is disabled. Enable it in Ecommerce Settings.');

            return self::SUCCESS;
        }

        $threshold = (int) ($this->option('threshold') ?: get_ecommerce_setting('auto_cancel_pending_orders_threshold_minutes', 30));
        $threshold = max(1, $threshold);
        $cutoff = Carbon::now()->subMinutes($threshold);

        $cancelled = 0;
        $failed = 0;

        // Orders with payment_id IS NULL are abandoned online-payment attempts.
        // COD and Bank Transfer create a payment row immediately and set is_finished=true,
        // so they are never matched here.
        Order::query()
            ->where('status', OrderStatusEnum::PENDING)
            ->where('is_finished', false)
            ->whereNull('payment_id')
            ->where('created_at', '<=', $cutoff)
            ->chunkById(100, function ($orders) use (&$cancelled, &$failed): void {
                foreach ($orders as $order) {
                    // Re-check inside the loop to avoid racing a payment webhook
                    // that completes the order between query and cancel.
                    $order->refresh();
                    if (
                        $order->status->getValue() !== OrderStatusEnum::PENDING
                        || $order->is_finished
                        || $order->payment_id
                    ) {
                        continue;
                    }

                    // Never cancel an order that already has a captured gateway payment.
                    // Async methods (UPI, wallets, bank redirects) can capture the money
                    // and record a completed Payment via webhook BEFORE the order is
                    // finalized (so order.payment_id is still null). Cancelling here would
                    // void a genuinely paid order and restock it - skip and let the
                    // payment flow complete it instead.
                    if ($this->hasCapturedPayment($order)) {
                        $this->components->warn(
                            "Skipped order #{$order->id}: a captured payment exists but the order is not finalized yet."
                        );

                        continue;
                    }

                    try {
                        OrderHelper::cancelOrder(
                            $order,
                            OrderCancellationReasonEnum::PAYMENT_ISSUES_OR_DECLINED_TRANSACTION
                        );
                        $cancelled++;
                    } catch (Throwable $exception) {
                        $failed++;
                        $this->components->error("Failed to cancel order #{$order->id}: {$exception->getMessage()}");
                    }
                }
            });

        $this->components->info("Cancelled {$cancelled} pending order(s), {$failed} failed.");

        return self::SUCCESS;
    }

    /**
     * Whether a captured gateway payment is already recorded for this order.
     *
     * Guards against auto-cancelling an order whose money was captured by an async
     * method (UPI, wallet, bank redirect) but whose finalization hasn't run yet, so
     * order.payment_id is still null. The webhook links the Payment by order_id even
     * when the order isn't finished, so a COMPLETED payment row is the reliable local
     * signal that the order is actually paid.
     */
    protected function hasCapturedPayment(Order $order): bool
    {
        if (! is_plugin_active('payment')) {
            return false;
        }

        return Payment::query()
            ->where('order_id', $order->getKey())
            ->where('status', PaymentStatusEnum::COMPLETED)
            ->exists();
    }
}
