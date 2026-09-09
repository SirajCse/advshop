<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_orders') || ! Schema::hasTable('ec_order_addresses') || ! Schema::hasTable('ec_customer_addresses')) {
            return;
        }

        $prefix = DB::getTablePrefix();

        // Pass 1: backfill from the customer's default address. Covers logged-in
        // customers who placed orders during a window where checkAndCreateOrderAddress
        // silently skipped row creation (empty addressData['name'] in session).
        //
        // The MIN(id) derived table guards against customers with multiple is_default=1
        // rows (data corruption from older versions): without it, the JOIN would emit
        // multiple SELECT rows per order_id and the unique key (order_id, type) added
        // in 2025_10_22_090000 would abort the INSERT with a duplicate-key error.
        DB::statement("
            INSERT INTO `{$prefix}ec_order_addresses`
                (order_id, type, name, phone, email, country, state, city, address, zip_code)
            SELECT
                o.id, 'shipping_address',
                a.name, a.phone, a.email, a.country, a.state, a.city, a.address, a.zip_code
            FROM `{$prefix}ec_orders` o
            INNER JOIN (
                SELECT customer_id, MIN(id) AS first_default_id
                FROM `{$prefix}ec_customer_addresses`
                WHERE is_default = 1
                GROUP BY customer_id
            ) default_a ON default_a.customer_id = o.user_id
            INNER JOIN `{$prefix}ec_customer_addresses` a ON a.id = default_a.first_default_id
            WHERE o.user_id > 0
              AND NOT EXISTS (
                SELECT 1 FROM `{$prefix}ec_order_addresses` oa
                WHERE oa.order_id = o.id AND oa.type = 'shipping_address'
              )
        ");

        // Pass 2: customers without a flagged default address — fall back to their
        // earliest saved address so the admin order view is no longer blank.
        DB::statement("
            INSERT INTO `{$prefix}ec_order_addresses`
                (order_id, type, name, phone, email, country, state, city, address, zip_code)
            SELECT
                o.id, 'shipping_address',
                a.name, a.phone, a.email, a.country, a.state, a.city, a.address, a.zip_code
            FROM `{$prefix}ec_orders` o
            INNER JOIN (
                SELECT customer_id, MIN(id) AS first_id
                FROM `{$prefix}ec_customer_addresses`
                GROUP BY customer_id
            ) first_a ON first_a.customer_id = o.user_id
            INNER JOIN `{$prefix}ec_customer_addresses` a ON a.id = first_a.first_id
            WHERE o.user_id > 0
              AND NOT EXISTS (
                SELECT 1 FROM `{$prefix}ec_order_addresses` oa
                WHERE oa.order_id = o.id AND oa.type = 'shipping_address'
              )
        ");

        // Guest orders (user_id = 0) cannot be backfilled here — their identifying
        // info lives in the payment gateway response (payments.metadata), and the
        // shape varies per gateway. Affected admins should review those manually
        // from the linked payment record's "View response source" panel.
    }
};
