<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('ec_products', 'currency_code')) {
            return;
        }

        Schema::table('ec_products', function (Blueprint $table): void {
            $table->string('currency_code', 10)
                ->nullable()
                ->after('cost_per_item')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('ec_products', function (Blueprint $table): void {
            $table->dropColumn('currency_code');
        });
    }
};
