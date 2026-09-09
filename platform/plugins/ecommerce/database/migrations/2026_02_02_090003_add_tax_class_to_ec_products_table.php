<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('ec_products', 'tax_class')) {
            return;
        }

        Schema::table('ec_products', function (Blueprint $table): void {
            $table->string('tax_class', 50)->default('standard')->after('tax_id');
        });
    }

    public function down(): void
    {
        Schema::table('ec_products', function (Blueprint $table): void {
            $table->dropColumn('tax_class');
        });
    }
};
