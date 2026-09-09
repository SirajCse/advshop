<?php

namespace Botble\Ecommerce\Commands;

use Botble\Ecommerce\Services\CartCleanupService;
use Illuminate\Console\Command;

class CleanupExpiredCartsCommand extends Command
{
    protected $signature = 'cms:cleanup-expired-carts
                            {--guest-days=30 : Days to keep guest carts}
                            {--customer-days=90 : Days to keep customer carts}
                            {--stats : Show cart statistics only}';

    protected $description = 'Clean up expired carts from the database';

    public function handle(CartCleanupService $service): int
    {
        if ($this->option('stats')) {
            return $this->showStatistics($service);
        }

        return $this->cleanupCarts($service);
    }

    protected function cleanupCarts(CartCleanupService $service): int
    {
        $guestDays = (int) $this->option('guest-days');
        $customerDays = (int) $this->option('customer-days');

        $this->components->info('Starting cart cleanup...');
        $this->newLine();

        $result = $service->cleanupAllExpiredCarts($guestDays, $customerDays);

        $this->components->twoColumnDetail(
            'Guest carts deleted (older than ' . $guestDays . ' days)',
            '<fg=yellow>' . $result['guest_carts'] . '</>'
        );

        $this->components->twoColumnDetail(
            'Customer carts deleted (older than ' . $customerDays . ' days)',
            '<fg=yellow>' . $result['customer_carts'] . '</>'
        );

        $this->newLine();

        $total = $result['guest_carts'] + $result['customer_carts'];

        if ($total > 0) {
            $this->components->info("Total: {$total} expired carts cleaned up.");
        } else {
            $this->components->info('No expired carts found.');
        }

        return self::SUCCESS;
    }

    protected function showStatistics(CartCleanupService $service): int
    {
        $stats = $service->getCartStatistics();

        $this->components->info('Cart Statistics');
        $this->newLine();

        $this->components->twoColumnDetail('Total carts', $stats['total_carts']);
        $this->components->twoColumnDetail('Guest carts', $stats['guest_carts']);
        $this->components->twoColumnDetail('Customer carts', $stats['customer_carts']);
        $this->components->twoColumnDetail('Updated in last 24h', $stats['carts_last_24h']);
        $this->components->twoColumnDetail('Updated in last 7 days', $stats['carts_last_7d']);
        $this->components->twoColumnDetail('Oldest cart', $stats['oldest_cart'] ?? 'N/A');

        return self::SUCCESS;
    }
}
