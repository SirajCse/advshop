<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ec_options', 'price_per_product')) {
            Schema::table('ec_options', function (Blueprint $table) {
                $table->boolean('price_per_product')->default(false)->after('required');
            });
        }

        if (! Schema::hasColumn('ec_global_options', 'price_per_product')) {
            Schema::table('ec_global_options', function (Blueprint $table) {
                $table->boolean('price_per_product')->default(false)->after('required');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ec_options', function (Blueprint $table) {
            $table->dropColumn('price_per_product');
        });

        Schema::table('ec_global_options', function (Blueprint $table) {
            $table->dropColumn('price_per_product');
        });
    }
};
