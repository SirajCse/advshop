<?php

namespace Botble\Ecommerce\Commands;

use Botble\Ecommerce\Events\AbandonedCartReminderEvent;
use Botble\Ecommerce\Services\AbandonedCartService;
use Exception;
use Illuminate\Console\Command;

class CheckAbandonedCartsCommand extends Command
{
    protected $signature = 'cms:check-abandoned-carts
                            {--cleanup : Clean up old abandoned carts}
                            {--cleanup-days= : Days to keep abandoned carts}';

    protected $description = 'Check for abandoned carts and send reminder emails in sequence';

    public function handle(AbandonedCartService $service): int
    {
        if (! get_ecommerce_setting('abandoned_cart_enabled', false)) {
            $this->components->warn('Abandoned cart tracking is disabled. Enable it in Ecommerce Settings.');

            return self::SUCCESS;
        }

        if ($this->option('cleanup')) {
            return $this->handleCleanup($service);
        }

        return $this->handleEmailSequence($service);
    }

    protected function handleCleanup(AbandonedCartService $service): int
    {
        $cleanupDays = $this->option('cleanup-days') ?: get_ecommerce_setting('abandoned_cart_cleanup_days', 30);
        $deleted = $service->cleanupOldAbandonedCarts((int) $cleanupDays);
        $this->components->info("Cleaned up {$deleted} old abandoned carts.");

        return self::SUCCESS;
    }

    protected function handleEmailSequence(AbandonedCartService $service): int
    {
        $this->components->info('Checking for abandoned carts...');

        $maxReminders = (int) get_ecommerce_setting('abandoned_cart_max_reminders', 3);
        $totalSent = 0;
        $totalSkipped = 0;
        $totalFailed = 0;

        foreach (range(1, min($maxReminders, 3)) as $sequence) {
            $carts = $service->getCartsForSequence($sequence);

            if ($carts->isEmpty()) {
                continue;
            }

            $delay = $service->getSequenceDelay($sequence);
            $this->components->info("Sequence {$sequence} ({$delay}h delay): Found {$carts->count()} carts.");

            foreach ($carts as $cart) {
                if (! $cart->email) {
                    $this->components->warn("  Skipping cart #{$cart->id} - no email address.");
                    $totalSkipped++;

                    continue;
                }

                if ($service->isOptedOut($cart)) {
                    $this->components->warn("  Skipping cart #{$cart->id} - customer opted out.");
                    $totalSkipped++;

                    continue;
                }

                try {
                    event(new AbandonedCartReminderEvent($cart, $sequence));
                    $cart->updateEmailSequence($sequence);
                    $this->components->info("  Sent email #{$sequence} for cart #{$cart->id} to {$cart->email}");
                    $totalSent++;
                } catch (Exception $e) {
                    $this->components->error("  Failed cart #{$cart->id}: {$e->getMessage()}");
                    $totalFailed++;
                }
            }
        }

        $this->newLine();
        $this->components->info("Summary: {$totalSent} sent, {$totalSkipped} skipped, {$totalFailed} failed.");

        return self::SUCCESS;
    }
}
