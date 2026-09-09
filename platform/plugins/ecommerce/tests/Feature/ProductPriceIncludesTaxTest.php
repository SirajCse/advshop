<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\Tax;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductPriceIncludesTaxTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setSetting('ecommerce_tax_enabled', 1);
        $this->setSetting('ecommerce_shopping_cart_enabled', 1);
    }

    protected function setSetting(string $key, mixed $value): void
    {
        Setting::load(true);
        Setting::set($key, $value);
        Setting::save();
        Setting::load(true);
    }

    protected function createTax(float $percentage = 10): Tax
    {
        return Tax::query()->create([
            'title' => "Tax {$percentage}%",
            'percentage' => $percentage,
            'priority' => 0,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    protected function createProductWithTax(
        float $price,
        bool $priceIncludesTax,
        Tax $tax,
        bool $isVariation = false
    ): Product {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => $price,
            'status' => BaseStatusEnum::PUBLISHED,
            'price_includes_tax' => $priceIncludesTax,
            'is_variation' => $isVariation,
            'quantity' => 100,
            'with_storehouse_management' => false,
        ]);

        $product->taxes()->attach($tax->id);

        return $product;
    }

    public function test_product_price_with_tax_included_does_not_add_additional_tax(): void
    {
        $tax = $this->createTax(10);
        $product = $this->createProductWithTax(500, true, $tax);

        $this->setSetting('ecommerce_display_product_price_including_taxes', 1);

        $product->refresh();

        $this->assertTrue($product->price_includes_tax);
        $this->assertEquals(10, $product->total_taxes_percentage);

        $this->assertEquals(500, $product->price_with_taxes);
        $this->assertEquals(500, $product->front_sale_price_with_taxes);
        $this->assertEquals(500, $product->price()->getPrice());
    }

    public function test_product_price_without_tax_included_adds_tax_when_setting_enabled(): void
    {
        $tax = $this->createTax(10);
        $product = $this->createProductWithTax(500, false, $tax);

        $this->setSetting('ecommerce_display_product_price_including_taxes', 1);

        $product->refresh();

        $this->assertFalse($product->price_includes_tax);
        $this->assertEquals(10, $product->total_taxes_percentage);

        $this->assertEquals(550, $product->price_with_taxes);
        $this->assertEquals(550, $product->front_sale_price_with_taxes);
        $this->assertEquals(550, $product->price()->getPrice());
    }

    public function test_product_price_without_tax_included_shows_base_price_when_setting_disabled(): void
    {
        $tax = $this->createTax(10);
        $product = $this->createProductWithTax(500, false, $tax);

        $this->setSetting('ecommerce_display_product_price_including_taxes', 0);

        $product->refresh();

        $this->assertFalse($product->price_includes_tax);

        $this->assertEquals(500, $product->price_with_taxes);
        $this->assertEquals(500, $product->front_sale_price_with_taxes);
        $this->assertEquals(500, $product->price()->getPrice());
    }

    public function test_variation_uses_own_price_includes_tax_setting(): void
    {
        $tax = $this->createTax(10);

        $parentProduct = $this->createProductWithTax(500, false, $tax);

        $variationProduct = Product::query()->create([
            'name' => 'Variation Product',
            'price' => 450,
            'status' => BaseStatusEnum::PUBLISHED,
            'is_variation' => true,
            'price_includes_tax' => true,
            'quantity' => 100,
            'with_storehouse_management' => false,
        ]);

        ProductVariation::query()->create([
            'product_id' => $variationProduct->id,
            'configurable_product_id' => $parentProduct->id,
            'is_default' => true,
        ]);

        $this->setSetting('ecommerce_display_product_price_including_taxes', 1);

        $variationProduct->refresh();

        $this->assertTrue($variationProduct->is_variation);
        $this->assertTrue($variationProduct->price_includes_tax);

        $this->assertEquals(450, $variationProduct->price_with_taxes);
        $this->assertEquals(450, $variationProduct->front_sale_price_with_taxes);
    }

    public function test_variation_adds_tax_when_own_price_excludes_tax(): void
    {
        $tax = $this->createTax(10);

        $parentProduct = $this->createProductWithTax(500, true, $tax);

        $variationProduct = Product::query()->create([
            'name' => 'Variation Product',
            'price' => 450,
            'status' => BaseStatusEnum::PUBLISHED,
            'is_variation' => true,
            'price_includes_tax' => false,
            'quantity' => 100,
            'with_storehouse_management' => false,
        ]);

        ProductVariation::query()->create([
            'product_id' => $variationProduct->id,
            'configurable_product_id' => $parentProduct->id,
            'is_default' => true,
        ]);

        $this->setSetting('ecommerce_display_product_price_including_taxes', 1);

        $variationProduct->refresh();

        $this->assertTrue($variationProduct->is_variation);
        $this->assertFalse($variationProduct->price_includes_tax);

        $this->assertEquals(495, $variationProduct->price_with_taxes);
        $this->assertEquals(495, $variationProduct->front_sale_price_with_taxes);
    }

    public function test_cart_item_tax_calculation_with_price_includes_tax(): void
    {
        $tax = $this->createTax(10);
        $product = $this->createProductWithTax(110, true, $tax);

        $this->setSetting('ecommerce_display_product_price_including_taxes', 1);

        $product->refresh();

        Cart::instance('cart')->destroy();

        OrderHelper::handleAddCart($product, request()->merge(['qty' => 1]));

        $cartContent = Cart::instance('cart')->content();
        $cartItem = $cartContent->first();

        $this->assertEquals(110, $cartItem->price);
        $this->assertTrue($cartItem->options->get('price_includes_tax'));

        $expectedTax = 110 - (110 / 1.10);
        $this->assertEqualsWithDelta($expectedTax, $cartItem->tax, 0.01);

        $this->assertEquals(110, $cartItem->priceTax);
        $this->assertEqualsWithDelta($expectedTax, $cartItem->taxTotal, 0.01);
        $this->assertEquals(110, $cartItem->total);
    }

    public function test_cart_item_tax_calculation_with_price_excludes_tax(): void
    {
        $tax = $this->createTax(10);
        $product = $this->createProductWithTax(100, false, $tax);

        $this->setSetting('ecommerce_display_product_price_including_taxes', 1);

        $product->refresh();

        Cart::instance('cart')->destroy();

        OrderHelper::handleAddCart($product, request()->merge(['qty' => 1]));

        $cartContent = Cart::instance('cart')->content();
        $cartItem = $cartContent->first();

        $this->assertEquals(100, $cartItem->price);
        $this->assertFalse($cartItem->options->get('price_includes_tax'));

        $this->assertEquals(10, $cartItem->tax);
        $this->assertEquals(110, $cartItem->priceTax);
        $this->assertEquals(10, $cartItem->taxTotal);
        $this->assertEquals(110, $cartItem->total);
    }

    public function test_cart_raw_tax_with_discount_price_includes_tax(): void
    {
        $tax = $this->createTax(10);
        $product = $this->createProductWithTax(110, true, $tax);

        $this->setSetting('ecommerce_display_product_price_including_taxes', 1);

        $product->refresh();

        Cart::instance('cart')->destroy();

        OrderHelper::handleAddCart($product, request()->merge(['qty' => 1]));

        $rawTotal = Cart::instance('cart')->rawTotal();
        $this->assertEquals(110, $rawTotal);

        $discountAmount = 55;

        $taxWithDiscount = Cart::instance('cart')->rawTax($discountAmount);

        $expectedTax = 55 - (55 / 1.10);
        $this->assertEqualsWithDelta($expectedTax, $taxWithDiscount, 0.01);
    }

    public function test_cart_raw_tax_with_discount_price_excludes_tax(): void
    {
        $tax = $this->createTax(10);
        $product = $this->createProductWithTax(100, false, $tax);

        $this->setSetting('ecommerce_display_product_price_including_taxes', 1);

        $product->refresh();

        Cart::instance('cart')->destroy();

        OrderHelper::handleAddCart($product, request()->merge(['qty' => 1]));

        $rawTotal = Cart::instance('cart')->rawTotal();
        $this->assertEquals(110, $rawTotal);

        $discountAmount = 55;

        $taxWithDiscount = Cart::instance('cart')->rawTax($discountAmount);

        $expectedTax = 50 * 0.10;
        $this->assertEqualsWithDelta($expectedTax, $taxWithDiscount, 0.01);
    }

    public function test_product_with_sale_price_and_tax_included(): void
    {
        $tax = $this->createTax(10);

        $product = Product::query()->create([
            'name' => 'Sale Product',
            'price' => 500,
            'sale_price' => 400,
            'sale_type' => 0,
            'status' => BaseStatusEnum::PUBLISHED,
            'price_includes_tax' => true,
            'quantity' => 100,
            'with_storehouse_management' => false,
        ]);
        $product->taxes()->attach($tax->id);

        $this->setSetting('ecommerce_display_product_price_including_taxes', 1);

        $product->refresh();

        $this->assertEquals(400, $product->front_sale_price);
        $this->assertEquals(400, $product->front_sale_price_with_taxes);
        $this->assertEquals(400, $product->price()->getPrice());
    }

    public function test_product_with_sale_price_and_tax_excluded(): void
    {
        $tax = $this->createTax(10);

        $product = Product::query()->create([
            'name' => 'Sale Product',
            'price' => 500,
            'sale_price' => 400,
            'sale_type' => 0,
            'status' => BaseStatusEnum::PUBLISHED,
            'price_includes_tax' => false,
            'quantity' => 100,
            'with_storehouse_management' => false,
        ]);
        $product->taxes()->attach($tax->id);

        $this->setSetting('ecommerce_display_product_price_including_taxes', 1);

        $product->refresh();

        $this->assertEquals(400, $product->front_sale_price);
        $this->assertEquals(440, $product->front_sale_price_with_taxes);
        $this->assertEquals(440, $product->price()->getPrice());
    }

    public function test_multiple_cart_items_with_mixed_tax_settings(): void
    {
        $tax = $this->createTax(10);

        $productIncludesTax = $this->createProductWithTax(110, true, $tax);
        $productExcludesTax = $this->createProductWithTax(100, false, $tax);

        $this->setSetting('ecommerce_display_product_price_including_taxes', 1);

        $productIncludesTax->refresh();
        $productExcludesTax->refresh();

        Cart::instance('cart')->destroy();

        OrderHelper::handleAddCart($productIncludesTax, request()->merge(['qty' => 1]));
        OrderHelper::handleAddCart($productExcludesTax, request()->merge(['qty' => 1]));

        $rawTotal = Cart::instance('cart')->rawTotal();

        $this->assertEquals(220, $rawTotal);

        $totalTax = Cart::instance('cart')->rawTax();

        $taxFromIncluded = 110 - (110 / 1.10);
        $taxFromExcluded = 100 * 0.10;

        $this->assertEqualsWithDelta($taxFromIncluded + $taxFromExcluded, $totalTax, 0.02);
    }

    protected function tearDown(): void
    {
        Cart::instance('cart')->destroy();

        parent::tearDown();
    }
}
