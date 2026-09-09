<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('ec_shipping_rule_items')
            ->whereNotNull('zip_code_from')
            ->where('zip_code_from', '!=', '')
            ->orderBy('id')
            ->chunk(500, function ($items): void {
                foreach ($items as $item) {
                    DB::table('ec_shipping_rule_items')
                        ->where('id', $item->id)
                        ->update([
                            'zip_code_from' => preg_replace('/\D/', '', $item->zip_code_from),
                            'zip_code_to' => $item->zip_code_to ? preg_replace('/\D/', '', $item->zip_code_to) : null,
                        ]);
                }
            });

        DB::table('ec_shipping_rule_items')
            ->whereNotNull('zip_code')
            ->where('zip_code', '!=', '')
            ->orderBy('id')
            ->chunk(500, function ($items): void {
                foreach ($items as $item) {
                    DB::table('ec_shipping_rule_items')
                        ->where('id', $item->id)
                        ->update([
                            'zip_code' => preg_replace('/\D/', '', $item->zip_code),
                        ]);
                }
            });
    }

    public function down(): void
    {
    }
};
