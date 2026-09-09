<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Traits\LocationTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRuleItem extends BaseModel
{
    use LocationTrait;

    protected $table = 'ec_shipping_rule_items';

    protected $fillable = [
        'shipping_rule_id',
        'name',
        'country',
        'state',
        'city',
        'adjustment_price',
        'is_enabled',
        'zip_code',
        'zip_code_from',
        'zip_code_to',
    ];

    public function shippingRule(): BelongsTo
    {
        return $this->belongsTo(ShippingRule::class)->withDefault();
    }

    protected function adjustmentPrice(): Attribute
    {
        return Attribute::set(fn (?string $value) => (float) str_replace(',', '', $value));
    }

    public static function normalizeZipCode(?string $zipCode): ?int
    {
        if ($zipCode === null || $zipCode === '') {
            return null;
        }

        $numeric = preg_replace('/\D/', '', $zipCode);

        return $numeric !== '' ? (int) $numeric : null;
    }

    protected function zipCodeFrom(): Attribute
    {
        return Attribute::set(function (?string $value) {
            if ($value === null || $value === '') {
                return null;
            }

            return preg_replace('/\D/', '', $value);
        });
    }

    protected function zipCodeTo(): Attribute
    {
        return Attribute::set(function (?string $value) {
            if ($value === null || $value === '') {
                return null;
            }

            return preg_replace('/\D/', '', $value);
        });
    }

    protected function nameItem(): Attribute
    {
        return Attribute::get(function () {
            if ($this->name) {
                return $this->name;
            }

            $zipDisplay = $this->zip_code_from;

            if ($this->zip_code_from && $this->zip_code_to && $this->zip_code_from !== $this->zip_code_to) {
                $zipDisplay = $this->zip_code_from . ' - ' . $this->zip_code_to;
            }

            return trim(implode(', ', array_filter([$this->state_name, $this->city_name, $zipDisplay ?: $this->zip_code])));
        });
    }
}
