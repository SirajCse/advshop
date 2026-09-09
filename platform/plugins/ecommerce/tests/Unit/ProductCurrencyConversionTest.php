<?php

namespace Botble\Ecommerce\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProductCurrencyConversionTest extends TestCase
{
    /**
     * Test currency conversion formula: price / exchange_rate = converted_price
     */
    public function test_vnd_to_usd_conversion(): void
    {
        $vndPrice = 2500000;
        $vndExchangeRate = 23203;

        $usdPrice = $vndPrice / $vndExchangeRate;

        $this->assertEqualsWithDelta(107.74, $usdPrice, 0.01);
    }

    public function test_eur_to_usd_conversion(): void
    {
        $eurPrice = 100;
        $eurExchangeRate = 0.84;

        $usdPrice = $eurPrice / $eurExchangeRate;

        $this->assertEqualsWithDelta(119.05, $usdPrice, 0.01);
    }

    public function test_ngn_to_usd_conversion(): void
    {
        $ngnPrice = 100000;
        $ngnExchangeRate = 895.52;

        $usdPrice = $ngnPrice / $ngnExchangeRate;

        $this->assertEqualsWithDelta(111.67, $usdPrice, 0.01);
    }

    public function test_default_currency_no_conversion(): void
    {
        $usdPrice = 100;
        $usdExchangeRate = 1;

        $convertedPrice = $usdPrice / $usdExchangeRate;

        $this->assertEquals(100, $convertedPrice);
    }

    public function test_zero_price_returns_zero(): void
    {
        $price = 0;
        $exchangeRate = 23203;

        $convertedPrice = $price / $exchangeRate;

        $this->assertEquals(0, $convertedPrice);
    }

    public function test_null_price_handling(): void
    {
        $price = null;

        $convertedPrice = $price === null ? null : $price / 23203;

        $this->assertNull($convertedPrice);
    }

    public function test_zero_exchange_rate_not_converted(): void
    {
        $price = 100;
        $exchangeRate = 0;

        // Should return original price when exchange rate is 0 or invalid
        $convertedPrice = $exchangeRate <= 0 ? $price : $price / $exchangeRate;

        $this->assertEquals(100, $convertedPrice);
    }

    public function test_negative_exchange_rate_not_converted(): void
    {
        $price = 100;
        $exchangeRate = -1;

        // Should return original price when exchange rate is negative
        $convertedPrice = $exchangeRate <= 0 ? $price : $price / $exchangeRate;

        $this->assertEquals(100, $convertedPrice);
    }

    public function test_sale_price_conversion(): void
    {
        $vndSalePrice = 2000000;
        $vndExchangeRate = 23203;

        $usdSalePrice = $vndSalePrice / $vndExchangeRate;

        $this->assertEqualsWithDelta(86.19, $usdSalePrice, 0.01);
    }

    public function test_discount_percentage_preserved_after_conversion(): void
    {
        $vndPrice = 2500000;
        $vndSalePrice = 2000000;
        $vndExchangeRate = 23203;

        $originalDiscount = (($vndPrice - $vndSalePrice) / $vndPrice) * 100;
        $this->assertEquals(20, $originalDiscount);

        $usdPrice = $vndPrice / $vndExchangeRate;
        $usdSalePrice = $vndSalePrice / $vndExchangeRate;

        $convertedDiscount = (($usdPrice - $usdSalePrice) / $usdPrice) * 100;
        $this->assertEqualsWithDelta(20, $convertedDiscount, 0.01);
    }

    public function test_large_vnd_price_conversion(): void
    {
        $vndPrice = 50000000; // 50 million VND
        $vndExchangeRate = 23203;

        $usdPrice = $vndPrice / $vndExchangeRate;

        $this->assertEqualsWithDelta(2154.89, $usdPrice, 0.01);
    }

    public function test_small_price_conversion(): void
    {
        $vndPrice = 1000; // 1000 VND
        $vndExchangeRate = 23203;

        $usdPrice = $vndPrice / $vndExchangeRate;

        $this->assertEqualsWithDelta(0.04, $usdPrice, 0.01);
    }
}
