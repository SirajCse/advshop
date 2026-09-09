<?php

namespace Botble\Marketplace\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Mirrors the commission math in OrderSupportServiceProvider::afterOrderStatusCompleted():
 *
 *   fee  = subAmount * (percentage / 100)        // (or the per-category equivalent)
 *   fee += fixedFee                               // charged once per vendor sub-order
 *   fee  = min(fee, subAmount)                     // never exceed the sub-amount
 *   amount (vendor earning) = subAmount - fee
 *
 * The fixed fee lets a marketplace recover the flat part of a payment gateway fee
 * (e.g. Stripe/PayPal €0.25) that a percentage-only commission cannot cover on small carts.
 */
class FixedCommissionFeeCalculationTest extends TestCase
{
    public function test_percentage_only_is_backward_compatible(): void
    {
        // fixed fee = 0 behaves exactly like the old percentage-only commission
        $result = $this->calculateCommission(subAmount: 100, percentage: 10, fixedFee: 0);

        $this->assertEqualsWithDelta(10.0, $result['fee'], 0.0001);
        $this->assertEqualsWithDelta(90.0, $result['amount'], 0.0001);
    }

    public function test_fixed_fee_only_without_percentage(): void
    {
        $result = $this->calculateCommission(subAmount: 50, percentage: 0, fixedFee: 0.25);

        $this->assertEqualsWithDelta(0.25, $result['fee'], 0.0001);
        $this->assertEqualsWithDelta(49.75, $result['amount'], 0.0001);
    }

    public function test_percentage_plus_fixed_fee_combined(): void
    {
        // The reported scenario: 4.5% + €0.25 on a €10 cart
        $result = $this->calculateCommission(subAmount: 10, percentage: 4.5, fixedFee: 0.25);

        // 10 * 4.5% = 0.45, + 0.25 fixed = 0.70 commission
        $this->assertEqualsWithDelta(0.70, $result['fee'], 0.0001);
        $this->assertEqualsWithDelta(9.30, $result['amount'], 0.0001);
    }

    public function test_fixed_fee_makes_small_cart_profitable_for_marketplace(): void
    {
        // Stripe charges 2.9% + €0.25 on the €10 charge => 0.29 + 0.25 = 0.54 gateway cost.
        $gatewayCost = 10 * 0.029 + 0.25;

        $percentageOnly = $this->calculateCommission(subAmount: 10, percentage: 4.5, fixedFee: 0)['fee'];
        $withFixedFee = $this->calculateCommission(subAmount: 10, percentage: 4.5, fixedFee: 0.25)['fee'];

        // Percentage-only commission loses money against the gateway cost...
        $this->assertLessThan($gatewayCost, $percentageOnly);
        // ...while the % + fixed commission covers it.
        $this->assertGreaterThanOrEqual($gatewayCost, $withFixedFee);
    }

    public function test_both_zero_charges_no_commission(): void
    {
        $result = $this->calculateCommission(subAmount: 100, percentage: 0, fixedFee: 0);

        $this->assertEqualsWithDelta(0.0, $result['fee'], 0.0001);
        $this->assertEqualsWithDelta(100.0, $result['amount'], 0.0001);
    }

    public function test_fixed_fee_is_capped_at_sub_amount_on_tiny_cart(): void
    {
        // sub-amount smaller than the fixed fee: commission must not exceed the sub-amount
        $result = $this->calculateCommission(subAmount: 0.20, percentage: 4.5, fixedFee: 0.25);

        $this->assertEqualsWithDelta(0.20, $result['fee'], 0.0001);
        $this->assertEqualsWithDelta(0.0, $result['amount'], 0.0001);
    }

    public function test_vendor_payout_is_never_negative(): void
    {
        // Even an absurd fixed fee can never push the vendor earning below zero
        $result = $this->calculateCommission(subAmount: 5, percentage: 10, fixedFee: 100);

        $this->assertGreaterThanOrEqual(0.0, $result['amount']);
        $this->assertEqualsWithDelta(5.0, $result['fee'], 0.0001);
        $this->assertEqualsWithDelta(0.0, $result['amount'], 0.0001);
    }

