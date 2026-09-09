<?php

namespace Botble\Ecommerce\Tests\Unit;

use PHPUnit\Framework\TestCase;

class TaxDiscountCalculationTest extends TestCase
{
    public function test_tax_calculation_with_50_percent_discount_price_excludes_tax(): void
    {
        $price = 100;
        $taxRate = 10;
        $priceIncludesTax = false;

        $rawTotal = $priceIncludesTax ? $price : $price * (1 + $taxRate / 100);
        $this->assertEqualsWithDelta(110, $rawTotal, 0.01);

        $totalDiscountAmount = 55;
        $discountRatio = $rawTotal > 0 ? max(0, $rawTotal - $totalDiscountAmount) / $rawTotal : 0;
        $this->assertEqualsWithDelta(0.5, $discountRatio, 0.001);

        $effectiveItemPrice = $price * $discountRatio;
        $this->assertEqualsWithDelta(50, $effectiveItemPrice, 0.01);

        $taxAmount = $this->calculateTax($effectiveItemPrice, $taxRate, $priceIncludesTax);
        $this->assertEqualsWithDelta(5, $taxAmount, 0.01);

        $orderAmount = max($rawTotal - $totalDiscountAmount, 0);
        $this->assertEqualsWithDelta(55, $orderAmount, 0.01);
    }

    public function test_tax_calculation_with_100_percent_discount_price_excludes_tax(): void
    {
        $price = 100;
        $taxRate = 10;
        $priceIncludesTax = false;

        $rawTotal = $priceIncludesTax ? $price : $price * (1 + $taxRate / 100);
        $this->assertEqualsWithDelta(110, $rawTotal, 0.01);

        $totalDiscountAmount = 110;
        $discountRatio = $rawTotal > 0 ? max(0, $rawTotal - $totalDiscountAmount) / $rawTotal : 0;
        $this->assertEqualsWithDelta(0, $discountRatio, 0.001);

        $effectiveItemPrice = $price * $discountRatio;
        $this->assertEqualsWithDelta(0, $effectiveItemPrice, 0.01);

        $taxAmount = $this->calculateTax($effectiveItemPrice, $taxRate, $priceIncludesTax);
        $this->assertEqualsWithDelta(0, $taxAmount, 0.01);

        $orderAmount = max($rawTotal - $totalDiscountAmount, 0);
        $this->assertEqualsWithDelta(0, $orderAmount, 0.01);
    }

    public function test_tax_calculation_with_50_percent_discount_price_includes_tax(): void
    {
        $price = 100;
        $taxRate = 10;
        $priceIncludesTax = true;

        $rawTotal = $priceIncludesTax ? $price : $price * (1 + $taxRate / 100);
        $this->assertEquals(100, $rawTotal);

        $totalDiscountAmount = 50;
        $discountRatio = $rawTotal > 0 ? max(0, $rawTotal - $totalDiscountAmount) / $rawTotal : 0;
        $this->assertEquals(0.5, $discountRatio);

        $effectiveItemPrice = $price * $discountRatio;
        $this->assertEquals(50, $effectiveItemPrice);

        $taxAmount = $this->calculateTax($effectiveItemPrice, $taxRate, $priceIncludesTax);
        $this->assertEqualsWithDelta(4.55, $taxAmount, 0.01);

        $orderAmount = max($rawTotal - $totalDiscountAmount, 0);
        $this->assertEquals(50, $orderAmount);
    }

    public function test_tax_calculation_with_100_percent_discount_price_includes_tax(): void
    {
        $price = 100;
        $taxRate = 10;
        $priceIncludesTax = true;

        $rawTotal = $priceIncludesTax ? $price : $price * (1 + $taxRate / 100);
        $this->assertEquals(100, $rawTotal);

        $totalDiscountAmount = 100;
        $discountRatio = $rawTotal > 0 ? max(0, $rawTotal - $totalDiscountAmount) / $rawTotal : 0;
        $this->assertEquals(0, $discountRatio);

        $effectiveItemPrice = $price * $discountRatio;
        $this->assertEquals(0, $effectiveItemPrice);

        $taxAmount = $this->calculateTax($effectiveItemPrice, $taxRate, $priceIncludesTax);
        $this->assertEquals(0, $taxAmount);

        $orderAmount = max($rawTotal - $totalDiscountAmount, 0);
        $this->assertEquals(0, $orderAmount);
    }

