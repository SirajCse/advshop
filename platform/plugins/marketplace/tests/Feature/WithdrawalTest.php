<?php

namespace Botble\Marketplace\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Enums\WithdrawalStatusEnum;
use Botble\Marketplace\Models\VendorInfo;
use Botble\Marketplace\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WithdrawalTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function createVendorWithBalance(float $balance = 1000): array
    {
        $customer = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        $vendorInfo = VendorInfo::query()->create([
            'customer_id' => $customer->id,
            'balance' => $balance,
            'total_fee' => 0,
            'total_revenue' => $balance,
        ]);

        return [$customer, $vendorInfo];
    }

    public function test_can_create_withdrawal(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 10,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::PENDING,
        ]);

        $this->assertDatabaseHas('mp_customer_withdrawals', [
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 10,
        ]);
    }

    public function test_withdrawal_status_pending(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::PENDING,
        ]);

        $this->assertEquals(WithdrawalStatusEnum::PENDING, $withdrawal->status);
    }

    public function test_withdrawal_status_processing(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::PROCESSING,
        ]);

        $this->assertEquals(WithdrawalStatusEnum::PROCESSING, $withdrawal->status);
    }

    public function test_withdrawal_status_completed(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::COMPLETED,
        ]);

        $this->assertEquals(WithdrawalStatusEnum::COMPLETED, $withdrawal->status);
    }

    public function test_withdrawal_status_canceled(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::CANCELED,
        ]);

        $this->assertEquals(WithdrawalStatusEnum::CANCELED, $withdrawal->status);
    }

    public function test_withdrawal_status_refused(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::REFUSED,
        ]);

        $this->assertEquals(WithdrawalStatusEnum::REFUSED, $withdrawal->status);
    }

    public function test_withdrawal_belongs_to_customer(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::PENDING,
        ]);

        $this->assertEquals($customer->id, $withdrawal->customer->id);
        $this->assertEquals('Test Vendor', $withdrawal->customer->name);
    }

    public function test_withdrawal_with_fee(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 25,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::PENDING,
        ]);

        $this->assertEquals(500, $withdrawal->amount);
        $this->assertEquals(25, $withdrawal->fee);
    }

    public function test_withdrawal_with_description(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'description' => 'Weekly payout request',
            'status' => WithdrawalStatusEnum::PENDING,
        ]);

        $this->assertEquals('Weekly payout request', $withdrawal->description);
    }

    public function test_withdrawal_with_bank_info(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $bankInfo = [
            'bank_name' => 'Test Bank',
            'account_number' => '1234567890',
            'account_holder' => 'Test Vendor',
        ];

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'bank_info' => $bankInfo,
            'status' => WithdrawalStatusEnum::PENDING,
        ]);

        $this->assertEquals('Test Bank', $withdrawal->bank_info['bank_name']);
        $this->assertEquals('1234567890', $withdrawal->bank_info['account_number']);
    }

    public function test_vendor_can_edit_pending_withdrawal(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::PENDING,
        ]);

        $this->assertTrue($withdrawal->vendor_can_edit);
    }

    public function test_vendor_cannot_edit_processing_withdrawal(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::PROCESSING,
        ]);

        $this->assertFalse($withdrawal->vendor_can_edit);
    }

    public function test_vendor_cannot_edit_completed_withdrawal(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::COMPLETED,
        ]);

        $this->assertFalse($withdrawal->vendor_can_edit);
    }

    public function test_can_edit_status_for_pending_withdrawal(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::PENDING,
        ]);

        $this->assertTrue($withdrawal->canEditStatus());
    }

    public function test_can_edit_status_for_processing_withdrawal(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::PROCESSING,
        ]);

        $this->assertTrue($withdrawal->canEditStatus());
    }

    public function test_cannot_edit_status_for_completed_withdrawal(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::COMPLETED,
        ]);

        $this->assertFalse($withdrawal->canEditStatus());
    }

    public function test_cannot_edit_status_for_canceled_withdrawal(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::CANCELED,
        ]);

        $this->assertFalse($withdrawal->canEditStatus());
    }

    public function test_get_next_statuses_for_pending(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::PENDING,
        ]);

        $nextStatuses = $withdrawal->getNextStatuses();

        $this->assertArrayHasKey(WithdrawalStatusEnum::PENDING, $nextStatuses);
        $this->assertArrayHasKey(WithdrawalStatusEnum::PROCESSING, $nextStatuses);
        $this->assertArrayHasKey(WithdrawalStatusEnum::CANCELED, $nextStatuses);
        $this->assertArrayHasKey(WithdrawalStatusEnum::REFUSED, $nextStatuses);
        $this->assertArrayNotHasKey(WithdrawalStatusEnum::COMPLETED, $nextStatuses);
    }

    public function test_get_next_statuses_for_processing(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::PROCESSING,
        ]);

        $nextStatuses = $withdrawal->getNextStatuses();

        $this->assertArrayHasKey(WithdrawalStatusEnum::PROCESSING, $nextStatuses);
        $this->assertArrayHasKey(WithdrawalStatusEnum::COMPLETED, $nextStatuses);
        $this->assertArrayHasKey(WithdrawalStatusEnum::CANCELED, $nextStatuses);
        $this->assertArrayHasKey(WithdrawalStatusEnum::REFUSED, $nextStatuses);
        $this->assertArrayNotHasKey(WithdrawalStatusEnum::PENDING, $nextStatuses);
    }

    public function test_can_filter_withdrawals_by_status(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(5000);

        Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 100,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::PENDING,
        ]);

        Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 200,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::PROCESSING,
        ]);

        Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 300,
            'fee' => 0,
            'currency' => 'USD',
            'status' => WithdrawalStatusEnum::COMPLETED,
        ]);

        $pendingWithdrawals = Withdrawal::query()->where('status', WithdrawalStatusEnum::PENDING)->get();
        $processingWithdrawals = Withdrawal::query()->where('status', WithdrawalStatusEnum::PROCESSING)->get();
        $completedWithdrawals = Withdrawal::query()->where('status', WithdrawalStatusEnum::COMPLETED)->get();

        $this->assertCount(1, $pendingWithdrawals);
        $this->assertCount(1, $processingWithdrawals);
        $this->assertCount(1, $completedWithdrawals);
    }

    public function test_withdrawal_with_transaction_id(): void
    {
        [$customer, $vendorInfo] = $this->createVendorWithBalance(1000);

        $withdrawal = Withdrawal::query()->create([
            'customer_id' => $customer->id,
            'amount' => 500,
            'fee' => 0,
            'currency' => 'USD',
            'transaction_id' => 'TXN123456789',
            'status' => WithdrawalStatusEnum::COMPLETED,
        ]);

        $this->assertEquals('TXN123456789', $withdrawal->transaction_id);
    }
}
