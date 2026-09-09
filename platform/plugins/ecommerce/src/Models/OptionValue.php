<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Option\OptionType\Field;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class OptionValue extends BaseModel
{
    protected $table = 'ec_option_value';

    protected $fillable = [
        'option_id',
        'option_value',
        'affect_price',
        'affect_type',
        'order',
    ];

    protected static function booted(): void
    {
        self::deleted(function (OptionValue $optionValue): void {
            $optionValue->translations()->delete();
        });
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'option_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(OptionValueTranslation::class, 'ec_option_value_id');
    }

    public function getTranslation(string $langCode): ?OptionValueTranslation
    {
        return $this->translations->firstWhere('lang_code', $langCode);
    }

    public function saveTranslation(string $langCode, string $optionValue): void
    {
        DB::table('ec_option_value_translations')->updateOrInsert(
            [
                'ec_option_value_id' => $this->id,
                'lang_code' => $langCode,
            ],
            ['option_value' => $optionValue]
        );
    }

    protected function formatPrice(): Attribute
    {
        return Attribute::get(fn () => format_price($this->price));
    }

    protected function price(): Attribute
    {
        return Attribute::get(function (): float|int {
            $option = $this->option;

            if ($option->option_type == Field::class) {
                return 0;
            }

            $product = $option->product;

            return $this->affect_type == 0 ? $this->affect_price : (floatval($this->affect_price) * $product->original_price) / 100;
        });
    }
}
