<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ec_shipping_rule_items', 'zip_code_from')) {
            Schema::table('ec_shipping_rule_items', function (Blueprint $table): void {
                $table->string('zip_code_from', 20)->nullable()->after('zip_code');
                $table->string('zip_code_to', 20)->nullable()->after('zip_code_from');
                $table->index(['zip_code_from', 'zip_code_to'], 'idx_zip_range');
            });

            DB::table('ec_shipping_rule_items')
                ->whereNotNull('zip_code')
                ->where('zip_code', '!=', '')
                ->update([
                    'zip_code_from' => DB::raw('zip_code'),
                    'zip_code_to' => DB::raw('zip_code'),
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ec_shipping_rule_items', 'zip_code_from')) {
            Schema::table('ec_shipping_rule_items', function (Blueprint $table): void {
                $table->dropIndex('idx_zip_range');
                $table->dropColumn(['zip_code_from', 'zip_code_to']);
            });
        }
    }
};
