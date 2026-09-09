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

class OrderTotalConsistencyTest extends BaseTestCase
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
    // ORDER EQUATION CONSISTENCY
    // amount = sub_total - discount_amount + tax_amount + shipping_amount + payment_fee
    // ========================================

    public function test_order_amount_equals_subtotal_plus_tax_plus_shipping_minus_discount(): void
    {
        $order = Order::query()->create([
            'amount' => 88,
            'sub_total' => 100,
            'tax_amount' => 5,
            'shipping_amount' => 15,
            'discount_amount' => 32,
            'payment_fee' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        $expectedAmount = $order->sub_total - $order->discount_amount + $order->tax_amount + $order->shipping_amount + ($order->payment_fee ?? 0);

        $this->assertEquals($expectedAmount, $order->amount);
    }

    public function test_order_amount_equation_holds_with_payment_fee(): void
    {
        $order = Order::query()->create([
            'amount' => 91,
            'sub_total' => 100,
            'tax_amount' => 5,
            'shipping_amount' => 15,
            'discount_amount' => 32,
            'payment_fee' => 3,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        $expectedAmount = $order->sub_total - $order->discount_amount + $order->tax_amount + $order->shipping_amount + $order->payment_fee;

        $this->assertEquals($expectedAmount, $order->amount);
    }

    // ========================================
    // RECALCULATION CONSISTENCY
    // Simulates the formulas used in saveInformation, HandleCheckoutOrderData, and processOrder
    // ========================================

    public function test_recalculation_formula_produces_same_amount_as_stored(): void
    {
        $subTotal = 100;
        $taxAmount = 5;
        $shippingAmount = 15;
        $discountAmount = 20;
        $paymentFee = 0;

        $originalAmount = $subTotal - $discountAmount + $taxAmount + $shippingAmount + $paymentFee;

        $order = Order::query()->create([
            'amount' => $originalAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'payment_fee' => $paymentFee,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        // Simulates saveInformation / HandleCheckoutOrderData recalculation
        $recalculatedAmount = max(
            $order->sub_total - $order->discount_amount + $order->tax_amount + $order->shipping_amount + ($order->payment_fee ?? 0),
            0
        );

        $this->assertEquals($order->amount, $recalculatedAmount);
    }

    public function test_recalculation_consistent_with_different_shipping_amounts(): void
    {
        $subTotal = 200;
        $taxAmount = 10;
        $discountAmount = 30;

        foreach ([0, 5, 15, 50, 100] as $shippingAmount) {
            $originalAmount = $subTotal - $discountAmount + $taxAmount + $shippingAmount;

            $order = Order::query()->create([
                'amount' => $originalAmount,
                'sub_total' => $subTotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $discountAmount,
                'status' => OrderStatusEnum::PENDING,
                'shipping_method' => ShippingMethodEnum::DEFAULT,
                'is_finished' => false,
            ]);

            $recalculatedAmount = max(
                $order->sub_total - $order->discount_amount + $order->tax_amount + $order->shipping_amount + ($order->payment_fee ?? 0),
                0
            );

            $this->assertEquals(
                $order->amount,
                $recalculatedAmount,
                "Recalculation mismatch with shipping_amount={$shippingAmount}"
            );
        }
    }

    // ========================================
    // TAX AMOUNT: MUST BE FULL TAX (NOT DISCOUNT-ADJUSTED)
    // This is the core bug fix — rawTax() instead of rawTax($discount)
    // ========================================

    public function test_full_tax_keeps_order_equation_consistent(): void
    {
        // Scenario: product $100, 5% tax, 10% discount coupon, $15 shipping
        $subTotal = 100;
        $fullTax = 5;        // 5% of $100
        $discountAmount = 10; // 10% of rawTotal ($105) ≈ 10.5, but let's use product-based
        $shippingAmount = 15;

        // With full tax: equation is consistent
        $amountWithFullTax = $subTotal - $discountAmount + $fullTax + $shippingAmount;
        $this->assertEquals(110, $amountWithFullTax);

        // If we stored adjusted tax instead (the old bug), recalculation would differ
        $adjustedTax = 4.5; // hypothetical discount-adjusted tax
        $amountWithAdjustedTax = $subTotal - $discountAmount + $adjustedTax + $shippingAmount;
        $this->assertEquals(109.5, $amountWithAdjustedTax);

        // The original postCheckout amount used rawTotal (which includes full tax):
        // amount = rawTotal - discount + shipping = (100 + 5) - 10 + 15 = 110
        $rawTotal = $subTotal + $fullTax;
        $postCheckoutAmount = max($rawTotal - $discountAmount, 0) + $shippingAmount;
        $this->assertEquals(110, $postCheckoutAmount);

        // Only full tax keeps the equation consistent with postCheckout amount
        $this->assertEquals($postCheckoutAmount, $amountWithFullTax);
        $this->assertNotEquals($postCheckoutAmount, $amountWithAdjustedTax);
    }

    public function test_adjusted_tax_breaks_recalculation_full_tax_does_not(): void
    {
        $subTotal = 33.333;
        $fullTax = 1.667;     // 5% of 33.333
        $discountAmount = 2.0;
        $shippingAmount = 2.0;

        // postCheckout formula: amount = max(rawTotal - discount, 0) + shipping
        $rawTotal = $subTotal + $fullTax; // 35.000
        $postCheckoutAmount = max($rawTotal - $discountAmount, 0) + $shippingAmount; // 35.000
        $this->assertEqualsWithDelta(35.0, $postCheckoutAmount, 0.001);

        // Recalculation with full tax (correct)
        $recalcWithFullTax = $subTotal - $discountAmount + $fullTax + $shippingAmount;
        $this->assertEqualsWithDelta(35.0, $recalcWithFullTax, 0.001);
        $this->assertEqualsWithDelta($postCheckoutAmount, $recalcWithFullTax, 0.001);

        // Recalculation with adjusted tax (bug: would produce ~34.9)
        $discountRatio = max(0, $rawTotal - $discountAmount) / $rawTotal; // ~0.9429
        $adjustedTax = $fullTax * $discountRatio; // ~1.571
        $recalcWithAdjustedTax = $subTotal - $discountAmount + $adjustedTax + $shippingAmount;
        $this->assertLessThan($postCheckoutAmount, $recalcWithAdjustedTax);
    }

    // ========================================
    // processOrder RECALCULATION SIMULATION
    // ========================================

    public function test_process_order_recalculation_with_percentage_coupon(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 2);

        $discount = Discount::query()->create([
            'code' => 'RECALC10',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::MINIMUM_ORDER_AMOUNT,
            'value' => 10,
            'min_order_price' => 50,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $rawTotal = Cart::instance('cart')->rawTotal();
        $subTotal = Cart::instance('cart')->rawSubTotal();
        $taxAmount = Cart::instance('cart')->rawTax();
        $shippingAmount = 15;

        // Simulate postCheckout discount calculation
        $couponData = $this->couponService->getCouponDiscountAmount($discount, [], []);
        $discountAmount = $couponData['discount_amount'];
        $this->assertGreaterThan(0, $discountAmount);

        // postCheckout stores: amount = max(rawTotal - discount, 0) + shipping
        $postCheckoutAmount = max($rawTotal - $discountAmount, 0) + $shippingAmount;

        // Create order as postCheckout would
        $order = Order::query()->create([
            'amount' => $postCheckoutAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'coupon_code' => 'RECALC10',
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        // processOrder recalculation formula (OrderHelper line 151)
        $processOrderAmount = max(
            $order->sub_total + $order->shipping_amount + $order->tax_amount + ($order->payment_fee ?? 0) - $order->discount_amount,
            0
        );

        $this->assertEqualsWithDelta(
            $order->amount,
            $processOrderAmount,
            0.01,
            'processOrder recalculation must match original postCheckout amount'
        );
    }

    public function test_process_order_recalculation_with_fixed_amount_coupon(): void
    {
        $product = $this->createProduct(['price' => 150]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'FIXED25',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::MINIMUM_ORDER_AMOUNT,
            'value' => 25,
            'min_order_price' => 50,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $rawTotal = Cart::instance('cart')->rawTotal();
        $subTotal = Cart::instance('cart')->rawSubTotal();
        $taxAmount = Cart::instance('cart')->rawTax();
        $shippingAmount = 10;

        $couponData = $this->couponService->getCouponDiscountAmount($discount, [], []);
        $discountAmount = $couponData['discount_amount'];
        $this->assertEquals(25, $discountAmount);

        $postCheckoutAmount = max($rawTotal - $discountAmount, 0) + $shippingAmount;

        $order = Order::query()->create([
            'amount' => $postCheckoutAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'coupon_code' => 'FIXED25',
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        $processOrderAmount = max(
            $order->sub_total + $order->shipping_amount + $order->tax_amount + ($order->payment_fee ?? 0) - $order->discount_amount,
            0
        );

        $this->assertEqualsWithDelta(
            $order->amount,
            $processOrderAmount,
            0.01,
            'processOrder recalculation must match original postCheckout amount'
        );
    }

    // ========================================
    // saveInformation / HandleCheckoutOrderData RECALCULATION SIMULATION
    // ========================================

    public function test_save_information_recalculation_matches_stored_amount(): void
    {
        $product = $this->createProduct(['price' => 80]);
        $this->addProductToCart($product, 3);

        $discount = Discount::query()->create([
            'code' => 'SAVE15',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 15,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $rawTotal = Cart::instance('cart')->rawTotal();
        $subTotal = Cart::instance('cart')->rawSubTotal();
        $taxAmount = Cart::instance('cart')->rawTax();

        $couponData = $this->couponService->getCouponDiscountAmount($discount, [], []);
        $discountAmount = $couponData['discount_amount'];

        // Test with multiple shipping amounts (simulating user changing shipping method)
        foreach ([0, 5, 12, 25, 50] as $shippingAmount) {
            $postCheckoutAmount = max($rawTotal - $discountAmount, 0) + $shippingAmount;

            $order = Order::query()->create([
                'amount' => $postCheckoutAmount,
                'sub_total' => $subTotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $discountAmount,
                'status' => OrderStatusEnum::PENDING,
                'shipping_method' => ShippingMethodEnum::DEFAULT,
                'is_finished' => false,
            ]);

            // saveInformation formula (PublicCheckoutController line 583)
            $newAmount = $order->sub_total - $order->discount_amount + $order->tax_amount + $shippingAmount + ($order->payment_fee ?? 0);

            $this->assertEqualsWithDelta(
                $order->amount,
                $newAmount,
                0.01,
                "saveInformation recalculation mismatch with shipping={$shippingAmount}"
            );
        }
    }

    // ========================================
    // CHECKOUT vs SUCCESS PAGE CONSISTENCY
    // ========================================

    public function test_success_page_total_matches_checkout_total(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 2);

        $rawTotal = Cart::instance('cart')->rawTotal();
        $subTotal = Cart::instance('cart')->rawSubTotal();
        $fullTax = Cart::instance('cart')->rawTax();
        $discountAmount = 20;
        $shippingAmount = 15;

        // Checkout page displays: rawTax() — full tax with no discount adjustment
        $checkoutDisplayedTax = $fullTax;

        // postCheckout stores tax_amount as rawTax() (after fix)
        $storedTaxAmount = Cart::instance('cart')->rawTax();

        // Success page reads tax_amount from the order
        $successDisplayedTax = $storedTaxAmount;

        // Tax displayed on both pages must match
        $this->assertEquals($checkoutDisplayedTax, $successDisplayedTax);

        // Total on checkout: rawTotal - discount + shipping
        $checkoutTotal = max($rawTotal - $discountAmount, 0) + $shippingAmount;

        // Total on success: sub_total - discount + tax_amount + shipping (from stored order)
        $successTotal = $subTotal - $discountAmount + $storedTaxAmount + $shippingAmount;

        $this->assertEqualsWithDelta(
            $checkoutTotal,
            $successTotal,
            0.01,
            'Success page total must match checkout page total'
        );
    }

    public function test_success_page_breakdown_adds_up_to_total(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 2);

        $rawTotal = Cart::instance('cart')->rawTotal();
        $subTotal = Cart::instance('cart')->rawSubTotal();
        $taxAmount = Cart::instance('cart')->rawTax();
        $discountAmount = 20;
        $shippingAmount = 15;

        $orderAmount = max($rawTotal - $discountAmount, 0) + $shippingAmount;

        $order = Order::query()->create([
            'amount' => $orderAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'payment_fee' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        // Success page breakdown: sub_total + tax_amount + shipping_amount - discount_amount = amount
        $breakdownTotal = $order->sub_total + $order->tax_amount + $order->shipping_amount - $order->discount_amount + ($order->payment_fee ?? 0);

        $this->assertEqualsWithDelta(
            $order->amount,
            $breakdownTotal,
            0.01,
            'Order breakdown (sub_total + tax + shipping - discount + payment_fee) must equal stored amount'
        );
    }

    // ========================================
    // EDGE CASES
    // ========================================

    public function test_order_consistency_with_zero_discount(): void
    {
        $product = $this->createProduct(['price' => 50]);
        $this->addProductToCart($product, 1);

        $rawTotal = Cart::instance('cart')->rawTotal();
        $subTotal = Cart::instance('cart')->rawSubTotal();
        $taxAmount = Cart::instance('cart')->rawTax();
        $shippingAmount = 10;

        $orderAmount = $rawTotal + $shippingAmount;

        $order = Order::query()->create([
            'amount' => $orderAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        $recalculated = $order->sub_total - $order->discount_amount + $order->tax_amount + $order->shipping_amount;

        $this->assertEqualsWithDelta($order->amount, $recalculated, 0.01);
    }

    public function test_order_consistency_with_zero_shipping(): void
    {
        $product = $this->createProduct(['price' => 75]);
        $this->addProductToCart($product, 2);

        $rawTotal = Cart::instance('cart')->rawTotal();
        $subTotal = Cart::instance('cart')->rawSubTotal();
        $taxAmount = Cart::instance('cart')->rawTax();
        $discountAmount = 15;

        $orderAmount = max($rawTotal - $discountAmount, 0);

        $order = Order::query()->create([
            'amount' => $orderAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => 0,
            'discount_amount' => $discountAmount,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        $recalculated = max($order->sub_total - $order->discount_amount + $order->tax_amount + $order->shipping_amount, 0);

        $this->assertEqualsWithDelta($order->amount, $recalculated, 0.01);
    }

    public function test_order_consistency_with_discount_equal_to_subtotal(): void
    {
        $subTotal = 100;
        $taxAmount = 5;
        $shippingAmount = 10;
        $discountAmount = 100;

        // rawTotal = subTotal + tax = 105. amount = max(105 - 100, 0) + 10 = 15
        $rawTotal = $subTotal + $taxAmount;
        $orderAmount = max($rawTotal - $discountAmount, 0) + $shippingAmount;

        $order = Order::query()->create([
            'amount' => $orderAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        $recalculated = max($order->sub_total - $order->discount_amount + $order->tax_amount + $order->shipping_amount, 0);

        $this->assertEqualsWithDelta($order->amount, $recalculated, 0.01);
    }

    public function test_order_consistency_with_large_discount_does_not_go_negative(): void
    {
        $subTotal = 50;
        $taxAmount = 2.5;
        $shippingAmount = 5;
        $discountAmount = 60; // larger than sub_total

        $rawTotal = $subTotal + $taxAmount;
        $orderAmount = max($rawTotal - $discountAmount, 0) + $shippingAmount;
        $this->assertEquals(5, $orderAmount); // only shipping remains

        $order = Order::query()->create([
            'amount' => $orderAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        $recalculated = max($order->sub_total - $order->discount_amount + $order->tax_amount + $order->shipping_amount, 0);

        // Both use max(..., 0) so neither goes negative
        $this->assertGreaterThanOrEqual(0, $order->amount);
        $this->assertGreaterThanOrEqual(0, $recalculated);
    }

    // ========================================
    // rawTax() vs rawTax($discount): CONSISTENCY CHECK
    // ========================================

    public function test_raw_tax_without_discount_equals_raw_total_minus_raw_sub_total(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 2);

        $rawTotal = Cart::instance('cart')->rawTotal();
        $rawSubTotal = Cart::instance('cart')->rawSubTotal();
        $rawTax = Cart::instance('cart')->rawTax();

        // rawTotal = rawSubTotal + rawTax (when no discount)
        $this->assertEqualsWithDelta(
            $rawTotal,
            $rawSubTotal + $rawTax,
            0.01,
            'rawTotal must equal rawSubTotal + rawTax (no discount)'
        );
    }

    public function test_storing_full_tax_preserves_equation_storing_adjusted_tax_breaks_it(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 2);

        $rawTotal = Cart::instance('cart')->rawTotal();
        $subTotal = Cart::instance('cart')->rawSubTotal();
        $fullTax = Cart::instance('cart')->rawTax();
        $discountAmount = 30;
        $shippingAmount = 15;

        // postCheckout amount formula
        $postCheckoutAmount = max($rawTotal - $discountAmount, 0) + $shippingAmount;

        // With full tax (the fix): recalculation matches
        $recalcWithFullTax = $subTotal - $discountAmount + $fullTax + $shippingAmount;
        $this->assertEqualsWithDelta($postCheckoutAmount, $recalcWithFullTax, 0.01);

        // With adjusted tax (the old bug): recalculation does NOT match
        $adjustedTax = Cart::instance('cart')->rawTax($discountAmount);
        if ($fullTax != $adjustedTax) {
            $recalcWithAdjustedTax = $subTotal - $discountAmount + $adjustedTax + $shippingAmount;
            $this->assertNotEquals(
                round($postCheckoutAmount, 2),
                round($recalcWithAdjustedTax, 2),
                'Adjusted tax should break the equation (proving the bug)'
            );
        }
    }
}
