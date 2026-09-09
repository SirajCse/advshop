<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('ec_cart', 'customer_id')) {
            return;
        }

        Schema::table('ec_cart', function (Blueprint $table): void {
            $table->foreignId('customer_id')->nullable()->after('instance');
            $table->index(['customer_id', 'instance'], 'ec_cart_customer_instance_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('ec_cart', 'customer_id')) {
            return;
        }

        Schema::table('ec_cart', function (Blueprint $table): void {
            $table->dropIndex('ec_cart_customer_instance_index');
            $table->dropColumn('customer_id');
        });
    }
};
