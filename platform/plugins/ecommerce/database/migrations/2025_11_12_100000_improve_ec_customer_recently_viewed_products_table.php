<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        try {
            if (Schema::hasColumn('ec_customer_recently_viewed_products', 'id')) {
                Schema::table('ec_customer_recently_viewed_products', function (Blueprint $table): void {
                    $table->dropColumn('id');
                });
            }

            if (! Schema::hasColumn('ec_customer_recently_viewed_products', 'created_at')) {
                Schema::table('ec_customer_recently_viewed_products', function (Blueprint $table): void {
                    $table->primary(['customer_id', 'product_id']);
                });
            }
        } catch (Throwable) {
            // Ignore if already modified
        }
    }

    public function down(): void
    {
        // This migration is not reversible as we cannot restore auto-increment IDs
    }
};
