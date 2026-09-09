<?php

namespace Botble\Ecommerce\Services;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;

class CartCleanupService
{
    public function __construct(protected DatabaseManager $db)
    {
    }

    public function cleanupExpiredGuestCarts(int $daysToKeep = 30): int
    {
        $deleted = $this->db
            ->table($this->getTableName())
            ->where('updated_at', '<=', now()->subDays($daysToKeep))
            ->whereNull('customer_id')
            ->delete();

        if ($deleted > 0) {
            Log::info("Cart cleanup: Deleted {$deleted} expired guest carts older than {$daysToKeep} days.");
        }

        return $deleted;
    }

    public function cleanupExpiredCustomerCarts(int $daysToKeep = 90): int
    {
        $deleted = $this->db
            ->table($this->getTableName())
            ->where('updated_at', '<=', now()->subDays($daysToKeep))
            ->whereNotNull('customer_id')
            ->delete();

        if ($deleted > 0) {
            Log::info("Cart cleanup: Deleted {$deleted} expired customer carts older than {$daysToKeep} days.");
        }

        return $deleted;
    }

    public function cleanupAllExpiredCarts(int $guestDays = 30, int $customerDays = 90): array
    {
        return [
            'guest_carts' => $this->cleanupExpiredGuestCarts($guestDays),
            'customer_carts' => $this->cleanupExpiredCustomerCarts($customerDays),
        ];
    }

    public function getCartStatistics(): array
    {
        $table = $this->getTableName();

        return [
            'total_carts' => $this->db->table($table)->count(),
            'guest_carts' => $this->db->table($table)->whereNull('customer_id')->count(),
            'customer_carts' => $this->db->table($table)->whereNotNull('customer_id')->count(),
            'carts_last_24h' => $this->db->table($table)->where('updated_at', '>=', now()->subDay())->count(),
            'carts_last_7d' => $this->db->table($table)->where('updated_at', '>=', now()->subWeek())->count(),
            'oldest_cart' => $this->db->table($table)->min('updated_at'),
        ];
    }

    protected function getTableName(): string
    {
        return config('plugins.ecommerce.cart.database.table', 'ec_cart');
    }
}
