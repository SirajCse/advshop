<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Services\HandleCheckoutOrderData;
use Botble\Ecommerce\ValueObjects\CheckoutOrderData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

/**
 * Regression test for the multi-store checkout shipping total bug fixed in
 * commit a0cc10726 (ticket 4553180).
 *
 * The bug: in HandleCheckoutOrderData::execute(), the foreach loop that syncs
 * each per-store created Order reassigned the outer-scope $shippingAmount on
 * every iteration, clobbering the correctly-summed value returned by the
 * marketplace filter (OrderSupportServiceProvider::processShippingDiscountOrderData
 * which plucks+sums shipping_amount across stores). As a result, the checkout
 * summary grand total only reflected the last store's shipping fee.
 *
 * The fix: the inner variable was renamed to $storeShippingAmount so the outer
 * summed value survives the loop and flows into CheckoutOrderData::$shippingAmount.
 *
 * The tests below mirror the three scenarios the customer captured in
 * screenshots on the Ticksy ticket.
 */
class MarketplaceMultiStoreShippingTotalTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        remove_filter(PROCESS_CHECKOUT_ORDER_DATA_ECOMMERCE);

        parent::tearDown();
    }

    /**
     * Customer scenario 1: store 1 free, store 2 = 10 — grand total shipping = 10.
     * This was the only case that looked "correct" before the fix, by coincidence
     * (the last store's shipping happened to equal the correct sum).
     */
    public function test_grand_total_shipping_equals_sum_when_first_store_free_and_second_paid(): void
    {
        $this->runMultiStoreScenario([0.0, 10.0], expectedSum: 10.0);
    }

    /**
     * Customer scenario 2: store 1 = 10, store 2 free — grand total should be 10.
     * Before the fix: grand total showed 0 (last store's shipping = free).
     */
    public function test_grand_total_shipping_equals_sum_when_first_store_paid_and_second_free(): void
    {
        $this->runMultiStoreScenario([10.0, 0.0], expectedSum: 10.0);
    }

    /**
     * Customer scenario 3: store 1 = 10, store 2 = 10 — grand total should be 20.
     * Before the fix: grand total showed 10 (last store's shipping only).
     */
    public function test_grand_total_shipping_equals_sum_when_both_stores_paid(): void
    {
        $this->runMultiStoreScenario([10.0, 10.0], expectedSum: 20.0);
    }

    /**
     * Three stores with mixed shipping — ensures the sum holds for N > 2.
     */
    public function test_grand_total_shipping_equals_sum_across_three_stores(): void
    {
        $this->runMultiStoreScenario([7.5, 0.0, 12.25], expectedSum: 19.75);
    }

    /**
     * Verifies that the fix also preserves each per-store Order's own
     * shipping_amount — i.e. the rename did not accidentally make all stores
     * receive the same summed value.
     */
    public function test_per_store_order_records_keep_their_individual_shipping_amount(): void
    {
        $perStoreShipping = [5.0, 15.0, 2.5];
        $orderIds = $this->createOrdersForStores($perStoreShipping);

        $this->runHandleCheckoutOrderData($perStoreShipping, $orderIds);

        foreach ($orderIds as $index => $orderId) {
            $order = Order::query()->find($orderId);
            $this->assertNotNull($order, "Order {$orderId} missing after execute()");
            $this->assertEqualsWithDelta(
                $perStoreShipping[$index],
                (float) $order->shipping_amount,
                0.01,
                "Store {$index} Order must retain its own shipping_amount, not the aggregate"
            );
        }
    }

    /**
     * Runs the shared assertion: given a per-store shipping array, execute
     * HandleCheckoutOrderData and assert the returned CheckoutOrderData carries
     * the summed grand-total shipping (not the last store's value).
     */
    private function runMultiStoreScenario(array $perStoreShipping, float $expectedSum): void
    {
        $orderIds = $this->createOrdersForStores($perStoreShipping);
        $checkoutData = $this->runHandleCheckoutOrderData($perStoreShipping, $orderIds);

        $this->assertEqualsWithDelta(
            $expectedSum,
            $checkoutData->shippingAmount,
            0.01,
            sprintf(
                'Grand-total shipping must equal the sum across stores (%s). '
                . 'Got %.2f — this indicates the inner loop is clobbering the outer sum.',
                implode(' + ', $perStoreShipping),
                $checkoutData->shippingAmount
            )
        );
    }

    /**
     * Creates one Order record per store with the given per-store shipping,
     * matching what processShippingDiscountOrderData would produce when
     * per-vendor shipping is enabled. Returns the list of created_order_ids.
     */
    private function createOrdersForStores(array $perStoreShipping): array
    {
        $orderIds = [];

        foreach ($perStoreShipping as $shippingAmount) {
            $subTotal = 50.0;
            $order = Order::query()->create([
                'amount' => $subTotal + $shippingAmount,
                'sub_total' => $subTotal,
                'tax_amount' => 0,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => 0,
                'payment_fee' => 0,
                'status' => OrderStatusEnum::PENDING,
                'shipping_method' => ShippingMethodEnum::DEFAULT,
                'is_finished' => false,
            ]);
            $orderIds[] = $order->id;
        }

        return $orderIds;
    }

    /**
     * Wires up a deterministic marketplace filter, seeds the checkout session
     * with per-store data, and invokes HandleCheckoutOrderData::execute().
     *
     * The filter replaces the real OrderSupportServiceProvider listener for the
     * duration of the test so we can drive the loop without a full cart and
     * shipping-method config — which is irrelevant to the variable-scoping
     * invariant we are regression-testing.
     */
    private function runHandleCheckoutOrderData(array $perStoreShipping, array $orderIds): CheckoutOrderData
    {
        $marketplaceSessionData = [];
        foreach ($perStoreShipping as $index => $shippingAmount) {
            $storeId = $index + 1;
            $marketplaceSessionData[$storeId] = [
                'shipping_amount' => $shippingAmount,
                'shipping_option' => 'default',
                'shipping_method' => ShippingMethodEnum::DEFAULT,
                'created_order_id' => $orderIds[$index],
                'is_available_shipping' => true,
                'promotion_discount_amount' => 0,
                'coupon_discount_amount' => 0,
                'shipping' => [],
            ];
        }

        $token = OrderHelper::getOrderSessionToken();
        $sessionCheckoutData = ['marketplace' => $marketplaceSessionData];
        OrderHelper::setOrderSessionData($token, $sessionCheckoutData);

        $summedShippingAmount = array_sum($perStoreShipping);

        // Stub the marketplace filter so execute() receives a pre-summed value,
        // matching what the real processShippingDiscountOrderData returns for
        // per-vendor shipping mode (OrderSupportServiceProvider.php:980).
        remove_filter(PROCESS_CHECKOUT_ORDER_DATA_ECOMMERCE);
        add_filter(
            PROCESS_CHECKOUT_ORDER_DATA_ECOMMERCE,
            function ($products, $token, $sessionCheckoutData, $request) use ($summedShippingAmount) {
                return [
                    $sessionCheckoutData,
                    [],
                    ShippingMethodEnum::DEFAULT,
                    'default',
                    $summedShippingAmount,
                    0,
                    0,
                ];
            },
            100,
            4
        );

        $service = app(HandleCheckoutOrderData::class);
        $request = Request::create('/checkout', 'POST');

        return $service->execute($request, new Collection(), $token, $sessionCheckoutData);
    }
}
