<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $tables = [
            'ec_brands_translations' => 'ec_brands_id',
            'ec_flash_sales_translations' => 'ec_flash_sales_id',
            'ec_global_option_value_translations' => 'ec_global_option_value_id',
            'ec_global_options_translations' => 'ec_global_options_id',
            'ec_option_value_translations' => 'ec_option_value_id',
            'ec_options_translations' => 'ec_options_id',
            'ec_product_attribute_sets_translations' => 'ec_product_attribute_sets_id',
            'ec_product_attributes_translations' => 'ec_product_attributes_id',
            'ec_product_categories_translations' => 'ec_product_categories_id',
            'ec_product_collections_translations' => 'ec_product_collections_id',
            'ec_product_labels_translations' => 'ec_product_labels_id',
            'ec_product_tags_translations' => 'ec_product_tags_id',
            'ec_products_translations' => 'ec_products_id',
            'ec_specification_attributes_translations' => 'ec_specification_attributes_id',
            'ec_specification_groups_translations' => 'ec_specification_groups_id',
            'ec_specification_tables_translations' => 'ec_specification_tables_id',
            'ec_taxes_translations' => 'ec_taxes_id',
        ];

        foreach ($tables as $table => $foreignKey) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $shortName = str_replace(['ec_', '_translations', '_id'], '', $foreignKey);
            $indexPrefix = 'idx_' . str_replace('ec_', '', str_replace('_translations', '', $table));

            Schema::table($table, function (Blueprint $blueprint) use ($foreignKey, $indexPrefix, $shortName): void {
                $blueprint->index($foreignKey, $indexPrefix . '_fk');
                $blueprint->index([$foreignKey, 'lang_code'], $indexPrefix . '_' . $shortName . '_lang');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'ec_brands_translations' => 'ec_brands_id',
            'ec_flash_sales_translations' => 'ec_flash_sales_id',
            'ec_global_option_value_translations' => 'ec_global_option_value_id',
            'ec_global_options_translations' => 'ec_global_options_id',
            'ec_option_value_translations' => 'ec_option_value_id',
            'ec_options_translations' => 'ec_options_id',
            'ec_product_attribute_sets_translations' => 'ec_product_attribute_sets_id',
            'ec_product_attributes_translations' => 'ec_product_attributes_id',
            'ec_product_categories_translations' => 'ec_product_categories_id',
            'ec_product_collections_translations' => 'ec_product_collections_id',
            'ec_product_labels_translations' => 'ec_product_labels_id',
            'ec_product_tags_translations' => 'ec_product_tags_id',
            'ec_products_translations' => 'ec_products_id',
            'ec_specification_attributes_translations' => 'ec_specification_attributes_id',
            'ec_specification_groups_translations' => 'ec_specification_groups_id',
            'ec_specification_tables_translations' => 'ec_specification_tables_id',
            'ec_taxes_translations' => 'ec_taxes_id',
        ];

        foreach ($tables as $table => $foreignKey) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $shortName = str_replace(['ec_', '_translations', '_id'], '', $foreignKey);
            $indexPrefix = 'idx_' . str_replace('ec_', '', str_replace('_translations', '', $table));

            Schema::table($table, function (Blueprint $blueprint) use ($indexPrefix, $shortName): void {
                $blueprint->dropIndex($indexPrefix . '_fk');
                $blueprint->dropIndex($indexPrefix . '_' . $shortName . '_lang');
            });
        }
    }
};
