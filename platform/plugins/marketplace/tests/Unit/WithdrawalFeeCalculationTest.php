<?php

namespace Botble\Marketplace\Tests\Unit;

use Botble\Marketplace\Enums\WithdrawalFeeTypeEnum;
use PHPUnit\Framework\TestCase;

class WithdrawalFeeCalculationTest extends TestCase
{
    public function test_fixed_fee_calculation(): void
    {
        $amount = 1000;
        $feeType = WithdrawalFeeTypeEnum::FIXED;
        $feeValue = 5;

        $fee = $this->calculateWithdrawalFee($amount, $feeType, $feeValue);

        $this->assertEquals(5, $fee);
    }

    public function test_percentage_fee_calculation(): void
    {
        $amount = 1000;
        $feeType = WithdrawalFeeTypeEnum::PERCENTAGE;
        $feeValue = 2.5;

        $fee = $this->calculateWithdrawalFee($amount, $feeType, $feeValue);

        $this->assertEquals(25, $fee);
    }

    public function test_percentage_fee_with_small_amount(): void
    {
        $amount = 100;
        $feeType = WithdrawalFeeTypeEnum::PERCENTAGE;
        $feeValue = 5;

        $fee = $this->calculateWithdrawalFee($amount, $feeType, $feeValue);

        $this->assertEquals(5, $fee);
    }

    public function test_fixed_fee_greater_than_amount(): void
    {
        $amount = 10;
        $feeType = WithdrawalFeeTypeEnum::FIXED;
        $feeValue = 15;

        $fee = $this->calculateWithdrawalFee($amount, $feeType, $feeValue);
        $netAmount = max(0, $amount - $fee);

        $this->assertEquals(15, $fee);
        $this->assertEquals(0, $netAmount);
    }

    public function test_zero_fee(): void
    {
        $amount = 1000;
        $feeType = WithdrawalFeeTypeEnum::FIXED;
        $feeValue = 0;

        $fee = $this->calculateWithdrawalFee($amount, $feeType, $feeValue);

        $this->assertEquals(0, $fee);
    }

    public function test_net_amount_after_fixed_fee(): void
    {
        $amount = 1000;
        $feeType = WithdrawalFeeTypeEnum::FIXED;
        $feeValue = 50;

        $fee = $this->calculateWithdrawalFee($amount, $feeType, $feeValue);
        $netAmount = $amount - $fee;

        $this->assertEquals(50, $fee);
        $this->assertEquals(950, $netAmount);
    }

    public function test_net_amount_after_percentage_fee(): void
    {
        $amount = 1000;
        $feeType = WithdrawalFeeTypeEnum::PERCENTAGE;
        $feeValue = 10;

        $fee = $this->calculateWithdrawalFee($amount, $feeType, $feeValue);
        $netAmount = $amount - $fee;

        $this->assertEquals(100, $fee);
        $this->assertEquals(900, $netAmount);
    }

    public function test_percentage_fee_with_decimal_result(): void
    {
        $amount = 333;
        $feeType = WithdrawalFeeTypeEnum::PERCENTAGE;
        $feeValue = 2.5;

        $fee = $this->calculateWithdrawalFee($amount, $feeType, $feeValue);

        $this->assertEqualsWithDelta(8.325, $fee, 0.001);
    }

    public function test_100_percent_fee(): void
    {
        $amount = 1000;
        $feeType = WithdrawalFeeTypeEnum::PERCENTAGE;
        $feeValue = 100;

        $fee = $this->calculateWithdrawalFee($amount, $feeType, $feeValue);
        $netAmount = $amount - $fee;

        $this->assertEquals(1000, $fee);
        $this->assertEquals(0, $netAmount);
    }

    public function test_minimum_withdrawal_amount_check(): void
    {
        $minimumWithdrawalAmount = 50;
        $requestedAmount = 30;

        $canWithdraw = $this->canWithdraw($requestedAmount, $minimumWithdrawalAmount, 1000);

        $this->assertFalse($canWithdraw);
    }

