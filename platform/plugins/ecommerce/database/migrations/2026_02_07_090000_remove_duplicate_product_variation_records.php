<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        try {
            if (! Schema::hasTable('ec_product_variations')) {
                return;
            }

            // Remove duplicate variation records, keeping only the latest (highest ID) for each product_id
            $duplicateIds = DB::table('ec_product_variations as pv')
                ->join(
                    DB::raw('(SELECT product_id, MAX(id) as max_id FROM ec_product_variations GROUP BY product_id HAVING COUNT(*) > 1) as duplicates'),
                    function ($join) {
                        $join->on('pv.product_id', '=', 'duplicates.product_id')
                            ->whereColumn('pv.id', '<', 'duplicates.max_id');
                    }
                )
                ->pluck('pv.id')
                ->all();

            // Remove variation records where product_id points to a non-variation (parent) product
            $parentProductIds = DB::table('ec_product_variations as pv')
                ->join('ec_products as p', 'p.id', '=', 'pv.product_id')
                ->where('p.is_variation', 0)
                ->pluck('pv.id')
                ->all();

            $idsToDelete = array_unique(array_merge($duplicateIds, $parentProductIds));

            if ($idsToDelete) {
                DB::table('ec_product_variations')
                    ->whereIn('id', $idsToDelete)
                    ->delete();
            }
        } catch (Throwable) {
        }
    }
};
