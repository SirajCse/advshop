<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Botble\Ecommerce\Models\Shipping;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Ecommerce\Models\ShippingRuleItem;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShippingZipRangeTest extends BaseTestCase
{
    use RefreshDatabase;

    protected Shipping $shipping;

    protected HandleShippingFeeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shipping = Shipping::query()->create([
            'title' => 'Test Shipping',
            'country' => 'US',
        ]);

        $this->service = new HandleShippingFeeService();
    }

    public function test_exact_zip_match_with_range_columns(): void
    {
        $rule = ShippingRule::query()->create([
            'name' => 'ZIP Rule',
            'shipping_id' => $this->shipping->id,
            'type' => ShippingRuleTypeEnum::BASED_ON_ZIPCODE,
            'price' => 10,
        ]);

        ShippingRuleItem::query()->create([
            'shipping_rule_id' => $rule->id,
            'country' => 'US',
            'zip_code_from' => '10001',
            'zip_code_to' => '10001',
            'adjustment_price' => 5,
            'is_enabled' => 1,
        ]);

        $item = ShippingRuleItem::query()
            ->where('zip_code_from', '<=', '10001')
            ->where('zip_code_to', '>=', '10001')
            ->first();

        $this->assertNotNull($item);
        $this->assertEquals('10001', $item->zip_code_from);
    }

    public function test_zip_range_match(): void
    {
        $rule = ShippingRule::query()->create([
            'name' => 'ZIP Range Rule',
            'shipping_id' => $this->shipping->id,
            'type' => ShippingRuleTypeEnum::BASED_ON_ZIPCODE,
            'price' => 10,
        ]);

        ShippingRuleItem::query()->create([
            'shipping_rule_id' => $rule->id,
            'country' => 'US',
            'zip_code_from' => '10000',
            'zip_code_to' => '19999',
            'adjustment_price' => 5,
            'is_enabled' => 1,
        ]);

        $matchInRange = ShippingRuleItem::query()
            ->where('zip_code_from', '<=', '15000')
            ->where('zip_code_to', '>=', '15000')
            ->first();

        $this->assertNotNull($matchInRange);

        $noMatch = ShippingRuleItem::query()
            ->where('zip_code_from', '<=', '20000')
            ->where('zip_code_to', '>=', '20000')
            ->first();

        $this->assertNull($noMatch);
    }

    public function test_zip_range_boundary_match(): void
    {
        $rule = ShippingRule::query()->create([
            'name' => 'Boundary Rule',
            'shipping_id' => $this->shipping->id,
            'type' => ShippingRuleTypeEnum::BASED_ON_ZIPCODE,
            'price' => 10,
        ]);

        ShippingRuleItem::query()->create([
            'shipping_rule_id' => $rule->id,
            'country' => 'US',
            'zip_code_from' => '10000',
            'zip_code_to' => '19999',
            'adjustment_price' => 0,
            'is_enabled' => 1,
        ]);

        $matchLower = ShippingRuleItem::query()
            ->where('zip_code_from', '<=', '10000')
            ->where('zip_code_to', '>=', '10000')
            ->first();
        $this->assertNotNull($matchLower);

        $matchUpper = ShippingRuleItem::query()
            ->where('zip_code_from', '<=', '19999')
            ->where('zip_code_to', '>=', '19999')
            ->first();
        $this->assertNotNull($matchUpper);
    }

    public function test_open_ended_zip_range(): void
    {
        $rule = ShippingRule::query()->create([
            'name' => 'Open Range Rule',
            'shipping_id' => $this->shipping->id,
            'type' => ShippingRuleTypeEnum::BASED_ON_ZIPCODE,
            'price' => 10,
        ]);

        ShippingRuleItem::query()->create([
            'shipping_rule_id' => $rule->id,
            'country' => 'US',
            'zip_code_from' => '10000',
            'zip_code_to' => null,
            'adjustment_price' => 0,
            'is_enabled' => 1,
        ]);

        $match = ShippingRuleItem::query()
            ->where('zip_code_from', '<=', '99999')
            ->where(function ($q): void {
                $q->where('zip_code_to', '>=', '99999')->orWhereNull('zip_code_to');
            })
            ->first();

        $this->assertNotNull($match);
    }

    public function test_combined_zip_and_weight_rule_type_exists(): void
    {
        $this->assertEquals('based_on_zipcode_and_weight', ShippingRuleTypeEnum::BASED_ON_ZIPCODE_AND_WEIGHT);
    }

    public function test_combined_type_shows_from_to_inputs(): void
    {
        $type = ShippingRuleTypeEnum::BASED_ON_ZIPCODE_AND_WEIGHT();
        $this->assertTrue($type->showFromToInputs());
    }

    public function test_combined_type_allows_rule_items(): void
    {
        $type = ShippingRuleTypeEnum::BASED_ON_ZIPCODE_AND_WEIGHT();
        $this->assertTrue($type->allowRuleItems());
    }

    public function test_shipping_rule_item_name_shows_range(): void
    {
        $item = new ShippingRuleItem([
            'zip_code_from' => '10000',
            'zip_code_to' => '19999',
        ]);

        $this->assertStringContainsString('10000 - 19999', $item->name_item);
    }

    public function test_shipping_rule_item_name_shows_single_when_equal(): void
    {
        $item = new ShippingRuleItem([
            'zip_code_from' => '10001',
            'zip_code_to' => '10001',
        ]);

        $this->assertStringContainsString('10001', $item->name_item);
        $this->assertStringNotContainsString(' - ', $item->name_item);
    }

    public function test_migration_columns_exist(): void
    {
        $item = ShippingRuleItem::query()->create([
            'shipping_rule_id' => ShippingRule::query()->create([
                'name' => 'Test',
                'shipping_id' => $this->shipping->id,
                'type' => ShippingRuleTypeEnum::BASED_ON_ZIPCODE,
                'price' => 0,
            ])->id,
            'country' => 'US',
            'zip_code_from' => '01000',
            'zip_code_to' => '01999',
            'adjustment_price' => 0,
            'is_enabled' => 1,
        ]);

        $this->assertEquals('01000', $item->fresh()->zip_code_from);
        $this->assertEquals('01999', $item->fresh()->zip_code_to);
    }
}
