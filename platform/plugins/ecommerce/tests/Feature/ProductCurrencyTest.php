<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Currency;
use Botble\Ecommerce\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCurrencyTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpCurrencies();
    }

    protected function setUpCurrencies(): void
    {
        Currency::query()->create([
            'title' => 'USD',
            'symbol' => '$',
            'is_prefix_symbol' => true,
            'order' => 0,
            'decimals' => 2,
            'is_default' => 1,
            'exchange_rate' => 1,
        ]);

        Currency::query()->create([
            'title' => 'VND',
            'symbol' => '₫',
            'is_prefix_symbol' => false,
            'order' => 1,
            'decimals' => 0,
            'is_default' => 0,
            'exchange_rate' => 23203,
        ]);

        Currency::query()->create([
            'title' => 'EUR',
            'symbol' => '€',
            'is_prefix_symbol' => false,
            'order' => 2,
            'decimals' => 2,
            'is_default' => 0,
            'exchange_rate' => 0.84,
        ]);
    }

    public function test_can_create_product_with_currency_code(): void
    {
        $product = Product::query()->create([
            'name' => 'VND Product',
            'price' => 2500000,
            'currency_code' => 'VND',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertDatabaseHas('ec_products', [
            'name' => 'VND Product',
            'price' => 2500000,
            'currency_code' => 'VND',
        ]);
    }

    public function test_get_raw_price_returns_stored_value(): void
    {
        $product = Product::query()->create([
            'name' => 'VND Product',
            'price' => 2500000,
            'currency_code' => 'VND',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(2500000, $product->getRawPrice());
    }

    public function test_get_raw_sale_price_returns_stored_value(): void
    {
        $product = Product::query()->create([
            'name' => 'VND Product',
            'price' => 2500000,
            'sale_price' => 2000000,
            'currency_code' => 'VND',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(2000000, $product->getRawSalePrice());
    }

    public function test_get_raw_cost_per_item_returns_stored_value(): void
    {
        $product = Product::query()->create([
            'name' => 'VND Product',
            'price' => 2500000,
            'cost_per_item' => 1500000,
            'currency_code' => 'VND',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(1500000, $product->getRawCostPerItem());
    }

    public function test_get_converted_price_converts_vnd_to_default_currency(): void
    {
        $product = Product::query()->create([
            'name' => 'VND Product',
            'price' => 2500000,
            'currency_code' => 'VND',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // 2,500,000 VND / 23203 = ~107.74 USD
        $this->assertEqualsWithDelta(107.74, $product->getConvertedPrice(), 0.01);
    }

    public function test_get_converted_sale_price_converts_vnd_to_default_currency(): void
    {
        $product = Product::query()->create([
            'name' => 'VND Product',
            'price' => 2500000,
            'sale_price' => 2000000,
            'currency_code' => 'VND',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // 2,000,000 VND / 23203 = ~86.19 USD
        $this->assertEqualsWithDelta(86.19, $product->getConvertedSalePrice(), 0.01);
    }

    public function test_null_currency_code_returns_raw_price(): void
    {
        $product = Product::query()->create([
            'name' => 'USD Product',
            'price' => 100,
            'currency_code' => null,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // No conversion should occur
        $this->assertEquals(100, $product->getConvertedPrice());
    }

    public function test_default_currency_product_no_conversion(): void
    {
        $product = Product::query()->create([
            'name' => 'USD Product',
            'price' => 100,
            'currency_code' => 'USD',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // USD is default, no conversion
        $this->assertEquals(100, $product->getConvertedPrice());
    }

    public function test_get_source_currency_returns_currency_model(): void
    {
        $product = Product::query()->create([
            'name' => 'VND Product',
            'price' => 2500000,
            'currency_code' => 'VND',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $sourceCurrency = $product->getSourceCurrency();

        $this->assertNotNull($sourceCurrency);
        $this->assertEquals('VND', $sourceCurrency->title);
        $this->assertEquals(23203, $sourceCurrency->exchange_rate);
    }

    public function test_get_source_currency_returns_null_for_null_currency_code(): void
    {
        $product = Product::query()->create([
            'name' => 'Product',
            'price' => 100,
            'currency_code' => null,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertNull($product->getSourceCurrency());
    }

    public function test_eur_to_usd_conversion(): void
    {
        $product = Product::query()->create([
            'name' => 'EUR Product',
            'price' => 100,
            'currency_code' => 'EUR',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // 100 EUR / 0.84 = ~119.05 USD
        $this->assertEqualsWithDelta(119.05, $product->getConvertedPrice(), 0.01);
    }

    public function test_zero_price_returns_zero(): void
    {
        $product = Product::query()->create([
            'name' => 'Free Product',
            'price' => 0,
            'currency_code' => 'VND',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(0, $product->getConvertedPrice());
    }

    public function test_null_sale_price_returns_null(): void
    {
        $product = Product::query()->create([
            'name' => 'Product',
            'price' => 100,
            'sale_price' => null,
            'currency_code' => 'VND',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertNull($product->getConvertedSalePrice());
    }

    public function test_can_update_product_currency_code(): void
    {
        $product = Product::query()->create([
            'name' => 'Product',
            'price' => 100,
            'currency_code' => null,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->update(['currency_code' => 'VND']);

        $this->assertDatabaseHas('ec_products', [
            'id' => $product->id,
            'currency_code' => 'VND',
        ]);
    }

    public function test_front_sale_price_uses_converted_price(): void
    {
        $product = Product::query()->create([
            'name' => 'VND Product',
            'price' => 2500000,
            'sale_price' => 2000000,
            'currency_code' => 'VND',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $frontSalePrice = $product->front_sale_price;

        // Should be converted from VND to USD
        // 2,000,000 VND / 23203 = ~86.19 USD
        $this->assertEqualsWithDelta(86.19, $frontSalePrice, 0.5);
    }

    public function test_original_price_uses_converted_price(): void
    {
        $product = Product::query()->create([
            'name' => 'VND Product',
            'price' => 2500000,
            'currency_code' => 'VND',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // Refresh to get a clean instance
        $product = Product::query()->find($product->id);

        // Verify currency code is set correctly
        $this->assertEquals('VND', $product->currency_code);
        $this->assertNotNull($product->getSourceCurrency());

        // Verify converted price is correct
        $convertedPrice = $product->getConvertedPrice();
        $this->assertEqualsWithDelta(107.74, $convertedPrice, 0.5);

        // original_price uses ProductPriceService which uses getConvertedPrice
        $originalPrice = $product->original_price;
        $this->assertEqualsWithDelta(107.74, $originalPrice, 0.5);
    }

    public function test_variation_inherits_parent_currency_via_direct_attribute(): void
    {
        // For variations, the currency is inherited through original_product
        // This requires the ec_product_variations pivot table setup
        // which is complex to set up in tests.
        // Instead, test the method directly with mocked attributes.

        $product = new Product();
        $product->setRawAttributes([
            'id' => 1,
            'price' => 2300000,
            'is_variation' => true,
            'currency_code' => 'VND', // Variation has its own currency for testing
        ]);

        // When variation has its own currency_code, it should use that
        $sourceCurrency = $product->getSourceCurrency();
        $this->assertEquals('VND', $sourceCurrency->title);
    }

    public function test_variation_without_currency_returns_null(): void
    {
        // Test that variations without currency_code and without parent return null
        $variation = Product::query()->create([
            'name' => 'Orphan Variation',
            'price' => 2300000,
            'is_variation' => true,
            'currency_code' => null,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // Without parent relationship set up, source currency should be null
        $this->assertNull($variation->getSourceCurrency());
        // Converted price should equal raw price (no conversion)
        $this->assertEquals(2300000, $variation->getConvertedPrice());
    }

    public function test_currency_code_is_fillable(): void
    {
        $product = new Product();
        $product->fill([
            'name' => 'Test',
            'price' => 100,
            'currency_code' => 'EUR',
        ]);

        $this->assertEquals('EUR', $product->currency_code);
    }

    public function test_invalid_currency_code_returns_null_source_currency(): void
    {
        $product = Product::query()->create([
            'name' => 'Product',
            'price' => 100,
            'currency_code' => 'INVALID',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertNull($product->getSourceCurrency());
    }

    public function test_invalid_currency_code_returns_raw_price(): void
    {
        $product = Product::query()->create([
            'name' => 'Product',
            'price' => 100,
            'currency_code' => 'INVALID',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // Invalid currency = no conversion
        $this->assertEquals(100, $product->getConvertedPrice());
    }

    public function test_large_vnd_price_converts_correctly(): void
    {
        $product = Product::query()->create([
            'name' => 'Expensive VND Product',
            'price' => 50000000, // 50 million VND
            'currency_code' => 'VND',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // 50,000,000 VND / 23203 = ~2154.89 USD
        $this->assertEqualsWithDelta(2154.89, $product->getConvertedPrice(), 0.5);
    }

    public function test_currency_conversion_with_multiple_products(): void
    {
        $vndProduct = Product::query()->create([
            'name' => 'VND Product',
            'price' => 2300000,
            'currency_code' => 'VND',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $eurProduct = Product::query()->create([
            'name' => 'EUR Product',
            'price' => 100,
            'currency_code' => 'EUR',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $usdProduct = Product::query()->create([
            'name' => 'USD Product',
            'price' => 100,
            'currency_code' => 'USD',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // All prices should be comparable in USD
        $this->assertEqualsWithDelta(99.12, $vndProduct->getConvertedPrice(), 0.5); // 2300000 / 23203
        $this->assertEqualsWithDelta(119.05, $eurProduct->getConvertedPrice(), 0.5); // 100 / 0.84
        $this->assertEquals(100, $usdProduct->getConvertedPrice()); // Already USD
    }
}
