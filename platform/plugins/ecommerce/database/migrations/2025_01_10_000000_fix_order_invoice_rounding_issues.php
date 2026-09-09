<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        try {
            $defaultCurrency = DB::table('ec_currencies')->where('is_default', 1)->first();

            $this->fixOrderData($defaultCurrency);
            $this->fixInvoiceData();
        } catch (Throwable) {
            // Do nothing
        }
    }

    public function down(): void
    {
    }

    protected function fixOrderData($defaultCurrency): void
    {
        // Pre-load all product tax percentages in one query
        $taxPercentages = $this->loadProductTaxPercentages();
        $defaultTaxRate = DB::table('settings')
            ->where('key', 'ecommerce_default_tax_rate')
            ->value('value');
        $defaultTaxPercentage = 0;

        if ($defaultTaxRate) {
            $defaultTaxPercentage = (float) DB::table('ec_taxes')
                ->where('id', $defaultTaxRate)
                ->value('percentage') ?: 0;
        }

        $decimals = $defaultCurrency ? (int) $defaultCurrency->decimals : 2;

        DB::table('ec_orders')
            ->select('id', 'shipping_amount', 'discount_amount')
            ->orderBy('id')
            ->chunk(500, function ($orders) use ($taxPercentages, $defaultTaxPercentage, $decimals) {
                $orderIds = $orders->pluck('id');
                $orderProducts = DB::table('ec_order_product')
                    ->whereIn('order_id', $orderIds)
                    ->get()
                    ->groupBy('order_id');

                $productUpdates = [];
                $orderUpdates = [];

                foreach ($orders as $order) {

                    $subtotal = 0;
                    $taxTotal = 0;
                    $items = $orderProducts->get($order->id, collect());

                    foreach ($items as $item) {
                        $price = round($item->price, $decimals);
                        $lineTotal = round($price * $item->qty, $decimals);
                        $subtotal += $lineTotal;

                        $taxAmount = $item->tax_amount;

                        if ($item->tax_amount > 0 && $lineTotal > 0) {
                            $taxPercentage = $taxPercentages[$item->product_id] ?? $defaultTaxPercentage;
                            $taxRate = $taxPercentage > 0 ? $taxPercentage / 100 : 0;

                            if ($taxRate == 0) {
                                $originalLineTotal = $item->price * $item->qty;
                                if ($originalLineTotal > 0) {
                                    $taxRate = $item->tax_amount / $originalLineTotal;
                                }
                            }

                            $taxAmount = round($lineTotal * $taxRate, $decimals);
                        }

                        $productUpdates[] = [
                            'id' => $item->id,
                            'price' => $price,
                            'tax_amount' => $taxAmount,
                        ];

                        $taxTotal += $taxAmount;
                    }

                    $shippingAmount = round($order->shipping_amount, $decimals);
                    $discountAmount = round($order->discount_amount, $decimals);

                    $orderUpdates[] = [
                        'id' => $order->id,
                        'sub_total' => $subtotal,
                        'tax_amount' => $taxTotal,
                        'shipping_amount' => $shippingAmount,
                        'discount_amount' => $discountAmount,
                        'amount' => round($subtotal + $taxTotal + $shippingAmount - $discountAmount, $decimals),
                    ];
                }

                // Bulk update order products
                $this->bulkUpdate('ec_order_product', $productUpdates, ['price', 'tax_amount']);

                // Bulk update orders
                $this->bulkUpdate('ec_orders', $orderUpdates, ['sub_total', 'tax_amount', 'shipping_amount', 'discount_amount', 'amount']);
            });
    }

    protected function fixInvoiceData(): void
    {
        if (! Schema::hasTable('ec_invoices')) {
            return;
        }

        $prefix = DB::getTablePrefix();

        // Sync invoices from orders in a single UPDATE JOIN query
        DB::statement("
            UPDATE `{$prefix}ec_invoices`
            INNER JOIN `{$prefix}ec_orders` ON `{$prefix}ec_invoices`.`reference_id` = `{$prefix}ec_orders`.`id`
            SET
                `{$prefix}ec_invoices`.`sub_total` = `{$prefix}ec_orders`.`sub_total`,
                `{$prefix}ec_invoices`.`tax_amount` = `{$prefix}ec_orders`.`tax_amount`,
                `{$prefix}ec_invoices`.`shipping_amount` = `{$prefix}ec_orders`.`shipping_amount`,
                `{$prefix}ec_invoices`.`discount_amount` = `{$prefix}ec_orders`.`discount_amount`,
                `{$prefix}ec_invoices`.`amount` = `{$prefix}ec_orders`.`amount`
        ");
    }

    protected function loadProductTaxPercentages(): array
    {
        if (! Schema::hasTable('ec_taxes') || ! Schema::hasTable('ec_taxables')) {
            return [];
        }

        return DB::table('ec_taxables')
            ->join('ec_taxes', 'ec_taxes.id', '=', 'ec_taxables.tax_id')
            ->where('ec_taxes.status', 'published')
            ->whereNotExists(function ($query) {
                if (Schema::hasTable('ec_tax_rules')) {
                    $query->select(DB::raw(1))
                        ->from('ec_tax_rules')
                        ->whereColumn('ec_tax_rules.tax_id', 'ec_taxes.id');
                }
            })
            ->groupBy('ec_taxables.taxable_id')
            ->pluck(DB::raw('SUM(ec_taxes.percentage)'), 'ec_taxables.taxable_id')
            ->map(fn ($val) => (float) $val)
            ->toArray();
    }

    /**
     * Bulk update rows using CASE WHEN for each column.
     */
    protected function bulkUpdate(string $table, array $rows, array $columns): void
    {
        if (empty($rows)) {
            return;
        }

        // Process in batches of 500 to avoid overly large queries
        foreach (array_chunk($rows, 500) as $chunk) {
            $ids = array_column($chunk, 'id');
            $sets = [];

            foreach ($columns as $column) {
                $cases = [];
                foreach ($chunk as $row) {
                    $cases[] = 'WHEN ' . (int) $row['id'] . ' THEN ' . (float) $row[$column];
                }
                $sets[] = "`{$column}` = CASE `id` " . implode(' ', $cases) . ' END';
            }

            $idsStr = implode(',', $ids);

            DB::statement("UPDATE `{$table}` SET " . implode(', ', $sets) . " WHERE `id` IN ({$idsStr})");
        }
    }
};
