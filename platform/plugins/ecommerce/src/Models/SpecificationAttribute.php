<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Facades\AdminHelper;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Enums\SpecificationAttributeFieldType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class SpecificationAttribute extends BaseModel
{
    protected $table = 'ec_specification_attributes';

    protected $fillable = [
        'author_type',
        'author_id',
        'group_id',
        'name',
        'type',
        'options',
        'default_value',
    ];

    protected $casts = [
        'options' => 'array',
        'type' => SpecificationAttributeFieldType::class,
    ];

    protected static function booted(): void
    {
        if (AdminHelper::isInAdmin(true)) {
            static::addGlobalScope('admin', function ($query): void {
                $query->whereNull('author_id');
            });
        }

        static::saving(function (self $attribute): void {
            if (! is_array($attribute->options) || empty($attribute->options)) {
                return;
            }

            $options = $attribute->options;
            $needsUpdate = false;

            foreach ($options as &$opt) {
                if (is_array($opt) && empty($opt['id'])) {
                    $opt['id'] = self::generateOptionId();
                    $needsUpdate = true;
                }
            }

            if ($needsUpdate) {
                $attribute->options = $options;
            }
        });

        static::deleted(function (self $attribute): void {
            $attribute->products()->detach();
        });
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'ec_product_specification_attribute', 'attribute_id', 'product_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(SpecificationGroup::class, 'group_id');
    }

    public static function generateOptionId(): string
    {
        return bin2hex(random_bytes(4));
    }

    public function hasOptions(): bool
    {
        return in_array($this->type, [
            SpecificationAttributeFieldType::SELECT,
            SpecificationAttributeFieldType::RADIO,
        ]);
    }

    public function hasIdBasedOptions(): bool
    {
        $options = $this->options;

        if (empty($options)) {
            return false;
        }

        $first = $options[0] ?? null;

        return is_array($first) && isset($first['id']);
    }

    public function getIdBasedOptions(): array
    {
        $options = $this->options ?? [];

        if (empty($options)) {
            return [];
        }

        if ($this->hasIdBasedOptions()) {
            return $options;
        }

        return array_map(fn (string $value) => [
            'id' => self::generateOptionId(),
            'value' => $value,
        ], $options);
    }

    public function getOptionValueById(string $id): ?string
    {
        foreach ($this->getIdBasedOptions() as $option) {
            if ($option['id'] === $id) {
                return $option['value'];
            }
        }

        return null;
    }

    public function getOptionIdByValue(string $value): ?string
    {
        foreach ($this->getIdBasedOptions() as $option) {
            if ($option['value'] === $value) {
                return $option['id'];
            }
        }

        return null;
    }

    public function getDefaultLanguageOptions(): array
    {
        $rawOptions = DB::table('ec_specification_attributes')
            ->where('id', $this->getKey())
            ->value('options');

        $options = json_decode($rawOptions ?: '', true) ?: [];

        if (empty($options)) {
            return [];
        }

        $first = $options[0] ?? null;

        if (is_array($first) && isset($first['id'])) {
            return $options;
        }

        return array_map(fn (string $value) => [
            'id' => self::generateOptionId(),
            'value' => $value,
        ], $options);
    }
}
