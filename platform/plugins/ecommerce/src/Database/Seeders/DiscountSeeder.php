<?php

namespace Botble\Ecommerce\Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;
use Botble\Ecommerce\Models\Discount;
use Illuminate\Support\Str;

class DiscountSeeder extends BaseSeeder
{
    public function run(): void
    {
        Discount::query()->truncate();

        $now = $this->now();

        $discounts = [
            ['title' => 'Discount 1', 'type_option' => DiscountTypeOptionEnum::PERCENTAGE, 'value' => 10, 'end_days' => 15],
            ['title' => 'Discount 2', 'type_option' => DiscountTypeOptionEnum::AMOUNT, 'value' => 50, 'end_days' => null],
            ['title' => 'Discount 3', 'type_option' => DiscountTypeOptionEnum::PERCENTAGE, 'value' => 25, 'end_days' => 7],
            ['title' => 'Discount 4', 'type_option' => DiscountTypeOptionEnum::AMOUNT, 'value' => 100, 'end_days' => null],
            ['title' => 'Discount 5', 'type_option' => DiscountTypeOptionEnum::PERCENTAGE, 'value' => 50, 'end_days' => 20],
            ['title' => 'Discount 6', 'type_option' => DiscountTypeOptionEnum::AMOUNT, 'value' => 200, 'end_days' => 10],
            ['title' => 'Discount 7', 'type_option' => DiscountTypeOptionEnum::PERCENTAGE, 'value' => 15, 'end_days' => null],
            ['title' => 'Discount 8', 'type_option' => DiscountTypeOptionEnum::AMOUNT, 'value' => 500, 'end_days' => 30],
            ['title' => 'Discount 9', 'type_option' => DiscountTypeOptionEnum::PERCENTAGE, 'value' => 75, 'end_days' => 5],
            ['title' => 'Discount 10', 'type_option' => DiscountTypeOptionEnum::AMOUNT, 'value' => 150, 'end_days' => null],
        ];

        foreach ($discounts as $discount) {
            Discount::query()->create([
                'type' => DiscountTypeEnum::COUPON,
                'title' => $discount['title'],
                'code' => strtoupper(Str::random(12)),
                'start_date' => $now->clone()->subDay(),
                'end_date' => $discount['end_days'] ? $now->clone()->addDays($discount['end_days']) : null,
                'type_option' => $discount['type_option'],
                'value' => $discount['value'],
                'display_at_checkout' => true,
            ]);
        }
    }
}
