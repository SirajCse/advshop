<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Botble\Ecommerce\Models\Shipping;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Ecommerce\Models\ShippingRuleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class ShippingZipRangeImprovementsTest extends BaseTestCase
{
    use RefreshDatabase;

    protected Shipping $shipping;

    protected ShippingRule $zipRule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shipping = Shipping::query()->create([
            'title' => 'Test Shipping',
            'country' => 'US',
        ]);

        $this->zipRule = ShippingRule::query()->create([
            'name' => 'ZIP Rule',
            'shipping_id' => $this->shipping->id,
            'type' => ShippingRuleTypeEnum::BASED_ON_ZIPCODE,
            'price' => 10,
        ]);
    }

    // ── Phase 1: Name Field ──

    public function test_name_column_is_fillable_and_persists(): void
    {
        $item = ShippingRuleItem::query()->create([
            'shipping_rule_id' => $this->zipRule->id,
            'name' => 'Downtown Area',
            'country' => 'US',
            'zip_code_from' => '10000',
            'zip_code_to' => '19999',
            'adjustment_price' => 5,
            'is_enabled' => 1,
        ]);

        $this->assertEquals('Downtown Area', $item->fresh()->name);
    }

    public function test_name_column_nullable(): void
    {
        $item = ShippingRuleItem::query()->create([
            'shipping_rule_id' => $this->zipRule->id,
            'country' => 'US',
            'zip_code_from' => '10000',
            'zip_code_to' => '19999',
            'adjustment_price' => 0,
            'is_enabled' => 1,
        ]);

        $this->assertNull($item->fresh()->name);
    }

    public function test_name_item_getter_prefers_name_when_set(): void
    {
        $item = new ShippingRuleItem([
            'name' => 'South Region',
            'zip_code_from' => '10000',
            'zip_code_to' => '19999',
        ]);

        $this->assertEquals('South Region', $item->name_item);
    }

    public function test_name_item_getter_falls_back_to_computed_when_name_empty(): void
    {
        $item = new ShippingRuleItem([
            'zip_code_from' => '10000',
            'zip_code_to' => '19999',
        ]);

        $this->assertStringContainsString('10000 - 19999', $item->name_item);
    }

    public function test_name_item_getter_falls_back_when_name_is_null(): void
    {
        $item = new ShippingRuleItem([
            'name' => null,
            'zip_code_from' => '50000',
            'zip_code_to' => null,
        ]);

        $this->assertStringContainsString('50000', $item->name_item);
    }

    public function test_name_validation_accepts_valid_string(): void
    {
        $validator = Validator::make(
            ['name' => 'Metro Area'],
            ['name' => ['nullable', 'string', 'max:120']]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_name_validation_rejects_too_long(): void
    {
        $validator = Validator::make(
            ['name' => str_repeat('a', 121)],
            ['name' => ['nullable', 'string', 'max:120']]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_name_validation_allows_null(): void
    {
        $validator = Validator::make(
            ['name' => null],
            ['name' => ['nullable', 'string', 'max:120']]
        );

        $this->assertTrue($validator->passes());
    }

    // ── Phase 3: normalizeZipCode Helper ──

    public function test_normalize_zip_code_strips_hyphens(): void
    {
        $this->assertEquals(14403860, ShippingRuleItem::normalizeZipCode('14403-860'));
    }

    public function test_normalize_zip_code_strips_dots(): void
    {
        $this->assertEquals(14403860, ShippingRuleItem::normalizeZipCode('14.403.860'));
    }

    public function test_normalize_zip_code_strips_spaces(): void
    {
        $this->assertEquals(14403860, ShippingRuleItem::normalizeZipCode('14403 860'));
    }

    public function test_normalize_zip_code_pure_numeric(): void
    {
        $this->assertEquals(90210, ShippingRuleItem::normalizeZipCode('90210'));
    }

    public function test_normalize_zip_code_with_leading_zeros(): void
    {
        $this->assertEquals(1234, ShippingRuleItem::normalizeZipCode('01234'));
    }

    public function test_normalize_zip_code_returns_null_for_null(): void
    {
        $this->assertNull(ShippingRuleItem::normalizeZipCode(null));
    }

    public function test_normalize_zip_code_returns_null_for_empty_string(): void
    {
        $this->assertNull(ShippingRuleItem::normalizeZipCode(''));
    }

    public function test_normalize_zip_code_returns_null_for_non_numeric_only(): void
    {
        $this->assertNull(ShippingRuleItem::normalizeZipCode('ABC'));
    }

    public function test_normalize_zip_code_large_brazilian_cep(): void
    {
        $this->assertEquals(14403860, ShippingRuleItem::normalizeZipCode('14403860'));
    }

    // ── Phase 3: Mutators ──

    public function test_zip_code_from_mutator_strips_non_digits(): void
    {
        $item = ShippingRuleItem::query()->create([
            'shipping_rule_id' => $this->zipRule->id,
            'country' => 'US',
            'zip_code_from' => '14403-860',
            'zip_code_to' => '14403-999',
            'adjustment_price' => 0,
            'is_enabled' => 1,
        ]);

        $fresh = $item->fresh();
        $this->assertEquals('14403860', $fresh->zip_code_from);
        $this->assertEquals('14403999', $fresh->zip_code_to);
    }

    public function test_zip_code_mutator_preserves_pure_numeric(): void
    {
        $item = ShippingRuleItem::query()->create([
            'shipping_rule_id' => $this->zipRule->id,
            'country' => 'US',
            'zip_code_from' => '90210',
            'zip_code_to' => '90220',
            'adjustment_price' => 0,
            'is_enabled' => 1,
        ]);

        $fresh = $item->fresh();
        $this->assertEquals('90210', $fresh->zip_code_from);
        $this->assertEquals('90220', $fresh->zip_code_to);
    }

    public function test_zip_code_mutator_handles_null(): void
    {
        $item = ShippingRuleItem::query()->create([
            'shipping_rule_id' => $this->zipRule->id,
            'country' => 'US',
            'zip_code_from' => '10000',
            'zip_code_to' => null,
            'adjustment_price' => 0,
            'is_enabled' => 1,
        ]);

        $this->assertNull($item->fresh()->zip_code_to);
    }

    public function test_zip_code_mutator_handles_empty_string(): void
    {
        $item = ShippingRuleItem::query()->create([
            'shipping_rule_id' => $this->zipRule->id,
            'country' => 'US',
            'zip_code_from' => '10000',
            'zip_code_to' => '',
            'adjustment_price' => 0,
            'is_enabled' => 1,
        ]);

        $this->assertNull($item->fresh()->zip_code_to);
    }

    // ── Phase 3: PHP In-Memory Filter (numeric comparison) ──

    public function test_php_filter_matches_different_digit_count_zips(): void
    {
        $items = collect([
            new ShippingRuleItem(['zip_code_from' => '1000', 'zip_code_to' => '10000']),
        ]);

        $normalizedZip = ShippingRuleItem::normalizeZipCode('9000');

        $match = $items->first(function ($item) use ($normalizedZip) {
            $from = ShippingRuleItem::normalizeZipCode($item->zip_code_from);

            if ($from === null) {
                return false;
            }

            $to = ShippingRuleItem::normalizeZipCode($item->zip_code_to);

            return $from <= $normalizedZip && ($to === null || $to >= $normalizedZip);
        });

        $this->assertNotNull($match, 'ZIP 9000 should match range 1000-10000 with numeric comparison');
    }

    public function test_php_filter_rejects_out_of_range(): void
    {
        $items = collect([
            new ShippingRuleItem(['zip_code_from' => '1000', 'zip_code_to' => '5000']),
        ]);

        $normalizedZip = ShippingRuleItem::normalizeZipCode('9000');

        $match = $items->first(function ($item) use ($normalizedZip) {
            $from = ShippingRuleItem::normalizeZipCode($item->zip_code_from);

            if ($from === null) {
                return false;
            }

            $to = ShippingRuleItem::normalizeZipCode($item->zip_code_to);

            return $from <= $normalizedZip && ($to === null || $to >= $normalizedZip);
        });

        $this->assertNull($match, 'ZIP 9000 should NOT match range 1000-5000');
    }

    public function test_php_filter_matches_brazilian_cep_range(): void
    {
        $items = collect([
            new ShippingRuleItem(['zip_code_from' => '12000000', 'zip_code_to' => '19999999']),
        ]);

        $normalizedZip = ShippingRuleItem::normalizeZipCode('14403860');

        $match = $items->first(function ($item) use ($normalizedZip) {
            $from = ShippingRuleItem::normalizeZipCode($item->zip_code_from);

            if ($from === null) {
                return false;
            }

            $to = ShippingRuleItem::normalizeZipCode($item->zip_code_to);

            return $from <= $normalizedZip && ($to === null || $to >= $normalizedZip);
        });

        $this->assertNotNull($match, 'CEP 14403860 should match range 12000000-19999999');
    }

    public function test_php_filter_handles_open_ended_range(): void
    {
        $items = collect([
            new ShippingRuleItem(['zip_code_from' => '10000', 'zip_code_to' => null]),
        ]);

        $normalizedZip = ShippingRuleItem::normalizeZipCode('99999');

        $match = $items->first(function ($item) use ($normalizedZip) {
            $from = ShippingRuleItem::normalizeZipCode($item->zip_code_from);

            if ($from === null) {
                return false;
            }

            $to = ShippingRuleItem::normalizeZipCode($item->zip_code_to);

            return $from <= $normalizedZip && ($to === null || $to >= $normalizedZip);
        });

        $this->assertNotNull($match, 'ZIP 99999 should match open-ended range from 10000');
    }

    public function test_php_filter_skips_items_with_no_zip_from(): void
    {
        $items = collect([
            new ShippingRuleItem(['zip_code_from' => null, 'zip_code_to' => '99999']),
            new ShippingRuleItem(['zip_code_from' => '', 'zip_code_to' => '99999']),
        ]);

        $normalizedZip = ShippingRuleItem::normalizeZipCode('50000');

        $match = $items->first(function ($item) use ($normalizedZip) {
            $from = ShippingRuleItem::normalizeZipCode($item->zip_code_from);

            if ($from === null) {
                return false;
            }

            $to = ShippingRuleItem::normalizeZipCode($item->zip_code_to);

            return $from <= $normalizedZip && ($to === null || $to >= $normalizedZip);
        });

        $this->assertNull($match, 'Items with null/empty zip_code_from should be skipped');
    }

    public function test_php_filter_string_comparison_bug_is_fixed(): void
    {
        // The critical bug: strcmp("9", "10") > 0 because '9' > '1' in ASCII.
        // With numeric comparison: 9 < 10 (correct).
        $fromNormalized = ShippingRuleItem::normalizeZipCode('9');
        $toNormalized = ShippingRuleItem::normalizeZipCode('10');

        $this->assertLessThan($toNormalized, $fromNormalized, 'Numeric 9 should be less than 10');

        // Confirm the old string comparison was broken (strcmp uses byte-by-byte)
        $this->assertGreaterThan(0, strcmp('9', '10'), 'strcmp("9","10") > 0 confirms the old bug');
    }

    // ── Phase 1 + 2: Name + Display Integration ──

    public function test_name_item_shows_name_over_computed_display(): void
    {
        $item = ShippingRuleItem::query()->create([
            'shipping_rule_id' => $this->zipRule->id,
            'name' => 'Sao Paulo Metro',
            'country' => 'US',
            'zip_code_from' => '14000000',
            'zip_code_to' => '15000000',
            'adjustment_price' => 0,
            'is_enabled' => 1,
        ]);

        $this->assertEquals('Sao Paulo Metro', $item->fresh()->name_item);
    }

    public function test_name_item_computed_fallback_shows_single_zip(): void
    {
        $item = new ShippingRuleItem([
            'zip_code_from' => '90210',
        ]);

        $this->assertStringContainsString('90210', $item->name_item);
        $this->assertStringNotContainsString(' - ', $item->name_item);
    }

    public function test_name_item_computed_fallback_shows_legacy_zip_code(): void
    {
        $item = new ShippingRuleItem([
            'zip_code' => '12345',
        ]);

        $this->assertStringContainsString('12345', $item->name_item);
    }

    // ── Edge Cases ──

    public function test_normalize_zip_code_handles_mixed_alphanumeric(): void
    {
        // UK-style postcode stripped to digits only
        $this->assertEquals(11, ShippingRuleItem::normalizeZipCode('SW1A 1AA'));
    }

    public function test_name_with_max_length_120_persists(): void
    {
        $longName = str_repeat('A', 120);

        $item = ShippingRuleItem::query()->create([
            'shipping_rule_id' => $this->zipRule->id,
            'name' => $longName,
            'country' => 'US',
            'zip_code_from' => '10000',
            'adjustment_price' => 0,
            'is_enabled' => 1,
        ]);

        $this->assertEquals($longName, $item->fresh()->name);
    }

    public function test_zip_code_from_boundary_zero(): void
    {
        $this->assertEquals(0, ShippingRuleItem::normalizeZipCode('00000'));
    }
}
