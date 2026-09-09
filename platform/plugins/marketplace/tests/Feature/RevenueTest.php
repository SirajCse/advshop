<?php

namespace Botble\Marketplace\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Enums\RevenueTypeEnum;
use Botble\Marketplace\Models\Revenue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RevenueTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function createVendor(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_can_create_revenue(): void
    {
        $customer = $this->createVendor();

        $revenue = Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 100,
            'fee' => 10,
            'amount' => 90,
            'current_balance' => 90,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        $this->assertDatabaseHas('mp_customer_revenues', [
            'customer_id' => $customer->id,
            'amount' => 90,
        ]);
    }

    public function test_revenue_type_add_amount(): void
    {
        $customer = $this->createVendor();

        $revenue = Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 100,
            'fee' => 10,
            'amount' => 90,
            'current_balance' => 90,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        $this->assertEquals(RevenueTypeEnum::ADD_AMOUNT, $revenue->type);
    }

    public function test_revenue_type_subtract_amount(): void
    {
        $customer = $this->createVendor();

        $revenue = Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 50,
            'fee' => 0,
            'amount' => 50,
            'current_balance' => 40,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::SUBTRACT_AMOUNT,
        ]);

        $this->assertEquals(RevenueTypeEnum::SUBTRACT_AMOUNT, $revenue->type);
    }

    public function test_revenue_type_order_return(): void
    {
        $customer = $this->createVendor();

        $revenue = Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 100,
            'fee' => 0,
            'amount' => 100,
            'current_balance' => 0,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ORDER_RETURN,
        ]);

        $this->assertEquals(RevenueTypeEnum::ORDER_RETURN, $revenue->type);
    }

    public function test_revenue_belongs_to_customer(): void
    {
        $customer = $this->createVendor();

        $revenue = Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 100,
            'fee' => 10,
            'amount' => 90,
            'current_balance' => 90,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        $this->assertEquals($customer->id, $revenue->customer->id);
        $this->assertEquals('Test Vendor', $revenue->customer->name);
    }

    public function test_revenue_with_description(): void
    {
        $customer = $this->createVendor();

        $revenue = Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 100,
            'fee' => 10,
            'amount' => 90,
            'current_balance' => 90,
            'currency' => 'USD',
            'description' => 'Order #12345 completed',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        $this->assertEquals('Order #12345 completed', $revenue->description);
    }

    public function test_revenue_fee_calculation(): void
    {
        $customer = $this->createVendor();

        $subAmount = 100;
        $feePercentage = 10;
        $fee = $subAmount * $feePercentage / 100;
        $amount = $subAmount - $fee;

        $revenue = Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => $subAmount,
            'fee' => $fee,
            'amount' => $amount,
            'current_balance' => $amount,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        $this->assertEquals(100, $revenue->sub_amount);
        $this->assertEquals(10, $revenue->fee);
        $this->assertEquals(90, $revenue->amount);
    }

    public function test_can_filter_revenues_by_type(): void
    {
        $customer = $this->createVendor();

        Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 100,
            'fee' => 10,
            'amount' => 90,
            'current_balance' => 90,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 50,
            'fee' => 5,
            'amount' => 45,
            'current_balance' => 135,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 30,
            'fee' => 0,
            'amount' => 30,
            'current_balance' => 105,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::SUBTRACT_AMOUNT,
        ]);

        $addRevenues = Revenue::query()->where('type', RevenueTypeEnum::ADD_AMOUNT)->get();
        $subtractRevenues = Revenue::query()->where('type', RevenueTypeEnum::SUBTRACT_AMOUNT)->get();

        $this->assertCount(2, $addRevenues);
        $this->assertCount(1, $subtractRevenues);
    }

    public function test_can_filter_revenues_by_customer(): void
    {
        $customer1 = Customer::query()->create([
            'name' => 'Vendor 1',
            'email' => 'vendor1@example.com',
            'password' => bcrypt('password'),
        ]);

        $customer2 = Customer::query()->create([
            'name' => 'Vendor 2',
            'email' => 'vendor2@example.com',
            'password' => bcrypt('password'),
        ]);

        Revenue::query()->create([
            'customer_id' => $customer1->id,
            'sub_amount' => 100,
            'fee' => 10,
            'amount' => 90,
            'current_balance' => 90,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        Revenue::query()->create([
            'customer_id' => $customer1->id,
            'sub_amount' => 50,
            'fee' => 5,
            'amount' => 45,
            'current_balance' => 135,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        Revenue::query()->create([
            'customer_id' => $customer2->id,
            'sub_amount' => 200,
            'fee' => 20,
            'amount' => 180,
            'current_balance' => 180,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        $customer1Revenues = Revenue::query()->where('customer_id', $customer1->id)->get();
        $customer2Revenues = Revenue::query()->where('customer_id', $customer2->id)->get();

        $this->assertCount(2, $customer1Revenues);
        $this->assertCount(1, $customer2Revenues);
    }

    public function test_revenue_current_balance_tracking(): void
    {
        $customer = $this->createVendor();

        $revenue1 = Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 100,
            'fee' => 10,
            'amount' => 90,
            'current_balance' => 90,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        $revenue2 = Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 50,
            'fee' => 5,
            'amount' => 45,
            'current_balance' => 135,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        $this->assertEquals(90, $revenue1->current_balance);
        $this->assertEquals(135, $revenue2->current_balance);
    }

    public function test_revenue_with_zero_fee(): void
    {
        $customer = $this->createVendor();

        $revenue = Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 100,
            'fee' => 0,
            'amount' => 100,
            'current_balance' => 100,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        $this->assertEquals(0, $revenue->fee);
        $this->assertEquals($revenue->sub_amount, $revenue->amount);
    }

    public function test_can_calculate_total_revenue_for_customer(): void
    {
        $customer = $this->createVendor();

        Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 100,
            'fee' => 10,
            'amount' => 90,
            'current_balance' => 90,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 200,
            'fee' => 20,
            'amount' => 180,
            'current_balance' => 270,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        $totalAmount = Revenue::query()
            ->where('customer_id', $customer->id)
            ->where('type', RevenueTypeEnum::ADD_AMOUNT)
            ->sum('amount');

        $totalFee = Revenue::query()
            ->where('customer_id', $customer->id)
            ->where('type', RevenueTypeEnum::ADD_AMOUNT)
            ->sum('fee');

        $this->assertEquals(270, $totalAmount);
        $this->assertEquals(30, $totalFee);
    }

    public function test_description_tooltip_attribute_empty_when_no_description(): void
    {
        $customer = $this->createVendor();

        $revenue = Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 100,
            'fee' => 10,
            'amount' => 90,
            'current_balance' => 90,
            'currency' => 'USD',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        $this->assertEquals('', $revenue->description_tooltip);
    }

    public function test_description_tooltip_attribute_not_empty_when_has_description(): void
    {
        $customer = $this->createVendor();

        $revenue = Revenue::query()->create([
            'customer_id' => $customer->id,
            'sub_amount' => 100,
            'fee' => 10,
            'amount' => 90,
            'current_balance' => 90,
            'currency' => 'USD',
            'description' => 'Test description',
            'type' => RevenueTypeEnum::ADD_AMOUNT,
        ]);

        $this->assertNotEmpty($revenue->description_tooltip);
        $this->assertStringContainsString('Test description', $revenue->description_tooltip);
    }
}
