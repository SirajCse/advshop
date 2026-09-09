<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('ec_order_product', 'tax_breakdown')) {
            return;
        }

        Schema::table('ec_order_product', function (Blueprint $table): void {
            $table->json('tax_breakdown')->nullable()->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('ec_order_product', function (Blueprint $table): void {
            $table->dropColumn('tax_breakdown');
        });
    }
};
