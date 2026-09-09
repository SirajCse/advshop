<?php

namespace Botble\Base\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasPhoneNumber
{
    public function scopeWherePhone(Builder $query, ?string $phone, string $column = 'phone'): Builder
    {
        if (empty($phone)) {
            return $query->whereRaw('1 = 0');
        }

        $normalizedPhone = static::normalizePhone($phone);

        if (empty($normalizedPhone)) {
            return $query->whereRaw('1 = 0');
        }

        $phoneVariants = static::getPhoneVariants($normalizedPhone);

        return $query->where(function (Builder $query) use ($phone, $phoneVariants, $column): void {
            $query->where($column, $phone);

            foreach ($phoneVariants as $variant) {
                $query->orWhere($column, 'LIKE', '%' . $variant);
            }
        });
    }

    public static function getPhoneVariants(string $normalizedPhone): array
    {
        $variants = [$normalizedPhone];

        $phoneWithoutLeadingZero = ltrim($normalizedPhone, '0');
        if ($phoneWithoutLeadingZero !== $normalizedPhone) {
            $variants[] = $phoneWithoutLeadingZero;
        }

        if (strlen($normalizedPhone) >= 11 && str_starts_with($normalizedPhone, '84')) {
            $variants[] = substr($normalizedPhone, 2);
        }

        return array_unique($variants);
    }

    public static function normalizePhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        return preg_replace('/[^0-9]/', '', $phone);
    }
}
