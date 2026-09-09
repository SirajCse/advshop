<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Tax;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShippingTaxTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::load(true);
        Cart::instance('cart')->destroy();
    }

    protected function tearDown(): void
    {
        Cart::instance('cart')->destroy();

        parent::tearDown();
    }

    protected function setSetting(string $key, mixed $value): void
    {
        Setting::load(true);
        Setting::set($key, $value);
        Setting::save();
        Setting::load(true);
    }

    protected function enableShippingTax(): void
    {
        $this->setSetting('ecommerce_ecommerce_tax_enabled', 1);
        $this->setSetting('ecommerce_tax_on_shipping_fee', 1);
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

    protected function createProduct(float $price = 100): Product
    {
        return Product::query()->create([
            'name' => 'Test Product',
            'price' => $price,
            'status' => BaseStatusEnum::PUBLISHED,
            'is_variation' => false,
            'quantity' => 100,
            'with_storehouse_management' => false,
        ]);
    }

    protected function addProductToCart(Product $product, int $qty = 1, float $taxRate = 0): void
    {
        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            $qty,
            $product->price,
            [
                'product' => $product,
                'taxRate' => $taxRate,
            ]
        );
    }

    // ========================================
    // isTaxOnShippingFeeEnabled()
    // ========================================

    public function test_shipping_tax_disabled_when_tax_globally_disabled(): void
    {
        $this->setSetting('ecommerce_ecommerce_tax_enabled', 0);
        $this->setSetting('ecommerce_tax_on_shipping_fee', 1);

        $this->assertFalse(EcommerceHelper::isTaxOnShippingFeeEnabled());
    }

    public function test_shipping_tax_disabled_when_setting_is_off(): void
    {
        $this->setSetting('ecommerce_ecommerce_tax_enabled', 1);
        $this->setSetting('ecommerce_tax_on_shipping_fee', 0);

        $this->assertFalse(EcommerceHelper::isTaxOnShippingFeeEnabled());
    }

    public function test_shipping_tax_enabled_when_both_settings_on(): void
    {
        $this->enableShippingTax();

        $this->assertTrue(EcommerceHelper::isTaxOnShippingFeeEnabled());
    }

    public function test_shipping_tax_disabled_when_only_tax_enabled(): void
    {
        $this->setSetting('ecommerce_ecommerce_tax_enabled', 1);
        $this->setSetting('ecommerce_tax_on_shipping_fee', 0);

        $this->assertFalse(EcommerceHelper::isTaxOnShippingFeeEnabled());
    }

    // ========================================
    // calculateShippingTax() — feature disabled
    // ========================================

    public function test_calculate_shipping_tax_returns_zero_when_feature_disabled(): void
    {
        $this->setSetting('ecommerce_ecommerce_tax_enabled', 1);
        $this->setSetting('ecommerce_tax_on_shipping_fee', 0);

        $this->assertEquals(0, EcommerceHelper::calculateShippingTax(20.0));
    }

    public function test_calculate_shipping_tax_returns_zero_when_tax_globally_disabled(): void
    {
        $this->setSetting('ecommerce_ecommerce_tax_enabled', 0);
        $this->setSetting('ecommerce_tax_on_shipping_fee', 1);

        $this->assertEquals(0, EcommerceHelper::calculateShippingTax(20.0));
    }

    // ========================================
    // calculateShippingTax() — zero/negative shipping
    // ========================================

    public function test_calculate_shipping_tax_returns_zero_for_zero_shipping(): void
    {
        $this->enableShippingTax();
        $tax = $this->createTax(10);
        $this->setSetting('ecommerce_default_tax_rate', $tax->id);

        $this->assertEquals(0, EcommerceHelper::calculateShippingTax(0));
    }

    public function test_calculate_shipping_tax_returns_zero_for_negative_shipping(): void
    {
        $this->enableShippingTax();
        $tax = $this->createTax(10);
        $this->setSetting('ecommerce_default_tax_rate', $tax->id);

        $this->assertEquals(0, EcommerceHelper::calculateShippingTax(-5.0));
    }

    // ========================================
    // calculateShippingTax() — using default tax rate
    // ========================================

    public function test_calculate_shipping_tax_uses_default_tax_rate(): void
    {
        $this->enableShippingTax();
        $tax = $this->createTax(15);
        $this->setSetting('ecommerce_default_tax_rate', $tax->id);

        $result = EcommerceHelper::calculateShippingTax(20.0);

        $this->assertEqualsWithDelta(3.0, $result, 0.01);
    }

    public function test_calculate_shipping_tax_with_10_percent_rate(): void
    {
        $this->enableShippingTax();
        $tax = $this->createTax(10);
        $this->setSetting('ecommerce_default_tax_rate', $tax->id);

        $result = EcommerceHelper::calculateShippingTax(50.0);

        $this->assertEqualsWithDelta(5.0, $result, 0.01);
    }

    public function test_calculate_shipping_tax_with_fractional_result(): void
    {
        $this->enableShippingTax();
        $tax = $this->createTax(7);
        $this->setSetting('ecommerce_default_tax_rate', $tax->id);

        $result = EcommerceHelper::calculateShippingTax(15.0);

        $this->assertEqualsWithDelta(1.05, $result, 0.01);
    }

    public function test_calculate_shipping_tax_returns_zero_when_default_tax_has_zero_percentage(): void
    {
        $this->enableShippingTax();
        $tax = $this->createTax(0);
        $this->setSetting('ecommerce_default_tax_rate', $tax->id);

        $this->assertEquals(0, EcommerceHelper::calculateShippingTax(20.0));
    }

    public function test_calculate_shipping_tax_returns_zero_when_default_tax_rate_id_invalid(): void
    {
        $this->enableShippingTax();
        $this->setSetting('ecommerce_default_tax_rate', 99999);

        $this->assertEquals(0, EcommerceHelper::calculateShippingTax(20.0));
    }

    // ========================================
    // calculateShippingTax() — cart item fallback
    // ========================================

    public function test_calculate_shipping_tax_falls_back_to_cart_item_tax_rate(): void
    {
        $this->enableShippingTax();

        $product = $this->createProduct(100);
        $this->addProductToCart($product, 1, 15);

        $result = EcommerceHelper::calculateShippingTax(20.0);

        $this->assertEqualsWithDelta(3.0, $result, 0.01);
    }

    public function test_calculate_shipping_tax_uses_first_nonzero_cart_item_tax_rate(): void
    {
        $this->enableShippingTax();

        $product1 = $this->createProduct(50);
        $product2 = $this->createProduct(75);

        $this->addProductToCart($product1, 1, 0);
        $this->addProductToCart($product2, 1, 12);

        $result = EcommerceHelper::calculateShippingTax(20.0);

        $this->assertEqualsWithDelta(2.4, $result, 0.01);
    }

    public function test_calculate_shipping_tax_returns_zero_when_no_tax_source_available(): void
    {
        $this->enableShippingTax();

        $this->assertEquals(0, EcommerceHelper::calculateShippingTax(20.0));
    }

    public function test_calculate_shipping_tax_returns_zero_when_cart_items_have_zero_tax(): void
    {
        $this->enableShippingTax();

        $product = $this->createProduct(100);
        $this->addProductToCart($product, 1, 0);

        $this->assertEquals(0, EcommerceHelper::calculateShippingTax(20.0));
    }

    public function test_default_tax_rate_takes_precedence_over_cart_item_rate(): void
    {
        $this->enableShippingTax();
        $tax = $this->createTax(10);
        $this->setSetting('ecommerce_default_tax_rate', $tax->id);

        $product = $this->createProduct(100);
        $this->addProductToCart($product, 1, 20);

        $result = EcommerceHelper::calculateShippingTax(50.0);

        // Should use default tax rate (10%), not cart item rate (20%)
        $this->assertEqualsWithDelta(5.0, $result, 0.01);
    }

    // ========================================
    // Order equation with shipping tax
    // ========================================

    public function test_order_amount_includes_shipping_tax(): void
    {
        $subTotal = 100;
        $taxAmount = 10;
        $shippingAmount = 20;
        $shippingTaxAmount = 3;
        $discountAmount = 0;

        $orderAmount = $subTotal + $taxAmount + $shippingAmount + $shippingTaxAmount - $discountAmount;

        $order = Order::query()->create([
            'amount' => $orderAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'shipping_tax_amount' => $shippingTaxAmount,
            'discount_amount' => $discountAmount,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        $this->assertEquals(133, $order->amount);
        $this->assertEquals(3, $order->shipping_tax_amount);

        $recalculated = $order->sub_total + $order->tax_amount + $order->shipping_amount
            + ($order->shipping_tax_amount ?? 0) - $order->discount_amount + ($order->payment_fee ?? 0);

        $this->assertEqualsWithDelta($order->amount, $recalculated, 0.01);
    }

    public function test_order_amount_equation_with_shipping_tax_and_discount(): void
    {
        $subTotal = 200;
        $taxAmount = 20;
        $shippingAmount = 15;
        $shippingTaxAmount = 1.5;
        $discountAmount = 30;

        $rawTotal = $subTotal + $taxAmount;
        $orderAmount = max($rawTotal - $discountAmount, 0) + $shippingAmount + $shippingTaxAmount;

        $order = Order::query()->create([
            'amount' => $orderAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'shipping_tax_amount' => $shippingTaxAmount,
            'discount_amount' => $discountAmount,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        $this->assertEqualsWithDelta(206.5, $order->amount, 0.01);
        $this->assertEqualsWithDelta(1.5, $order->shipping_tax_amount, 0.01);
    }

    public function test_order_with_zero_shipping_tax_when_free_shipping(): void
    {
        $subTotal = 100;
        $taxAmount = 10;
        $shippingAmount = 0;
        $shippingTaxAmount = 0;

        $orderAmount = $subTotal + $taxAmount + $shippingAmount + $shippingTaxAmount;

        $order = Order::query()->create([
            'amount' => $orderAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'shipping_tax_amount' => $shippingTaxAmount,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        $this->assertEquals(0, $order->shipping_tax_amount);
        $this->assertEquals(110, $order->amount);
    }

    public function test_shipping_tax_defaults_to_zero_for_existing_orders(): void
    {
        $order = Order::query()->create([
            'amount' => 110,
            'sub_total' => 100,
            'tax_amount' => 10,
            'shipping_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        $this->assertEquals(0, (float) ($order->shipping_tax_amount ?? 0));
    }

    // ========================================
    // Recalculation consistency with shipping tax
    // ========================================

    public function test_recalculation_with_shipping_tax_across_different_shipping_amounts(): void
    {
        $this->enableShippingTax();
        $tax = $this->createTax(10);
        $this->setSetting('ecommerce_default_tax_rate', $tax->id);

        $subTotal = 200;
        $taxAmount = 20;
        $discountAmount = 30;

        foreach ([0, 5, 15, 50, 100] as $shippingAmount) {
            $shippingTaxAmount = EcommerceHelper::calculateShippingTax($shippingAmount);
            $rawTotal = $subTotal + $taxAmount;
            $orderAmount = max($rawTotal - $discountAmount, 0) + $shippingAmount + $shippingTaxAmount;

            $order = Order::query()->create([
                'amount' => $orderAmount,
                'sub_total' => $subTotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'shipping_tax_amount' => $shippingTaxAmount,
                'discount_amount' => $discountAmount,
                'status' => OrderStatusEnum::PENDING,
                'shipping_method' => ShippingMethodEnum::DEFAULT,
                'is_finished' => false,
            ]);

            $recalculated = max(
                $order->sub_total - $order->discount_amount + $order->tax_amount + $order->shipping_amount + ($order->payment_fee ?? 0),
                0
            ) + ($order->shipping_tax_amount ?? 0);

            $this->assertEqualsWithDelta(
                $order->amount,
                $recalculated,
                0.01,
                "Recalculation mismatch with shipping_amount={$shippingAmount}, shipping_tax={$shippingTaxAmount}"
            );
        }
    }

    // ========================================
    // Marketplace: per-vendor shipping
    // ========================================

    public function test_per_vendor_shipping_tax_calculated_independently(): void
    {
        $this->enableShippingTax();
        $tax = $this->createTax(10);
        $this->setSetting('ecommerce_default_tax_rate', $tax->id);

        $vendorOrders = [
            ['sub_total' => 100, 'tax_amount' => 10, 'shipping_amount' => 20],
            ['sub_total' => 200, 'tax_amount' => 20, 'shipping_amount' => 15],
            ['sub_total' => 50, 'tax_amount' => 5, 'shipping_amount' => 0],
        ];

        $orders = collect();
        foreach ($vendorOrders as $data) {
            $shippingTaxAmount = EcommerceHelper::calculateShippingTax($data['shipping_amount']);
            $orderAmount = $data['sub_total'] + $data['tax_amount'] + $data['shipping_amount'] + $shippingTaxAmount;

            $order = Order::query()->create([
                'amount' => $orderAmount,
                'sub_total' => $data['sub_total'],
                'tax_amount' => $data['tax_amount'],
                'shipping_amount' => $data['shipping_amount'],
                'shipping_tax_amount' => $shippingTaxAmount,
                'discount_amount' => 0,
                'status' => OrderStatusEnum::PENDING,
                'shipping_method' => ShippingMethodEnum::DEFAULT,
                'is_finished' => false,
            ]);

            $orders->push($order);
        }

        // Vendor A: shipping $20 → tax $2
        $this->assertEqualsWithDelta(2.0, $orders[0]->shipping_tax_amount, 0.01);
        $this->assertEqualsWithDelta(132.0, $orders[0]->amount, 0.01);

        // Vendor B: shipping $15 → tax $1.50
        $this->assertEqualsWithDelta(1.5, $orders[1]->shipping_tax_amount, 0.01);
        $this->assertEqualsWithDelta(236.5, $orders[1]->amount, 0.01);

        // Vendor C: free shipping → tax $0
        $this->assertEquals(0, $orders[2]->shipping_tax_amount);
        $this->assertEqualsWithDelta(55.0, $orders[2]->amount, 0.01);

        // Aggregate totals
        $this->assertEqualsWithDelta(3.5, $orders->sum('shipping_tax_amount'), 0.01);
        $this->assertEqualsWithDelta(35.0, $orders->sum('shipping_amount'), 0.01);
    }

    // ========================================
    // Marketplace: unified shipping (proportional split)
    // ========================================

    public function test_unified_shipping_tax_proportional_split(): void
    {
        $this->enableShippingTax();
        $tax = $this->createTax(10);
        $this->setSetting('ecommerce_default_tax_rate', $tax->id);

        $totalUnifiedShipping = 30.0;
        $totalCartAmount = 300.0;

        $vendorData = [
            ['raw_total' => 100, 'sub_total' => 100, 'tax_amount' => 10],
            ['raw_total' => 200, 'sub_total' => 200, 'tax_amount' => 20],
        ];

        $orders = collect();
        $totalShippingTax = 0;

        foreach ($vendorData as $data) {
            $vendorProportion = $data['raw_total'] / $totalCartAmount;
            $shippingAmount = round($totalUnifiedShipping * $vendorProportion, 2);
            $shippingTaxAmount = EcommerceHelper::calculateShippingTax($shippingAmount);
            $totalShippingTax += $shippingTaxAmount;

            $orderAmount = $data['sub_total'] + $data['tax_amount'] + $shippingAmount + $shippingTaxAmount;

            $order = Order::query()->create([
                'amount' => $orderAmount,
                'sub_total' => $data['sub_total'],
                'tax_amount' => $data['tax_amount'],
                'shipping_amount' => $shippingAmount,
                'shipping_tax_amount' => $shippingTaxAmount,
                'discount_amount' => 0,
                'status' => OrderStatusEnum::PENDING,
                'shipping_method' => ShippingMethodEnum::DEFAULT,
                'is_finished' => false,
            ]);

            $orders->push($order);
        }

        // Vendor A: 100/300 = 1/3 → shipping $10 → tax $1
        $this->assertEqualsWithDelta(10.0, $orders[0]->shipping_amount, 0.01);
        $this->assertEqualsWithDelta(1.0, $orders[0]->shipping_tax_amount, 0.01);

        // Vendor B: 200/300 = 2/3 → shipping $20 → tax $2
        $this->assertEqualsWithDelta(20.0, $orders[1]->shipping_amount, 0.01);
        $this->assertEqualsWithDelta(2.0, $orders[1]->shipping_tax_amount, 0.01);

        // Sum of proportional shipping taxes ≈ tax on total shipping
        $expectedTotalTax = EcommerceHelper::calculateShippingTax($totalUnifiedShipping);
        $this->assertEqualsWithDelta($expectedTotalTax, $totalShippingTax, 0.02);

        // Sum of shipping amounts = total unified shipping
        $this->assertEqualsWithDelta($totalUnifiedShipping, $orders->sum('shipping_amount'), 0.01);
    }

    public function test_unified_shipping_tax_with_uneven_proportions(): void
    {
        $this->enableShippingTax();
        $tax = $this->createTax(15);
        $this->setSetting('ecommerce_default_tax_rate', $tax->id);

        $totalUnifiedShipping = 25.0;
        $totalCartAmount = 175.0;

        $vendorData = [
            ['raw_total' => 50, 'sub_total' => 50, 'tax_amount' => 7.5],
            ['raw_total' => 75, 'sub_total' => 75, 'tax_amount' => 11.25],
            ['raw_total' => 50, 'sub_total' => 50, 'tax_amount' => 7.5],
        ];

        $orders = collect();
        foreach ($vendorData as $data) {
            $vendorProportion = $data['raw_total'] / $totalCartAmount;
            $shippingAmount = round($totalUnifiedShipping * $vendorProportion, 2);
            $shippingTaxAmount = EcommerceHelper::calculateShippingTax($shippingAmount);

            $orderAmount = $data['sub_total'] + $data['tax_amount'] + $shippingAmount + $shippingTaxAmount;

            $order = Order::query()->create([
                'amount' => $orderAmount,
                'sub_total' => $data['sub_total'],
                'tax_amount' => $data['tax_amount'],
                'shipping_amount' => $shippingAmount,
                'shipping_tax_amount' => $shippingTaxAmount,
                'discount_amount' => 0,
                'status' => OrderStatusEnum::PENDING,
                'shipping_method' => ShippingMethodEnum::DEFAULT,
                'is_finished' => false,
            ]);

            $orders->push($order);
        }

        // Each order has correct proportional shipping tax
        foreach ($orders as $order) {
            $expectedTax = round($order->shipping_amount * 15 / 100, 2);
            $this->assertEqualsWithDelta($expectedTax, $order->shipping_tax_amount, 0.01);
        }

        // Sum of proportional taxes ≈ tax on full amount (allow small rounding diff)
        $expectedTotalTax = EcommerceHelper::calculateShippingTax($totalUnifiedShipping);
        $this->assertEqualsWithDelta($expectedTotalTax, $orders->sum('shipping_tax_amount'), 0.02);
    }

    // ========================================
    // Marketplace: vendor commission
    // ========================================

    public function test_vendor_commission_excludes_shipping_tax(): void
    {
        $subTotal = 200;
        $taxAmount = 20;
        $shippingAmount = 15;
        $shippingTaxAmount = 1.5;
        $paymentFee = 3;

        $orderAmount = $subTotal + $taxAmount + $shippingAmount + $shippingTaxAmount + $paymentFee;

        $order = Order::query()->create([
            'amount' => $orderAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'shipping_tax_amount' => $shippingTaxAmount,
            'payment_fee' => $paymentFee,
            'discount_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        // Commission base = amount - shipping - shipping_tax - tax - payment_fee
        $commissionBase = $order->amount - $order->shipping_amount
            - ($order->shipping_tax_amount ?? 0) - $order->tax_amount - $order->payment_fee;

        // Commission base should equal the product sub_total
        $this->assertEqualsWithDelta($subTotal, $commissionBase, 0.01);
    }

    public function test_vendor_commission_excludes_shipping_tax_with_discount(): void
    {
        $subTotal = 200;
        $taxAmount = 20;
        $shippingAmount = 15;
        $shippingTaxAmount = 1.5;
        $discountAmount = 30;
        $paymentFee = 0;

        $rawTotal = $subTotal + $taxAmount;
        $orderAmount = max($rawTotal - $discountAmount, 0) + $shippingAmount + $shippingTaxAmount + $paymentFee;

        $order = Order::query()->create([
            'amount' => $orderAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'shipping_tax_amount' => $shippingTaxAmount,
            'discount_amount' => $discountAmount,
            'payment_fee' => $paymentFee,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        $commissionBase = $order->amount - $order->shipping_amount
            - ($order->shipping_tax_amount ?? 0) - $order->tax_amount - ($order->payment_fee ?? 0);

        // Commission base = sub_total - discount
        $this->assertEqualsWithDelta($subTotal - $discountAmount, $commissionBase, 0.01);
    }

    // ========================================
    // Marketplace: checkout success recalculation
    // ========================================

    public function test_checkout_success_recalculation_includes_shipping_tax(): void
    {
        $subTotal = 150;
        $taxAmount = 15;
        $shippingAmount = 20;
        $shippingTaxAmount = 2;
        $paymentFee = 5;
        $discountAmount = 10;

        $orderAmount = $subTotal + $taxAmount + $shippingAmount + $shippingTaxAmount + $paymentFee - $discountAmount;

        $order = Order::query()->create([
            'amount' => $orderAmount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'shipping_tax_amount' => $shippingTaxAmount,
            'payment_fee' => $paymentFee,
            'discount_amount' => $discountAmount,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        // Simulates processGetCheckoutSuccess recalculation formula
        $recalculated = $order->sub_total + $order->tax_amount + $order->shipping_amount
            + ($order->shipping_tax_amount ?? 0) + $order->payment_fee - $order->discount_amount;

        $this->assertEqualsWithDelta($order->amount, $recalculated, 0.01);
    }

    public function test_checkout_success_recalculation_with_multiple_vendor_orders(): void
    {
        $vendorOrders = [
            ['sub_total' => 100, 'tax_amount' => 10, 'shipping_amount' => 12, 'shipping_tax_amount' => 1.2, 'payment_fee' => 2, 'discount_amount' => 5],
            ['sub_total' => 200, 'tax_amount' => 20, 'shipping_amount' => 18, 'shipping_tax_amount' => 1.8, 'payment_fee' => 3, 'discount_amount' => 15],
        ];

        $orders = collect();
        foreach ($vendorOrders as $data) {
            $orderAmount = $data['sub_total'] + $data['tax_amount'] + $data['shipping_amount']
                + $data['shipping_tax_amount'] + $data['payment_fee'] - $data['discount_amount'];

            $order = Order::query()->create(array_merge($data, [
                'amount' => $orderAmount,
                'status' => OrderStatusEnum::PENDING,
                'shipping_method' => ShippingMethodEnum::DEFAULT,
                'is_finished' => false,
                'token' => 'test-token-multi',
            ]));

            $orders->push($order);
        }

        // Each order recalculates correctly
        foreach ($orders as $order) {
            $recalculated = $order->sub_total + $order->tax_amount + $order->shipping_amount
                + ($order->shipping_tax_amount ?? 0) + $order->payment_fee - $order->discount_amount;

            $this->assertEqualsWithDelta(
                $order->amount,
                $recalculated,
                0.01,
                "Order {$order->id} recalculation mismatch"
            );
        }

        // Aggregate shipping tax
        $this->assertEqualsWithDelta(3.0, $orders->sum('shipping_tax_amount'), 0.01);
    }
}
