<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_order_product_tax_components')) {
            return;
        }

        try {
            Schema::table('ec_order_product_tax_components', function (Blueprint $table): void {
                $table->dropForeign(['order_product_id']);
            });
        } catch (Throwable) {
        }
    }
};
