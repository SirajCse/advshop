<?php

use Botble\Ecommerce\Models\ProductCategory;
use Botble\Slug\Models\Slug;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $categoryIdsWithMissingSlugs = ProductCategory::query()
            ->whereNull('slug')
            ->orWhere('slug', '')
            ->pluck('id')
            ->toArray();

        if (empty($categoryIdsWithMissingSlugs)) {
            return;
        }

        $slugs = Slug::query()
            ->where('reference_type', ProductCategory::class)
            ->whereIn('reference_id', $categoryIdsWithMissingSlugs)
            ->pluck('key', 'reference_id')
            ->toArray();

        if (! empty($slugs)) {
            ProductCategory::query()
                ->whereIn('id', array_keys($slugs))
                ->chunkById(1000, function ($categories) use ($slugs): void {
                    foreach ($categories as $category) {
                        if (isset($slugs[$category->id])) {
                            $category->slug = $slugs[$category->id];
                            $category->saveQuietly();
                        }
                    }
                });
        }

        if (Schema::hasTable('ec_product_categories_translations') && Schema::hasTable('slugs_translations')) {
            $translationIdsWithMissingSlugs = DB::table('ec_product_categories_translations')
                ->where(function ($query): void {
                    $query->whereNull('slug')->orWhere('slug', '');
                })
                ->pluck('ec_product_categories_id')
                ->toArray();

            if (empty($translationIdsWithMissingSlugs)) {
                return;
            }

            $translatedSlugs = DB::table('slugs_translations')
                ->join('slugs', 'slugs.id', '=', 'slugs_translations.slugs_id')
                ->where('slugs.reference_type', ProductCategory::class)
                ->whereIn('slugs.reference_id', $translationIdsWithMissingSlugs)
                ->select([
                    'slugs.reference_id',
                    'slugs_translations.lang_code',
                    'slugs_translations.key',
                ])
                ->get()
                ->groupBy('lang_code');

            foreach ($translatedSlugs as $langCode => $slugsByLang) {
                $updates = [];
                foreach ($slugsByLang as $slug) {
                    $updates[$slug->reference_id] = $slug->key;
                }

                if (! empty($updates)) {
                    $cases = [];
                    $ids = [];

                    foreach ($updates as $id => $slugValue) {
                        $cases[] = "WHEN ec_product_categories_id = {$id} THEN " . DB::getPdo()->quote($slugValue);
                        $ids[] = $id;
                    }

                    $idsString = implode(',', $ids);
                    $casesString = implode(' ', $cases);

                    DB::update("
                        UPDATE ec_product_categories_translations
                        SET slug = CASE {$casesString} END
                        WHERE ec_product_categories_id IN ({$idsString})
                        AND lang_code = ?
                        AND (slug IS NULL OR slug = '')
                    ", [$langCode]);
                }
            }
        }
    }

    public function down(): void
    {
    }
};
