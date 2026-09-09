<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\Concerns\HasUuidsOrIntegerIds;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductVariationItem extends Pivot
{
    use HasUuidsOrIntegerIds;

    protected $table = 'ec_product_variation_items';

    protected $fillable = [
        'attribute_id',
        'variation_id',
    ];

    public $timestamps = false;

    public $incrementing = true;

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id')->withDefault();
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id')->withDefault();
    }

    public static function getVariationsInfo(array $versionIds): Collection
    {
        return self::query()
            ->join('ec_product_attributes', 'ec_product_attributes.id', '=', 'ec_product_variation_items.attribute_id')
            ->join(
                'ec_product_attribute_sets',
                'ec_product_attribute_sets.id',
                '=',
                'ec_product_attributes.attribute_set_id'
            )
            ->distinct()
            ->whereIn('ec_product_variation_items.variation_id', $versionIds)
            ->select([
                'ec_product_variation_items.variation_id',
                'ec_product_attributes.id',
                'ec_product_attributes.slug',
                'ec_product_attributes.title',
                'ec_product_attributes.color',
                'ec_product_attributes.image',
                'ec_product_attributes.attribute_set_id',
                'ec_product_attributes.order',
                'ec_product_attribute_sets.id as attribute_set_id',
                'ec_product_attribute_sets.title as attribute_set_title',
                'ec_product_attribute_sets.slug as attribute_set_slug',
            ])
            ->get();
    }

    public static function getProductAttributes(int|string $productId): Collection
    {
        $result = self::query()
            ->join('ec_product_attributes', 'ec_product_attributes.id', '=', 'ec_product_variation_items.attribute_id')
            ->join(
                'ec_product_attribute_sets',
                'ec_product_attribute_sets.id',
                '=',
                'ec_product_attributes.attribute_set_id'
            )
            ->join('ec_product_variations', 'ec_product_variations.id', '=', 'ec_product_variation_items.variation_id')
            ->distinct()
            ->where('ec_product_variations.product_id', $productId)
            ->select([
                'ec_product_attributes.*',
                'ec_product_attribute_sets.title as attribute_set_title',
                'ec_product_attribute_sets.slug as attribute_set_slug',
            ])
            ->get();

        if (
            is_plugin_active('language-advanced')
            && class_exists(LanguageAdvancedManager::class)
            && ! LanguageAdvancedManager::isDefaultLocale()
        ) {
            $translatedAttributes = ProductAttribute::query()
                ->whereIn('id', $result->pluck('id')->all())
                ->with('productAttributeSet')
                ->get()
                ->keyBy('id');

            foreach ($result as $item) {
                if ($translated = $translatedAttributes->get($item->id)) {
                    $item->title = $translated->title;
                    $item->attribute_set_title = $translated->productAttributeSet?->title ?? $item->attribute_set_title;
                }
            }
        }

        return $result;
    }
}
