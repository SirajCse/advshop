<?php

namespace Botble\SimpleSlider\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class SimpleSliderItem extends BaseModel
{
    protected $table = 'simple_slider_items';

    protected $fillable = [
        'title',
        'description',
        'link',
        'image',
        'order',
        'simple_slider_id',
        'status',
    ];

    protected $casts = [
        'title' => SafeContent::class,
        'description' => SafeContent::class,
        'link' => SafeContent::class,
        'status' => BaseStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::deleted(function (SimpleSliderItem $item): void {
            $item->metadata()->delete();
        });
    }
}
