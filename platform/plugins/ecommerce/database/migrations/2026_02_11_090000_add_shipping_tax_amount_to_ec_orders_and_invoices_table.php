<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ec_orders', 'shipping_tax_amount')) {
            Schema::table('ec_orders', function (Blueprint $table): void {
                $table->decimal('shipping_tax_amount', 15)->default(0)->nullable()->after('shipping_amount');
            });
        }

        if (! Schema::hasColumn('ec_invoices', 'shipping_tax_amount')) {
            Schema::table('ec_invoices', function (Blueprint $table): void {
                $table->decimal('shipping_tax_amount', 15)->default(0)->nullable()->after('shipping_amount');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ec_orders', function (Blueprint $table): void {
            $table->dropColumn('shipping_tax_amount');
        });

        Schema::table('ec_invoices', function (Blueprint $table): void {
            $table->dropColumn('shipping_tax_amount');
        });
    }
};
