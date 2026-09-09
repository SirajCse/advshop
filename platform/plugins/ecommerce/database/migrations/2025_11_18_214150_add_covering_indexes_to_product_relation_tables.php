<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_product_category_product', function (): void {
            if (! $this->hasIndex('ec_product_category_product', 'idx_product_id_category_id')) {
                DB::statement('CREATE INDEX idx_product_id_category_id ON ec_product_category_product (product_id, category_id)');
            }
        });

        Schema::table('ec_product_collection_products', function (): void {
            if (! $this->hasIndex('ec_product_collection_products', 'idx_product_id_collection_id')) {
                DB::statement('CREATE INDEX idx_product_id_collection_id ON ec_product_collection_products (product_id, product_collection_id)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ec_product_category_product', function (): void {
            if ($this->hasIndex('ec_product_category_product', 'idx_product_id_category_id')) {
                DB::statement('DROP INDEX idx_product_id_category_id ON ec_product_category_product');
            }
        });

        Schema::table('ec_product_collection_products', function (): void {
            if ($this->hasIndex('ec_product_collection_products', 'idx_product_id_collection_id')) {
                DB::statement('DROP INDEX idx_product_id_collection_id ON ec_product_collection_products');
            }
        });
    }

    protected function hasIndex(string $table, string $index): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);

        return ! empty($indexes);
    }
};
