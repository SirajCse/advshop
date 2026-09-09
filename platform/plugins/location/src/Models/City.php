<?php

namespace Botble\Location\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class City extends BaseModel
{
    use HasSlug;

    protected $table = 'cities';

    protected $fillable = [
        'name',
        'state_id',
        'country_id',
        'record_id',
        'zip_code',
        'slug',
        'image',
        'order',
        'is_default',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
        'is_default' => 'bool',
        'order' => 'int',
    ];

    protected static function booted(): void
    {
        self::saving(function (self $model): void {
            $model->slug = self::createSlug($model->slug ?: $model->name, $model->getKey());
        });

        $clearCache = function (self $model): void {
            Cache::forget('location_city_' . $model->getKey() . '_default');
            if ($model->state_id) {
                Cache::forget('location_cities_state_' . $model->state_id . '_default');
            }
            if ($model->country_id) {
                Cache::forget('location_cities_country_' . $model->country_id . '_default');
            }
        };

        self::saved($clearCache);
        self::deleted($clearCache);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class)->withDefault();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class)->withDefault();
    }
}
