<?php

namespace Botble\Marketplace\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Models\Currency;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Enums\StoreStatusEnum;
use Botble\Marketplace\Models\Revenue;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Providers\OrderSupportServiceProvider;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Drives the real OrderSupportServiceProvider::afterOrderStatusCompleted() so the fixed
 * commission fee is verified against the production code path, not just a mirrored formula.
 */
class FixedCommissionFeeTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // get_application_currency()->title is read while writing the Revenue row.
        Currency::query()->create([
            'title' => 'USD',
            'symbol' => '$',
            'is_prefix_symbol' => true,
            'exchange_rate' => 1,
            'is_default' => true,
            'order' => 0,
        ]);
    }

    protected function setSetting(string $key, mixed $value): void
    {
        // Mirror the proven ecommerce test pattern: reload forces a fresh read and
        // prevents the in-memory setting cache from leaking between tests.
        Setting::load(true);
        Setting::set($key, $value);
        Setting::save();
        Setting::load(true);
    }

    protected function setCommission(float $percentage, float $fixed): void
    {
        $this->setSetting('marketplace_enable_commission_fee_for_each_category', 0);
        $this->setSetting('marketplace_fee_per_order', $percentage);
        $this->setSetting('marketplace_fee_per_order_fixed', $fixed);
    }

    protected function createVendorStore(): Store
    {
        $customer = Customer::query()->create([
            'name' => 'Commission Vendor',
            'email' => 'commission-vendor-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'is_vendor' => true,
        ]);

        return Store::query()->create([
            'name' => 'Commission Store',
            'email' => 'commission-store-' . uniqid() . '@example.com',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
        ]);
    }

    protected function createOrderForStore(Store $store, float $amount): Order
    {
        $order = Order::query()->create([
            'user_id' => 0,
            'amount' => $amount,
            'sub_total' => $amount,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'shipping_tax_amount' => 0,
            'payment_fee' => 0,
            'discount_amount' => 0,
            'status' => OrderStatusEnum::COMPLETED,
            'is_finished' => true,
        ]);

        // store_id is a marketplace-added column and is not in Order's $fillable,
        // so assign it directly (mass-assignment would silently drop it).
        $order->store_id = $store->id;
        $order->save();

        return $order;
    }

    protected function completeOrder(Order $order): void
    {
        (new OrderSupportServiceProvider($this->app))->afterOrderStatusCompleted($order);
    }

    public function test_percentage_plus_fixed_fee_is_applied_to_revenue(): void
    {
        $this->setCommission(percentage: 4.5, fixed: 0.25);
        $store = $this->createVendorStore();
        $order = $this->createOrderForStore($store, amount: 10);

        $this->completeOrder($order);

        $revenue = Revenue::query()->where('order_id', $order->id)->first();

        $this->assertNotNull($revenue);
        // 10 * 4.5% = 0.45, + 0.25 fixed = 0.70 commission
        $this->assertEqualsWithDelta(0.70, (float) $revenue->fee, 0.0001);
        $this->assertEqualsWithDelta(9.30, (float) $revenue->amount, 0.0001);
        $this->assertEqualsWithDelta(10.0, (float) $revenue->sub_amount, 0.0001);
    }

    public function test_zero_fixed_fee_is_backward_compatible(): void
    {
        $this->setCommission(percentage: 4.5, fixed: 0);
        $store = $this->createVendorStore();
        $order = $this->createOrderForStore($store, amount: 10);

        $this->completeOrder($order);

        $revenue = Revenue::query()->where('order_id', $order->id)->first();

        $this->assertNotNull($revenue);
        // Pure percentage: 10 * 4.5% = 0.45
        $this->assertEqualsWithDelta(0.45, (float) $revenue->fee, 0.0001);
        $this->assertEqualsWithDelta(9.55, (float) $revenue->amount, 0.0001);
    }

    public function test_fixed_fee_is_capped_so_vendor_payout_is_not_negative(): void
    {
        $this->setCommission(percentage: 4.5, fixed: 0.25);
        $store = $this->createVendorStore();
        // Sub-amount (0.20) smaller than the fixed fee
        $order = $this->createOrderForStore($store, amount: 0.20);

        $this->completeOrder($order);

        $revenue = Revenue::query()->where('order_id', $order->id)->first();

        $this->assertNotNull($revenue);
        $this->assertEqualsWithDelta(0.20, (float) $revenue->fee, 0.0001);
        $this->assertEqualsWithDelta(0.0, (float) $revenue->amount, 0.0001);
        $this->assertGreaterThanOrEqual(0.0, (float) $revenue->amount);
    }

    public function test_vendor_balance_reflects_fixed_fee(): void
    {
        $this->setCommission(percentage: 4.5, fixed: 0.25);
        $store = $this->createVendorStore();
        $order = $this->createOrderForStore($store, amount: 100);

        $this->completeOrder($order);

        $vendorInfo = $store->customer->refresh()->vendorInfo;

        // 100 * 4.5% = 4.50, + 0.25 = 4.75 fee; vendor earns 95.25
        $this->assertEqualsWithDelta(4.75, (float) $vendorInfo->total_fee, 0.0001);
        $this->assertEqualsWithDelta(95.25, (float) $vendorInfo->balance, 0.0001);
    }

    public function test_fixed_fee_charged_once_per_vendor_order(): void
    {
        $this->setCommission(percentage: 4.5, fixed: 0.25);
        $store = $this->createVendorStore();

        // Two separate vendor orders (as a multi-vendor cart would produce)
        $orderA = $this->createOrderForStore($store, amount: 10);
        $orderB = $this->createOrderForStore($store, amount: 20);

        $this->completeOrder($orderA);
        $this->completeOrder($orderB);

        $feeA = (float) Revenue::query()->where('order_id', $orderA->id)->value('fee');
        $feeB = (float) Revenue::query()->where('order_id', $orderB->id)->value('fee');

        // 0.45+0.25 and 0.90+0.25 — the fixed 0.25 applied once to each order
        $this->assertEqualsWithDelta(0.70, $feeA, 0.0001);
        $this->assertEqualsWithDelta(1.15, $feeB, 0.0001);
    }
}
