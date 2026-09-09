<?php

namespace Botble\Marketplace\Http\Resources\API;

use Botble\Marketplace\Models\Store;
use Botble\Media\Facades\RvMedia;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Store
 */
class StoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slugable?->key,
            'url' => $this->url,
            'phone' => $this->phone,
            'email' => $this->email,
            'description' => $this->description,
            'logo' => $this->logo ? RvMedia::getImageUrl($this->logo) : null,
            'logo_url' => $this->logo_url,
            'logo_with_sizes' => $this->logo ? rv_get_image_list([$this->logo], array_unique([
                'origin',
                'thumb',
                ...array_keys(RvMedia::getSizes()),
            ])) : null,
            'cover_image' => $this->cover_image ? RvMedia::getImageUrl($this->cover_image) : null,
            'products_count' => $this->products_count ?? 0,
            'reviews_avg' => $this->when(
                isset($this->reviews),
                fn () => round($this->reviews->avg('star') ?? 0, 1)
            ),
            'reviews_count' => $this->when(
                isset($this->reviews),
                fn () => $this->reviews->count()
            ),
            'is_verified' => (bool) $this->is_verified,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
