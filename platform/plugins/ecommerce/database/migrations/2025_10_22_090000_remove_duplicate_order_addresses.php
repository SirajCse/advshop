<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_order_addresses')) {
            return;
        }

        $prefix = DB::getTablePrefix();

        // Delete duplicates directly in SQL — no PHP memory overhead
        DB::statement("
            DELETE oa1 FROM `{$prefix}ec_order_addresses` oa1
            INNER JOIN `{$prefix}ec_order_addresses` oa2
                ON oa1.order_id = oa2.order_id
                AND oa1.type = oa2.type
                AND oa1.id > oa2.id
        ");

        try {
            Schema::table('ec_order_addresses', function (Blueprint $table): void {
                $table->unique(['order_id', 'type'], 'ec_order_addresses_order_id_type_unique');
            });
        } catch (Exception) {
        }
    }

    public function down(): void
    {
        try {
            Schema::table('ec_order_addresses', function (Blueprint $table): void {
                $table->dropUnique('ec_order_addresses_order_id_type_unique');
            });
        } catch (Exception) {
        }
    }
};
