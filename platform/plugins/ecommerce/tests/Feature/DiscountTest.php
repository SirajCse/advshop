<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Discount;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DiscountTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::instance('cart')->destroy();
    }

    public function test_can_create_fixed_discount(): void
    {
        $discount = Discount::query()->create([
            'code' => 'FIXED10',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertDatabaseHas('ec_discounts', [
            'code' => 'FIXED10',
            'value' => 10,
        ]);
    }

    public function test_can_create_percentage_discount(): void
    {
        $discount = Discount::query()->create([
            'code' => 'PERCENT20',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'value' => 20,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertDatabaseHas('ec_discounts', [
            'code' => 'PERCENT20',
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'value' => 20,
        ]);
    }

    public function test_discount_with_minimum_order_value(): void
    {
        $discount = Discount::query()->create([
            'code' => 'MIN100',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'min_order_price' => 100,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(100, $discount->min_order_price);
    }

    public function test_discount_with_usage_limit(): void
    {
        $discount = Discount::query()->create([
            'code' => 'LIMITED',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'quantity' => 100,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(100, $discount->quantity);
    }

    public function test_discount_is_expired(): void
    {
        $expiredDiscount = Discount::query()->create([
            'code' => 'EXPIRED',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'start_date' => now()->subWeek(),
            'end_date' => now()->subDay(),
        ]);

        $this->assertTrue($expiredDiscount->end_date->isPast());
    }

    public function test_discount_is_not_started(): void
    {
        $futureDiscount = Discount::query()->create([
            'code' => 'FUTURE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'start_date' => now()->addDay(),
            'end_date' => now()->addWeek(),
        ]);

        $this->assertTrue($futureDiscount->start_date->isFuture());
    }

    public function test_discount_is_active(): void
    {
        $activeDiscount = Discount::query()->create([
            'code' => 'ACTIVE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertTrue($activeDiscount->start_date->isPast());
        $this->assertTrue($activeDiscount->end_date->isFuture());
    }

    public function test_can_find_discount_by_code(): void
    {
        Discount::query()->create([
            'code' => 'FINDME',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 25,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discount = Discount::query()->where('code', 'FINDME')->first();

        $this->assertNotNull($discount);
        $this->assertEquals(25, $discount->value);
    }

    public function test_discount_code_is_case_insensitive_search(): void
    {
        Discount::query()->create([
            'code' => 'TESTCODE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discount = Discount::query()
            ->whereRaw('LOWER(code) = ?', ['testcode'])
            ->first();

        $this->assertNotNull($discount);
    }

    public function test_free_shipping_discount(): void
    {
        $discount = Discount::query()->create([
            'code' => 'FREESHIP',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::SHIPPING,
            'value' => 0,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTypeOptionEnum::SHIPPING, $discount->type_option);
    }

    public function test_can_update_discount(): void
    {
        $discount = Discount::query()->create([
            'code' => 'UPDATE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discount->update(['value' => 20]);

        $this->assertDatabaseHas('ec_discounts', [
            'id' => $discount->id,
            'value' => 20,
        ]);
    }

    public function test_can_delete_discount(): void
    {
        $discount = Discount::query()->create([
            'code' => 'DELETE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discountId = $discount->id;
        $discount->delete();

        $this->assertDatabaseMissing('ec_discounts', [
            'id' => $discountId,
        ]);
    }

    public function test_discount_total_used(): void
    {
        $discount = Discount::query()->create([
            'code' => 'TRACKED',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'quantity' => 100,
            'total_used' => 25,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(25, $discount->total_used);
        $this->assertEquals(75, $discount->quantity - $discount->total_used);
    }

    public function test_multiple_discounts_exist(): void
    {
        Discount::query()->create([
            'code' => 'DISCOUNT1',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        Discount::query()->create([
            'code' => 'DISCOUNT2',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'value' => 15,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        Discount::query()->create([
            'code' => 'DISCOUNT3',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 20,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discounts = Discount::query()->where('type', DiscountTypeEnum::COUPON)->get();

        $this->assertCount(3, $discounts);
    }
}
