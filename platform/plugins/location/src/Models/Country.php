<?php

namespace Botble\Location\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Country extends BaseModel
{
    protected $table = 'countries';

    protected $fillable = [
        'name',
        'nationality',
        'code',
        'order',
        'is_default',
        'status',
        'image',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
        'nationality' => SafeContent::class,
        'code' => SafeContent::class,
        'is_default' => 'bool',
        'order' => 'int',
    ];

    protected static function booted(): void
    {
        static::deleted(function (Country $country): void {
            $country->cities->each->delete();
            $country->states->each->delete();
        });

        $clearCache = function (self $model): void {
            Cache::forget('location_countries_default');
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
