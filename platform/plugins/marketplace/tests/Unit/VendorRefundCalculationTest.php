<?php

namespace Botble\Marketplace\Tests\Unit;

use PHPUnit\Framework\TestCase;

class VendorRefundCalculationTest extends TestCase
{
    // Mirrors the logic in HookServiceProvider::afterOrderRefunded()
    // vendorRefundAmount = refundAmount * (originalRevenueAmount / orderAmount)

    public function test_full_refund_with_tax_no_commission(): void
    {
        // Order: $1,890 products + $189 tax = $2,079 total
        // Vendor received: $1,890 (no commission)
        // Full refund: $2,079
        $vendorRefund = $this->calculateVendorRefundAmount(
            refundAmount: 2079,
            originalRevenueAmount: 1890,
            orderAmount: 2079
        );

        $this->assertEquals(1890, $vendorRefund);
    }

    public function test_full_refund_with_tax_and_commission(): void
    {
        // Order: $1,890 products + $189 tax = $2,079 total
        // Commission: 10% of $1,890 = $189
        // Vendor received: $1,701
        // Full refund: $2,079
        $vendorRefund = $this->calculateVendorRefundAmount(
            refundAmount: 2079,
            originalRevenueAmount: 1701,
            orderAmount: 2079
        );

        $this->assertEquals(1701, $vendorRefund);
    }

    public function test_partial_refund_with_tax(): void
    {
        // Order: $1,890 products + $189 tax = $2,079 total
        // Vendor received: $1,890 (no commission)
        // Partial refund: $500
        $vendorRefund = $this->calculateVendorRefundAmount(
            refundAmount: 500,
            originalRevenueAmount: 1890,
            orderAmount: 2079
        );

        // $500 * ($1,890 / $2,079) = $454.55
        $this->assertEqualsWithDelta(454.55, $vendorRefund, 0.01);
    }

    public function test_partial_refund_with_tax_and_commission(): void
    {
        // Order: $1,890 products + $189 tax = $2,079 total
        // Commission: 10% of $1,890 = $189, vendor received: $1,701
        // Partial refund: $500
        $vendorRefund = $this->calculateVendorRefundAmount(
            refundAmount: 500,
            originalRevenueAmount: 1701,
            orderAmount: 2079
        );

        // $500 * ($1,701 / $2,079) = $409.09
        $this->assertEqualsWithDelta(409.09, $vendorRefund, 0.01);
    }

    public function test_full_refund_no_tax_no_commission(): void
    {
        // No tax, no commission: vendor received full order amount
        $vendorRefund = $this->calculateVendorRefundAmount(
            refundAmount: 1000,
            originalRevenueAmount: 1000,
            orderAmount: 1000
        );

        $this->assertEquals(1000, $vendorRefund);
    }

    public function test_full_refund_with_shipping_tax_and_commission(): void
    {
        // Order: $1,000 products + $100 tax + $50 shipping + $10 shipping_tax = $1,160
        // orderAmountWithoutShippingFee = $1,160 - $50 - $10 - $100 - $0 = $1,000
        // Commission: 15% of $1,000 = $150, vendor received: $850
        $vendorRefund = $this->calculateVendorRefundAmount(
            refundAmount: 1160,
            originalRevenueAmount: 850,
            orderAmount: 1160
        );

        $this->assertEquals(850, $vendorRefund);
    }

    public function test_refund_amount_never_exceeds_original_revenue(): void
    {
        // Full refund should never exceed what vendor received
        $orderAmount = 2079;
        $originalRevenueAmount = 1701;

        $vendorRefund = $this->calculateVendorRefundAmount(
            refundAmount: $orderAmount,
            originalRevenueAmount: $originalRevenueAmount,
            orderAmount: $orderAmount
        );

        $this->assertLessThanOrEqual($originalRevenueAmount, $vendorRefund);
    }

    public function test_zero_refund_amount(): void
    {
        $vendorRefund = $this->calculateVendorRefundAmount(
            refundAmount: 0,
            originalRevenueAmount: 1890,
            orderAmount: 2079
        );

        $this->assertEquals(0, $vendorRefund);
    }

    public function test_zero_order_amount_returns_zero(): void
    {
        $vendorRefund = $this->calculateVendorRefundAmount(
            refundAmount: 500,
            originalRevenueAmount: 0,
            orderAmount: 0
        );

        $this->assertEquals(0, $vendorRefund);
    }

    public function test_zero_revenue_amount_returns_zero(): void
    {
        $vendorRefund = $this->calculateVendorRefundAmount(
            refundAmount: 500,
            originalRevenueAmount: 0,
            orderAmount: 2079
        );

        $this->assertEquals(0, $vendorRefund);
    }

    public function test_small_decimal_amounts(): void
    {
        // Order: $9.99 products + $1.00 tax = $10.99
        // Vendor received: $9.99
        $vendorRefund = $this->calculateVendorRefundAmount(
            refundAmount: 10.99,
            originalRevenueAmount: 9.99,
            orderAmount: 10.99
        );

        $this->assertEquals(9.99, $vendorRefund);
    }

    public function test_multiple_partial_refunds_proportional(): void
    {
        // Order: $2,079, vendor received: $1,890
        // First partial refund of $1,000
        $firstRefund = $this->calculateVendorRefundAmount(
            refundAmount: 1000,
            originalRevenueAmount: 1890,
            orderAmount: 2079
        );

        // Second partial refund of remaining $1,079
        $secondRefund = $this->calculateVendorRefundAmount(
            refundAmount: 1079,
            originalRevenueAmount: 1890,
            orderAmount: 2079
        );

        // Total vendor refund should equal original revenue amount
        $this->assertEqualsWithDelta(1890, $firstRefund + $secondRefund, 0.01);
    }

    public function test_high_tax_rate(): void
    {
        // Order: $1,000 products + $250 tax (25%) = $1,250
        // Vendor received: $1,000 (no commission)
        $vendorRefund = $this->calculateVendorRefundAmount(
            refundAmount: 1250,
            originalRevenueAmount: 1000,
            orderAmount: 1250
        );

        $this->assertEquals(1000, $vendorRefund);
    }

    public function test_high_commission_rate(): void
    {
        // Order: $1,000 products + $100 tax = $1,100
        // Commission: 30% of $1,000 = $300, vendor received: $700
        $vendorRefund = $this->calculateVendorRefundAmount(
            refundAmount: 1100,
            originalRevenueAmount: 700,
            orderAmount: 1100
        );

        $this->assertEquals(700, $vendorRefund);
    }

    /**
     * Mirrors the vendor refund calculation from HookServiceProvider::afterOrderRefunded()
     */
    protected function calculateVendorRefundAmount(
        float $refundAmount,
        float $originalRevenueAmount,
        float $orderAmount
    ): float {
        if ($orderAmount <= 0 || $originalRevenueAmount <= 0) {
            return 0;
        }

        return round($refundAmount * ($originalRevenueAmount / $orderAmount), 2);
    }
}
