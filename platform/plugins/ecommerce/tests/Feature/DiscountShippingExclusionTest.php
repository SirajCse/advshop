<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\DiscountTargetEnum;
use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Services\HandleApplyCouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DiscountShippingExclusionTest extends BaseTestCase
{
    use RefreshDatabase;

    protected HandleApplyCouponService $couponService;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::instance('cart')->destroy();
        $this->couponService = app(HandleApplyCouponService::class);
    }

    protected function tearDown(): void
    {
        Cart::instance('cart')->destroy();
        session()->forget(['applied_coupon_code', 'auto_apply_coupon_code']);

        parent::tearDown();
    }

    protected function createProduct(array $attributes = []): Product
    {
        return Product::query()->create(array_merge([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
            'is_variation' => false,
        ], $attributes));
    }

    protected function addProductToCart(Product $product, int $qty = 1): void
    {
        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            $qty,
            $product->price,
            ['product' => $product]
        );
    }

    // ========================================
    // PERCENTAGE DISCOUNT: SHIPPING EXCLUSION
    // ========================================

    public function test_percentage_minimum_order_discount_excludes_shipping(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 2);

        $discount = Discount::query()->create([
            'code' => 'PERCENT10',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::MINIMUM_ORDER_AMOUNT,
            'value' => 10,
            'min_order_price' => 50,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        // Apply without shipping in session
        $resultWithoutShipping = $this->couponService->execute('PERCENT10');
        $this->assertFalse($resultWithoutShipping['error']);
        $discountWithoutShipping = $resultWithoutShipping['data']['discount_amount'];

        Cart::instance('cart')->destroy();
        $this->addProductToCart($product, 2);

        // Apply with shipping in session — discount should be the same
        $resultWithShipping = $this->couponService->execute('PERCENT10', ['shipping_amount' => 15]);
        $this->assertFalse($resultWithShipping['error']);
        $discountWithShipping = $resultWithShipping['data']['discount_amount'];

        // Both should be 10% of $200 = $20, shipping must NOT reduce the discount
        $this->assertEquals(20, $discountWithoutShipping);
        $this->assertEquals(20, $discountWithShipping);
        $this->assertEquals($discountWithoutShipping, $discountWithShipping);
    }

    public function test_percentage_minimum_order_discount_consistent_with_all_orders_target(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 1);

        $minOrderDiscount = Discount::query()->create([
            'code' => 'MINORDER15',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::MINIMUM_ORDER_AMOUNT,
            'value' => 15,
            'min_order_price' => 50,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $allOrdersDiscount = Discount::query()->create([
            'code' => 'ALLORDERS15',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 15,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $sessionData = ['shipping_amount' => 20];

        $minOrderResult = $this->couponService->execute('MINORDER15', $sessionData);
        $this->assertFalse($minOrderResult['error']);

        Cart::instance('cart')->destroy();
        $this->addProductToCart($product, 1);

        $allOrdersResult = $this->couponService->execute('ALLORDERS15', $sessionData);
        $this->assertFalse($allOrdersResult['error']);

        // Both targets should yield the same discount for the same cart
        $this->assertEquals(
            $allOrdersResult['data']['discount_amount'],
            $minOrderResult['data']['discount_amount']
        );
    }

    // ========================================
    // getCouponDiscountAmount: DIRECT TESTS
    // ========================================

    public function test_get_coupon_discount_amount_percentage_minimum_order_ignores_shipping(): void
    {
        $product = $this->createProduct(['price' => 200]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'DIRECT10',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::MINIMUM_ORDER_AMOUNT,
            'value' => 10,
            'min_order_price' => 50,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $cartData = ['rawTotal' => 200];

        // With zero shipping
        $resultNoShipping = $this->couponService->getCouponDiscountAmount($discount, $cartData, ['shipping_amount' => 0]);
        $this->assertEquals(20, $resultNoShipping['discount_amount']);

        // With shipping — discount must remain unchanged
        $resultWithShipping = $this->couponService->getCouponDiscountAmount($discount, $cartData, ['shipping_amount' => 30]);
        $this->assertEquals(20, $resultWithShipping['discount_amount']);
    }

    public function test_get_coupon_discount_amount_simulates_process_order_context(): void
    {
        $product = $this->createProduct(['price' => 150]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'PROCESSORDER',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::MINIMUM_ORDER_AMOUNT,
            'value' => 20,
            'min_order_price' => 100,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        // Simulate checkout context: rawTotal from Cart::rawTotal() (products only)
        $checkoutCartData = ['rawTotal' => 150];
        $checkoutSessionData = ['shipping_amount' => 0];
        $checkoutResult = $this->couponService->getCouponDiscountAmount($discount, $checkoutCartData, $checkoutSessionData);

        // Simulate processOrder context: rawTotal from $order->sub_total, shipping from $order->shipping_amount
        $processOrderCartData = ['rawTotal' => 150];
        $processOrderSessionData = ['shipping_amount' => 25];
        $processOrderResult = $this->couponService->getCouponDiscountAmount($discount, $processOrderCartData, $processOrderSessionData);

        // Both contexts must produce the same discount (20% of $150 = $30)
        $this->assertEquals(30, $checkoutResult['discount_amount']);
        $this->assertEquals(30, $processOrderResult['discount_amount']);
        $this->assertEquals($checkoutResult['discount_amount'], $processOrderResult['discount_amount']);
    }

    // ========================================
    // ORDER MODEL: discount_amount_format
    // ========================================

    public function test_order_discount_amount_format_returns_discount_not_shipping(): void
    {
        $order = Order::query()->create([
            'amount' => 85,
            'sub_total' => 100,
            'shipping_amount' => 15,
            'discount_amount' => 30,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        // discount_amount_format must reflect discount_amount, not shipping_amount
        $this->assertEquals(format_price(30), $order->discount_amount_format);
        $this->assertNotEquals(format_price(15), $order->discount_amount_format);
    }

    public function test_order_discount_amount_format_with_zero_discount(): void
    {
        $order = Order::query()->create([
            'amount' => 110,
            'sub_total' => 100,
            'shipping_amount' => 10,
            'discount_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        $this->assertEquals(format_price(0), $order->discount_amount_format);
    }

    // ========================================
    // EDGE CASES
    // ========================================

    public function test_percentage_discount_with_high_shipping_does_not_go_negative(): void
    {
        $product = $this->createProduct(['price' => 50]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'HIGHSHIP',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::MINIMUM_ORDER_AMOUNT,
            'value' => 10,
            'min_order_price' => 30,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        // Shipping higher than product price — must not affect percentage calculation
        $cartData = ['rawTotal' => 50];
        $sessionData = ['shipping_amount' => 100];

        $result = $this->couponService->getCouponDiscountAmount($discount, $cartData, $sessionData);

        // 10% of $50 = $5, regardless of shipping
        $this->assertEquals(5, $result['discount_amount']);
    }

    public function test_fixed_amount_minimum_order_discount_unaffected_by_shipping(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'FIXEDMIN',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::MINIMUM_ORDER_AMOUNT,
            'value' => 15,
            'min_order_price' => 50,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $cartData = ['rawTotal' => 100];

        $resultNoShipping = $this->couponService->getCouponDiscountAmount($discount, $cartData, ['shipping_amount' => 0]);
        $resultWithShipping = $this->couponService->getCouponDiscountAmount($discount, $cartData, ['shipping_amount' => 20]);

        $this->assertEquals(15, $resultNoShipping['discount_amount']);
        $this->assertEquals(15, $resultWithShipping['discount_amount']);
    }
}