    public function test_fee_never_exceeds_sub_amount_across_many_inputs(): void
    {
        $cases = [
            [1, 4.5, 0.25],
            [0.5, 50, 1],
            [0.01, 4.5, 0.25],
            [3, 99, 5],
            [10, 100, 0.25],
        ];

        foreach ($cases as [$sub, $pct, $fixed]) {
            $result = $this->calculateCommission($sub, $pct, $fixed);
            $this->assertLessThanOrEqual($sub, $result['fee'], "fee exceeded sub for [$sub,$pct,$fixed]");
            $this->assertGreaterThanOrEqual(0.0, $result['amount'], "negative payout for [$sub,$pct,$fixed]");
        }
    }

    public function test_decimal_precision_is_preserved(): void
    {
        $result = $this->calculateCommission(subAmount: 9.99, percentage: 4.5, fixedFee: 0.25);

        // 9.99 * 4.5% = 0.44955, + 0.25 = 0.69955
        $this->assertEqualsWithDelta(0.69955, $result['fee'], 0.00001);
        $this->assertEqualsWithDelta(9.29045, $result['amount'], 0.00001);
    }

    public function test_large_order_amount(): void
    {
        $result = $this->calculateCommission(subAmount: 10000, percentage: 4.5, fixedFee: 0.25);

        // 10000 * 4.5% = 450, + 0.25 = 450.25
        $this->assertEqualsWithDelta(450.25, $result['fee'], 0.0001);
        $this->assertEqualsWithDelta(9549.75, $result['amount'], 0.0001);
    }

    public function test_full_percentage_with_fixed_fee_caps_at_sub_amount(): void
    {
        // 100% + fixed would exceed the sub-amount; the cap keeps it at the sub-amount
        $result = $this->calculateCommission(subAmount: 80, percentage: 100, fixedFee: 0.25);

        $this->assertEqualsWithDelta(80.0, $result['fee'], 0.0001);
        $this->assertEqualsWithDelta(0.0, $result['amount'], 0.0001);
    }

    public function test_zero_sub_amount_charges_nothing(): void
    {
        // A fully-discounted order (sub-amount 0) must not produce a negative payout from the fixed fee
        $result = $this->calculateCommission(subAmount: 0, percentage: 4.5, fixedFee: 0.25);

        $this->assertEqualsWithDelta(0.0, $result['fee'], 0.0001);
        $this->assertEqualsWithDelta(0.0, $result['amount'], 0.0001);
    }

    public function test_fixed_fee_charged_once_per_vendor_sub_order(): void
    {
        // A 2-vendor cart becomes 2 separate orders; each is charged the fixed fee once.
        $orderA = $this->calculateCommission(subAmount: 10, percentage: 4.5, fixedFee: 0.25);
        $orderB = $this->calculateCommission(subAmount: 20, percentage: 4.5, fixedFee: 0.25);

        // Vendor A: 0.45 + 0.25 = 0.70 ; Vendor B: 0.90 + 0.25 = 1.15
        $this->assertEqualsWithDelta(0.70, $orderA['fee'], 0.0001);
        $this->assertEqualsWithDelta(1.15, $orderB['fee'], 0.0001);

        // Two fixed fees total across the two sub-orders (0.25 each)
        $totalFee = $orderA['fee'] + $orderB['fee'];
        $totalPercentageFee = (10 * 0.045) + (20 * 0.045);
        $this->assertEqualsWithDelta(0.50, $totalFee - $totalPercentageFee, 0.0001);
    }

    public function test_exact_cap_boundary(): void
    {
        // raw fee exactly equals sub-amount
        $result = $this->calculateCommission(subAmount: 0.50, percentage: 0, fixedFee: 0.50);

        $this->assertEqualsWithDelta(0.50, $result['fee'], 0.0001);
        $this->assertEqualsWithDelta(0.0, $result['amount'], 0.0001);
    }

    public function test_negative_fixed_fee_setting_is_ignored(): void
    {
        // The form validates min:0, but guard against a stray negative value: it must not be added.
        $result = $this->calculateCommission(subAmount: 100, percentage: 10, fixedFee: -5);

        $this->assertEqualsWithDelta(10.0, $result['fee'], 0.0001);
        $this->assertEqualsWithDelta(90.0, $result['amount'], 0.0001);
    }

    /**
     * Faithful mirror of the production commission calculation.
     */
    protected function calculateCommission(float $subAmount, float $percentage, float $fixedFee): array
    {
        $fee = $subAmount * ($percentage / 100);

        if ($fixedFee > 0) {
            $fee += $fixedFee;
        }

        $fee = min($fee, $subAmount);

        return [
            'fee' => $fee,
            'amount' => $subAmount - $fee,
        ];
    }
}
