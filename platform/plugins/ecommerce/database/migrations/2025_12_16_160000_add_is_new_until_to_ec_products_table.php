<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ec_products', 'is_new_until')) {
            Schema::table('ec_products', function (Blueprint $table): void {
                $table->date('is_new_until')->nullable()->after('is_featured');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ec_products', 'is_new_until')) {
            Schema::table('ec_products', function (Blueprint $table): void {
                $table->dropColumn('is_new_until');
            });
        }
    }
};
