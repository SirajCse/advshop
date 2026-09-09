<?php

namespace Botble\Marketplace\Http\Resources\API;

use Botble\Marketplace\Models\Store;
use Botble\Media\Facades\RvMedia;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Store
 */
class StoreDetailResource extends JsonResource
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
            'content' => $this->content,
            'logo' => $this->logo ? RvMedia::getImageUrl($this->logo) : null,
            'logo_url' => $this->logo_url,
            'logo_square' => $this->logo_square ? RvMedia::getImageUrl($this->logo_square) : null,
            'logo_with_sizes' => $this->logo ? rv_get_image_list([$this->logo], array_unique([
                'origin',
                'thumb',
                ...array_keys(RvMedia::getSizes()),
            ])) : null,
            'cover_image' => $this->cover_image ? RvMedia::getImageUrl($this->cover_image) : null,
            'cover_image_with_sizes' => $this->cover_image ? rv_get_image_list([$this->cover_image], array_unique([
                'origin',
                'thumb',
                ...array_keys(RvMedia::getSizes()),
            ])) : null,
            'address' => $this->full_address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'zip_code' => $this->zip_code,
            'company' => $this->company,
            'tax_id' => $this->tax_id,
            'is_verified' => (bool) $this->is_verified,
            'products_count' => $this->products_count ?? $this->products()->wherePublished()->count(),
            'reviews_avg' => $this->when(
                isset($this->reviews),
                fn () => round($this->reviews->avg('star') ?? 0, 1),
                fn () => 0
            ),
            'reviews_count' => $this->when(
                isset($this->reviews),
                fn () => $this->reviews->count(),
                fn () => 0
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
