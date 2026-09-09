<?php

namespace Botble\Ecommerce\Http\Resources\API;

use Botble\Ecommerce\Models\Product;
use Botble\Media\Facades\RvMedia;
use Botble\Shortcode\Facades\Shortcode;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductSearchResource extends JsonResource
{
    public function toArray($request): array
    {
        $price = $this->price();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'url' => $this->url,
            'image' => RvMedia::getImageUrl($this->image, 'thumb', false, RvMedia::getDefaultImage()),
            'description' => Shortcode::compile((string) $this->description, true)->toHtml(),
            'price' => $price->getPrice(),
            'price_formatted' => $price->displayAsText(),
            'original_price' => $price->getPriceOriginal(),
            'original_price_formatted' => $price->displayPriceOriginalAsText(),
            'reviews_avg' => (float) $this->reviews_avg,
            'reviews_count' => (int) $this->reviews_count,
        ];
    }
}
