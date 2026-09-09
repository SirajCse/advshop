<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Batch index creation: one ALTER TABLE per table, skip existing indexes
        $indexes = [
            'ec_orders' => [
                'ec_orders_status_created_at_index' => ['status', 'created_at'],
                'ec_orders_user_id_is_finished_index' => ['user_id', 'is_finished'],
            ],
            'ec_order_product' => [
                'ec_order_product_order_id_product_id_index' => ['order_id', 'product_id'],
            ],
            'ec_product_variations' => [
                'ec_product_variations_product_id_index' => ['product_id'],
                'ec_product_variations_configurable_product_id_index' => ['configurable_product_id'],
            ],
            'ec_product_variation_items' => [
                'ec_product_variation_items_variation_id_attribute_id_index' => ['variation_id', 'attribute_id'],
            ],
            'ec_flash_sale_products' => [
                'ec_flash_sale_products_product_id_flash_sale_id_index' => ['product_id', 'flash_sale_id'],
            ],
            'ec_products' => [
                'ec_products_status_is_variation_index' => ['status', 'is_variation'],
                'ec_products_storehouse_quantity_index' => ['with_storehouse_management', 'quantity'],
            ],
            'ec_invoices' => [
                'ec_invoices_reference_id_reference_type_index' => ['reference_id', 'reference_type'],
            ],
            'ec_reviews' => [
                'ec_reviews_product_id_status_index' => ['product_id', 'status'],
                'ec_reviews_customer_id_status_index' => ['customer_id', 'status'],
            ],
        ];

        $prefix = DB::getTablePrefix();

        foreach ($indexes as $table => $tableIndexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            // Get existing indexes in one query per table
            $existing = collect(DB::select("SHOW INDEX FROM `{$prefix}{$table}`"))
                ->pluck('Key_name')
                ->unique()
                ->flip()
                ->toArray();

            $addStatements = [];

            foreach ($tableIndexes as $indexName => $columns) {
                if (isset($existing[$indexName])) {
                    continue;
                }

                $colStr = implode('`, `', $columns);
                $addStatements[] = "ADD INDEX `{$indexName}` (`{$colStr}`)";
            }

            if (! empty($addStatements)) {
                // Single ALTER TABLE with all indexes for this table
                DB::statement("ALTER TABLE `{$prefix}{$table}` " . implode(', ', $addStatements));
            }
        }
    }

    public function down(): void
    {
        $indexes = [
            'ec_reviews' => ['ec_reviews_product_id_status_index', 'ec_reviews_customer_id_status_index'],
            'ec_invoices' => ['ec_invoices_reference_id_reference_type_index'],
            'ec_products' => ['ec_products_status_is_variation_index', 'ec_products_storehouse_quantity_index'],
            'ec_flash_sale_products' => ['ec_flash_sale_products_product_id_flash_sale_id_index'],
            'ec_product_variation_items' => ['ec_product_variation_items_variation_id_attribute_id_index'],
            'ec_product_variations' => ['ec_product_variations_product_id_index', 'ec_product_variations_configurable_product_id_index'],
            'ec_order_product' => ['ec_order_product_order_id_product_id_index'],
            'ec_orders' => ['ec_orders_status_created_at_index', 'ec_orders_user_id_is_finished_index'],
        ];

        $prefix = DB::getTablePrefix();

        foreach ($indexes as $table => $indexNames) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $existing = collect(DB::select("SHOW INDEX FROM `{$prefix}{$table}`"))
                ->pluck('Key_name')
                ->unique()
                ->flip()
                ->toArray();

            $dropStatements = [];

            foreach ($indexNames as $indexName) {
                if (isset($existing[$indexName])) {
                    $dropStatements[] = "DROP INDEX `{$indexName}`";
                }
            }

            if (! empty($dropStatements)) {
                DB::statement("ALTER TABLE `{$prefix}{$table}` " . implode(', ', $dropStatements));
            }
        }
    }
};
