<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    protected int $decimals = 2;

    public function up(): void
    {
        try {
            $currency = DB::table('ec_currencies')->where('is_default', 1)->first();
            $this->decimals = $currency ? (int) $currency->decimals : 2;

            $this->fixOrderTaxCalculations();
            $this->fixInvoiceTaxCalculations();
        } catch (Throwable) {
            // Do nothing
        }
    }

    protected function fixOrderTaxCalculations(): void
    {
        DB::table('ec_orders')
            ->select('id', 'tax_amount', 'amount')
            ->where('tax_amount', '>', 0)
            ->orderBy('id')
            ->chunk(500, function ($orders) {
                $orderIds = $orders->pluck('id');
                $allProducts = DB::table('ec_order_product')
                    ->whereIn('order_id', $orderIds)
                    ->get()
                    ->groupBy('order_id');

                $productUpdates = [];
                $orderUpdates = [];

                foreach ($orders as $order) {
                    $products = $allProducts->get($order->id, collect());
                    $taxGroups = $this->buildTaxGroups($products);

                    $totalCorrectTax = 0;

                    foreach ($taxGroups as $group) {
                        $taxRate = $group['rate'];
                        $groupTax = round($group['subtotal'] * $taxRate / 100, $this->decimals);
                        $totalCorrectTax += $groupTax;

                        $remainingTax = $groupTax;
                        $itemCount = count($group['items']);

                        foreach ($group['items'] as $index => $item) {
                            $itemSubtotal = $item->price * $item->qty;

                            if ($index === $itemCount - 1) {
                                $itemTax = $remainingTax;
                            } else {
                                $itemTax = round($itemSubtotal * $taxRate / 100, $this->decimals);
                                $remainingTax -= $itemTax;
                            }

                            if (abs($item->tax_amount - $itemTax) > 0.001) {
                                $productUpdates[] = [
                                    'id' => $item->id,
                                    'tax_amount' => $itemTax,
                                ];
                            }
                        }
                    }

                    if (abs($order->tax_amount - $totalCorrectTax) > 0.001) {
                        $taxDifference = $totalCorrectTax - $order->tax_amount;
                        $orderUpdates[] = [
                            'id' => $order->id,
                            'tax_amount' => $totalCorrectTax,
                            'amount' => round($order->amount + $taxDifference, $this->decimals),
                        ];
                    }
                }

                $this->bulkUpdate('ec_order_product', $productUpdates, ['tax_amount']);
                $this->bulkUpdate('ec_orders', $orderUpdates, ['tax_amount', 'amount']);
            });
    }

    protected function fixInvoiceTaxCalculations(): void
    {
        if (! Schema::hasTable('ec_invoices') || ! Schema::hasTable('ec_invoice_items')) {
            return;
        }

        DB::table('ec_invoices')
            ->select('id', 'tax_amount', 'amount')
            ->where('tax_amount', '>', 0)
            ->orderBy('id')
            ->chunk(500, function ($invoices) {
                $invoiceIds = $invoices->pluck('id');
                $allItems = DB::table('ec_invoice_items')
                    ->whereIn('invoice_id', $invoiceIds)
                    ->get()
                    ->groupBy('invoice_id');

                $itemUpdates = [];
                $invoiceUpdates = [];

                foreach ($invoices as $invoice) {
                    $items = $allItems->get($invoice->id, collect());
                    $taxGroups = $this->buildTaxGroups($items);

                    $totalCorrectTax = 0;

                    foreach ($taxGroups as $group) {
                        $taxRate = $group['rate'];
                        $groupTax = round($group['subtotal'] * $taxRate / 100, $this->decimals);
                        $totalCorrectTax += $groupTax;

                        $remainingTax = $groupTax;
                        $itemCount = count($group['items']);

                        foreach ($group['items'] as $index => $item) {
                            $itemSubtotal = $item->price * $item->qty;

                            if ($index === $itemCount - 1) {
                                $itemTax = $remainingTax;
                            } else {
                                $itemTax = round($itemSubtotal * $taxRate / 100, $this->decimals);
                                $remainingTax -= $itemTax;
                            }

                            if (abs($item->tax_amount - $itemTax) > 0.001) {
                                $itemUpdates[] = [
                                    'id' => $item->id,
                                    'tax_amount' => $itemTax,
                                    'amount' => round($item->price * $item->qty + $itemTax, $this->decimals),
                                ];
                            }
                        }
                    }

                    if (abs($invoice->tax_amount - $totalCorrectTax) > 0.001) {
                        $taxDifference = $totalCorrectTax - $invoice->tax_amount;
                        $invoiceUpdates[] = [
                            'id' => $invoice->id,
                            'tax_amount' => $totalCorrectTax,
                            'amount' => round($invoice->amount + $taxDifference, $this->decimals),
                        ];
                    }
                }

                $this->bulkUpdate('ec_invoice_items', $itemUpdates, ['tax_amount', 'amount']);
                $this->bulkUpdate('ec_invoices', $invoiceUpdates, ['tax_amount', 'amount']);
            });
    }

    protected function buildTaxGroups($items): array
    {
        $taxGroups = [];

        foreach ($items as $item) {
            $options = is_string($item->options) ? json_decode($item->options, true) : ($item->options ?? []);

            if (! is_array($options)) {
                continue;
            }

            $taxRate = null;

            if (! empty($options['taxRate'])) {
                $taxRate = $options['taxRate'];
            } elseif (! empty($options['taxClasses'])) {
                $taxRate = array_sum(array_values($options['taxClasses']));
            }

            if ($taxRate !== null && $taxRate > 0) {
                // Use string key to avoid PHP float-to-int casting (10.5 -> 10)
                $key = (string) $taxRate;
                if (! isset($taxGroups[$key])) {
                    $taxGroups[$key] = ['subtotal' => 0, 'items' => [], 'rate' => (float) $taxRate];
                }
                $taxGroups[$key]['subtotal'] += ($item->price * $item->qty);
                $taxGroups[$key]['items'][] = $item;
            }
        }

        return $taxGroups;
    }

    protected function bulkUpdate(string $table, array $rows, array $columns): void
    {
        if (empty($rows)) {
            return;
        }

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
