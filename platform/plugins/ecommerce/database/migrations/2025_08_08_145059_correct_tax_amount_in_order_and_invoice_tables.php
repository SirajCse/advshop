<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ec_order_product') && Schema::hasColumn('ec_order_product', 'options')) {
            $this->fixTable('ec_order_product');
        }

        if (Schema::hasTable('ec_invoice_items') && Schema::hasColumn('ec_invoice_items', 'options')) {
            $this->fixTable('ec_invoice_items');
        }
    }

    public function down(): void
    {
        // This migration corrects data, so we cannot reverse the correction
    }

    protected function fixTable(string $table): void
    {
        DB::table($table)
            ->whereNotNull('options')
            ->where('options', '!=', '')
            ->oldest('id')
            ->chunk(500, function ($rows) use ($table) {
                $updates = [];

                foreach ($rows as $row) {
                    $options = json_decode($row->options, true);

                    if (json_last_error() === JSON_ERROR_NONE && isset($options['taxRate']) && $options['taxRate'] > 0) {
                        $taxAmount = $row->price * $row->qty * $options['taxRate'] / 100;

                        $updates[] = [
                            'id' => $row->id,
                            'tax_amount' => $taxAmount,
                        ];
                    }
                }

                $this->bulkUpdate($table, $updates);
            });
    }

    protected function bulkUpdate(string $table, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            $ids = array_column($chunk, 'id');
            $cases = [];

            foreach ($chunk as $row) {
                $cases[] = 'WHEN ' . (int) $row['id'] . ' THEN ' . (float) $row['tax_amount'];
            }

            $idsStr = implode(',', $ids);

            DB::statement(
                "UPDATE `{$table}` SET `tax_amount` = CASE `id` " . implode(' ', $cases) . " END WHERE `id` IN ({$idsStr})"
            );
        }
    }
};
