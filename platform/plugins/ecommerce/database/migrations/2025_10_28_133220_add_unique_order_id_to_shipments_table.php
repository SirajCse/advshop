<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_shipments') || ! Schema::hasColumn('ec_shipments', 'order_id')) {
            return;
        }

        $prefix = DB::getTablePrefix();

        DB::statement("
            DELETE s1 FROM `{$prefix}ec_shipments` s1
            INNER JOIN `{$prefix}ec_shipments` s2
                ON s1.order_id = s2.order_id
                AND s1.id > s2.id
        ");

        try {
            $indexes = DB::select("SHOW INDEX FROM `{$prefix}ec_shipments` WHERE Key_name = 'ec_shipments_order_id_unique'");

            if (empty($indexes)) {
                Schema::table('ec_shipments', function (Blueprint $table): void {
                    $table->unique('order_id', 'ec_shipments_order_id_unique');
                });
            }
        } catch (Exception) {
        }
    }

    public function down(): void
    {
        try {
            Schema::table('ec_shipments', function (Blueprint $table): void {
                $table->dropUnique('ec_shipments_order_id_unique');
            });
        } catch (Exception) {
        }
    }
};