    public function test_tax_calculation_without_discount_price_excludes_tax(): void
    {
        $price = 100;
        $taxRate = 10;
        $priceIncludesTax = false;

        $rawTotal = $price * (1 + $taxRate / 100);
        $discountRatio = 1;

        $effectiveItemPrice = $price * $discountRatio;
        $taxAmount = $this->calculateTax($effectiveItemPrice, $taxRate, $priceIncludesTax);

        $this->assertEquals(10, $taxAmount);
    }

    public function test_tax_calculation_without_discount_price_includes_tax(): void
    {
        $price = 100;
        $taxRate = 10;
        $priceIncludesTax = true;

        $discountRatio = 1;

        $effectiveItemPrice = $price * $discountRatio;
        $taxAmount = $this->calculateTax($effectiveItemPrice, $taxRate, $priceIncludesTax);

        $this->assertEqualsWithDelta(9.09, $taxAmount, 0.01);
    }

    public function test_tax_calculation_with_multiple_items_and_50_percent_discount(): void
    {
        $items = [
            ['price' => 100, 'qty' => 2, 'taxRate' => 10, 'priceIncludesTax' => false],
            ['price' => 50, 'qty' => 1, 'taxRate' => 10, 'priceIncludesTax' => false],
        ];

        $rawTotal = 0;
        foreach ($items as $item) {
            $itemTotal = $item['price'] * $item['qty'];
            if (! $item['priceIncludesTax']) {
                $itemTotal *= (1 + $item['taxRate'] / 100);
            }
            $rawTotal += $itemTotal;
        }
        $this->assertEqualsWithDelta(275, $rawTotal, 0.01);

        $totalDiscountAmount = 137.5;
        $discountRatio = $rawTotal > 0 ? max(0, $rawTotal - $totalDiscountAmount) / $rawTotal : 0;
        $this->assertEqualsWithDelta(0.5, $discountRatio, 0.001);

        $totalTax = 0;
        foreach ($items as $item) {
            $itemPrice = $item['price'] * $item['qty'];
            $effectiveItemPrice = $itemPrice * $discountRatio;
            $totalTax += $this->calculateTax($effectiveItemPrice, $item['taxRate'], $item['priceIncludesTax']);
        }

        $this->assertEqualsWithDelta(12.5, $totalTax, 0.01);

        $orderAmount = max($rawTotal - $totalDiscountAmount, 0);
        $this->assertEqualsWithDelta(137.5, $orderAmount, 0.01);
    }

    public function test_discount_greater_than_total_should_not_go_negative(): void
    {
        $price = 100;
        $taxRate = 10;
        $priceIncludesTax = false;

        $rawTotal = $price * (1 + $taxRate / 100);
        $this->assertEqualsWithDelta(110, $rawTotal, 0.01);

        $totalDiscountAmount = 200;
        $discountRatio = $rawTotal > 0 ? max(0, $rawTotal - $totalDiscountAmount) / $rawTotal : 0;
        $this->assertEqualsWithDelta(0, $discountRatio, 0.001);

        $effectiveItemPrice = $price * $discountRatio;
        $taxAmount = $this->calculateTax($effectiveItemPrice, $taxRate, $priceIncludesTax);

        $this->assertEqualsWithDelta(0, $taxAmount, 0.01);

        $orderAmount = max($rawTotal - $totalDiscountAmount, 0);
        $this->assertEquals(0, $orderAmount);
    }

    public function test_zero_subtotal_edge_case(): void
    {
        $price = 0;
        $taxRate = 10;

        $rawTotal = 0;
        $totalDiscountAmount = 0;
        $discountRatio = $rawTotal > 0 ? max(0, $rawTotal - $totalDiscountAmount) / $rawTotal : 0;

        $this->assertEquals(0, $discountRatio);

        $effectiveItemPrice = $price * $discountRatio;
        $taxAmount = $this->calculateTax($effectiveItemPrice, $taxRate, false);

        $this->assertEquals(0, $taxAmount);
    }

    protected function calculateTax(float $effectiveItemPrice, float $taxRate, bool $priceIncludesTax): float
    {
        if ($priceIncludesTax) {
            return $effectiveItemPrice - ($effectiveItemPrice / (1 + $taxRate / 100));
        }

        return ($effectiveItemPrice * $taxRate) / 100;
    }
}
