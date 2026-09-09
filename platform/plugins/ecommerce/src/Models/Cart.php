<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Cart extends BaseModel
{
    protected $table = 'ec_cart';

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'identifier',
        'instance',
        'content',
        'customer_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function scopeForCustomer(Builder $query, int|string $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeInstance(Builder $query, string $instance = 'cart'): Builder
    {
        return $query->where('instance', $instance);
    }

    public function scopeForIdentifier(Builder $query, string $identifier): Builder
    {
        return $query->where('identifier', $identifier);
    }

    protected function items(): Attribute
    {
        return Attribute::get(function (): Collection {
            if (empty($this->content)) {
                return collect();
            }

            $unserialized = @unserialize($this->content);

            return $unserialized instanceof Collection ? $unserialized : collect();
        });
    }

    protected function itemCount(): Attribute
    {
        return Attribute::get(fn () => $this->items->sum('qty'));
    }

    protected function rawTotal(): Attribute
    {
        return Attribute::get(fn () => $this->items->sum(fn ($item) => $item->qty * $item->price));
    }
}