    public function test_can_withdraw_with_sufficient_balance(): void
    {
        $minimumWithdrawalAmount = 50;
        $requestedAmount = 100;
        $balance = 1000;

        $canWithdraw = $this->canWithdraw($requestedAmount, $minimumWithdrawalAmount, $balance);

        $this->assertTrue($canWithdraw);
    }

    public function test_cannot_withdraw_more_than_balance(): void
    {
        $minimumWithdrawalAmount = 50;
        $requestedAmount = 1500;
        $balance = 1000;

        $canWithdraw = $this->canWithdraw($requestedAmount, $minimumWithdrawalAmount, $balance);

        $this->assertFalse($canWithdraw);
    }

    public function test_commission_fee_calculation_for_order(): void
    {
        $orderAmount = 1000;
        $commissionPercentage = 15;

        $commissionFee = $this->calculateCommissionFee($orderAmount, $commissionPercentage);
        $vendorEarnings = $orderAmount - $commissionFee;

        $this->assertEquals(150, $commissionFee);
        $this->assertEquals(850, $vendorEarnings);
    }

    public function test_commission_fee_with_zero_percentage(): void
    {
        $orderAmount = 1000;
        $commissionPercentage = 0;

        $commissionFee = $this->calculateCommissionFee($orderAmount, $commissionPercentage);
        $vendorEarnings = $orderAmount - $commissionFee;

        $this->assertEquals(0, $commissionFee);
        $this->assertEquals(1000, $vendorEarnings);
    }

    public function test_commission_fee_with_decimal_amount(): void
    {
        $orderAmount = 99.99;
        $commissionPercentage = 10;

        $commissionFee = $this->calculateCommissionFee($orderAmount, $commissionPercentage);

        $this->assertEqualsWithDelta(9.999, $commissionFee, 0.001);
    }

    public function test_balance_update_after_withdrawal(): void
    {
        $currentBalance = 1000;
        $withdrawalAmount = 300;
        $withdrawalFee = 10;

        $newBalance = $this->calculateBalanceAfterWithdrawal($currentBalance, $withdrawalAmount, $withdrawalFee);

        $this->assertEquals(690, $newBalance);
    }

    public function test_balance_update_after_revenue_added(): void
    {
        $currentBalance = 500;
        $orderAmount = 200;
        $commissionFee = 30;

        $newBalance = $this->calculateBalanceAfterRevenue($currentBalance, $orderAmount, $commissionFee);

        $this->assertEquals(670, $newBalance);
    }

    public function test_balance_cannot_go_negative(): void
    {
        $currentBalance = 100;
        $withdrawalAmount = 150;
        $withdrawalFee = 10;

        $newBalance = $this->calculateBalanceAfterWithdrawal($currentBalance, $withdrawalAmount, $withdrawalFee);

        $this->assertEquals(max(0, $newBalance), 0);
    }

    protected function calculateWithdrawalFee(float $amount, string $feeType, float $feeValue): float
    {
        if ($feeType === WithdrawalFeeTypeEnum::FIXED) {
            return $feeValue;
        }

        return ($amount * $feeValue) / 100;
    }

    protected function canWithdraw(float $requestedAmount, float $minimumAmount, float $balance): bool
    {
        if ($requestedAmount < $minimumAmount) {
            return false;
        }

        return $requestedAmount <= $balance;
    }

    protected function calculateCommissionFee(float $orderAmount, float $commissionPercentage): float
    {
        return ($orderAmount * $commissionPercentage) / 100;
    }

    protected function calculateBalanceAfterWithdrawal(float $currentBalance, float $withdrawalAmount, float $withdrawalFee): float
    {
        return $currentBalance - $withdrawalAmount - $withdrawalFee;
    }

    protected function calculateBalanceAfterRevenue(float $currentBalance, float $orderAmount, float $commissionFee): float
    {
        return $currentBalance + $orderAmount - $commissionFee;
    }
}
