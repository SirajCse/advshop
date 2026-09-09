<?php

namespace Botble\Location\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class State extends BaseModel
{
    use HasSlug;

    protected $table = 'states';

    protected $fillable = [
        'name',
        'abbreviation',
        'country_id',
        'slug',
        'image',
        'order',
        'is_default',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
        'abbreviation' => SafeContent::class,
        'is_default' => 'bool',
        'order' => 'int',
    ];

    protected static function booted(): void
    {
        static::deleted(function (State $state): void {
            $state->cities->each->delete();
        });

        static::saving(function (self $model): void {
            $model->slug = self::createSlug($model->slug ?: $model->name, $model->getKey());
        });

        $clearCache = function (self $model): void {
            Cache::forget('location_state_' . $model->getKey() . '_default');
            Cache::forget('location_states_all_default');
            if ($model->country_id) {
                Cache::forget('location_states_' . $model->country_id . '_default');
            }
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class)->withDefault();
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
