<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductPriceTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_hide_product_price_when_zero_setting(): void
    {
        // Ensure cart is enabled so price visibility depends on other settings
        Setting::set('ecommerce_shopping_cart_enabled', 1);
        Setting::set('ecommerce_hide_product_price', 0);
        Setting::save();

        // Create a product with price 0
        $productZeroPrice = Product::query()->create([
            'name' => 'Free Product',
            'price' => 0,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // Create a product with price > 0
        $productWithPrice = Product::query()->create([
            'name' => 'Paid Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // Case 1: Setting is disabled (default)
        Setting::set('ecommerce_hide_product_price_when_zero', 0);
        Setting::save();

        $this->assertFalse(EcommerceHelper::hideProductPriceWhenZero());

        // Render the view for zero price product
        $view = $this->blade(
            '@include(EcommerceHelper::viewPath("includes.product-price"))',
            ['product' => $productZeroPrice]
        );

        // Price should be visible
        $view->assertSee('bb-product-price');

        // Case 2: Setting is enabled
        Setting::set('ecommerce_hide_product_price_when_zero', 1);
        Setting::save();

        $this->assertTrue(EcommerceHelper::hideProductPriceWhenZero());

        // Render the view for zero price product
        $view = $this->blade(
            '@include(EcommerceHelper::viewPath("includes.product-price"))',
            ['product' => $productZeroPrice]
        );

        // Price should NOT be visible
        $view->assertDontSee('bb-product-price');

        // Render the view for paid product
        $view = $this->blade(
            '@include(EcommerceHelper::viewPath("includes.product-price"))',
            ['product' => $productWithPrice]
        );

        // Price should be visible
        $view->assertSee('bb-product-price');
    }
}
