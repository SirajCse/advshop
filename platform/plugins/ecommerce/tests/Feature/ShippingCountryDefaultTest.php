<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Shipping;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShippingCountryDefaultTest extends BaseTestCase
{
    use RefreshDatabase;

    protected HandleShippingFeeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::load(true);

        $this->service = new HandleShippingFeeService();
    }

    protected function setSetting(string $key, mixed $value): void
    {
        Setting::load(true);
        Setting::set($key, $value);
        Setting::save();
        Setting::load(true);
    }

    protected function createShippingWithRule(
        ?string $country,
        float $price,
        float $from = 0,
        ?float $to = null
    ): ShippingRule {
        $shipping = Shipping::query()->create([
            'title' => 'Shipping ' . ($country ?: 'Default'),
            'country' => $country,
        ]);

        return ShippingRule::query()->create([
            'name' => 'Rule ' . ($country ?: 'Default'),
            'shipping_id' => $shipping->id,
            'type' => ShippingRuleTypeEnum::BASED_ON_PRICE,
            'price' => $price,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * @return array<float>
     */
    protected function extractPrices(array $result): array
    {
        $prices = [];

        foreach ($result as $options) {
            if (is_array($options)) {
                foreach ($options as $option) {
                    if (isset($option['price'])) {
                        $prices[] = (float) $option['price'];
                    }
                }
            }
        }

        return $prices;
    }

    protected function buildShippingData(string $country, float $orderTotal = 15): array
    {
        return [
            'country' => $country,
            'state' => '',
            'city' => '',
            'weight' => 1,
            'order_total' => $orderTotal,
            'address_to' => [],
            'origin' => [],
            'items' => [],
        ];
    }

    // ========================================
    // getShippingData: country fallback
    // ========================================

    public function test_get_shipping_data_uses_session_country_when_available(): void
    {
        $this->setSetting('ecommerce_available_countries', json_encode(['FR', 'DE', 'US']));

        $session = ['country' => 'FR', 'state' => '', 'city' => ''];

        $data = EcommerceHelper::getShippingData(collect(), $session, [], 50);

        $this->assertEquals('FR', $data['country']);
    }

    public function test_get_shipping_data_falls_back_to_default_country_when_session_empty(): void
    {
        $this->setSetting('ecommerce_available_countries', json_encode(['FR', 'DE', 'US']));
        $this->setSetting('ecommerce_default_country_at_checkout_page', 'FR');

        $session = ['state' => '', 'city' => ''];

        $data = EcommerceHelper::getShippingData(collect(), $session, [], 50);

        $this->assertNotNull($data['country']);
        $this->assertNotEmpty($data['country']);
    }

    public function test_get_shipping_data_falls_back_to_default_when_country_null(): void
    {
        $this->setSetting('ecommerce_available_countries', json_encode(['FR', 'DE', 'US']));
        $this->setSetting('ecommerce_default_country_at_checkout_page', 'FR');

        $session = ['country' => null, 'state' => '', 'city' => ''];

        $data = EcommerceHelper::getShippingData(collect(), $session, [], 50);

        $this->assertNotNull($data['country']);
    }

    public function test_get_shipping_data_falls_back_to_default_when_country_empty_string(): void
    {
        $this->setSetting('ecommerce_available_countries', json_encode(['FR', 'DE', 'US']));
        $this->setSetting('ecommerce_default_country_at_checkout_page', 'FR');

        $session = ['country' => '', 'state' => '', 'city' => ''];

        $data = EcommerceHelper::getShippingData(collect(), $session, [], 50);

        $this->assertNotNull($data['country']);
    }

    // ========================================
    // HandleShippingFeeService: country-specific rules
    // ========================================

    public function test_shipping_fee_returns_country_specific_rules(): void
    {
        $this->createShippingWithRule('FR', 7.90, 0, 49);
        $this->createShippingWithRule('DE', 11.90, 0, 99);

        $result = $this->service->execute($this->buildShippingData('FR'));

        $this->assertNotEmpty($result);

        $prices = $this->extractPrices($result);

        $this->assertContains(7.90, $prices);
        $this->assertNotContains(11.90, $prices);
    }

    public function test_shipping_fee_does_not_mix_countries(): void
    {
        $this->createShippingWithRule('FR', 7.90, 0, 49);
        $this->createShippingWithRule('DE', 11.90, 0, 99);

        $resultFR = $this->service->execute($this->buildShippingData('FR', 15));

        $this->service->clearCache();
        $resultDE = $this->service->execute($this->buildShippingData('DE', 15));

        $pricesFR = $this->extractPrices($resultFR);
        $pricesDE = $this->extractPrices($resultDE);

        $this->assertContains(7.90, $pricesFR);
        $this->assertNotContains(11.90, $pricesFR);

        $this->assertContains(11.90, $pricesDE);
        $this->assertNotContains(7.90, $pricesDE);
    }

    public function test_shipping_fee_with_null_country_uses_default_shipping(): void
    {
        $this->createShippingWithRule('FR', 7.90, 0, 49);
        $this->createShippingWithRule(null, 19.90, 0, 999);

        $result = $this->service->execute($this->buildShippingData(''));

        $prices = $this->extractPrices($result);

        $this->assertContains(19.90, $prices);
        $this->assertNotContains(7.90, $prices);
    }

    public function test_shipping_fee_falls_back_to_default_when_no_country_match(): void
    {
        $this->createShippingWithRule('FR', 7.90, 0, 49);
        $this->createShippingWithRule(null, 5.00, 0, 999);

        $result = $this->service->execute($this->buildShippingData('IT'));

        $prices = $this->extractPrices($result);

        $this->assertContains(5.00, $prices);
    }

    // ========================================
    // PublicUpdateCheckoutController: session country update
    // ========================================

    public function test_checkout_update_saves_country_to_session(): void
    {
        $token = OrderHelper::getOrderSessionToken();

        OrderHelper::setOrderSessionData($token, [
            'state' => '',
            'city' => '',
        ]);

        $sessionBefore = OrderHelper::getOrderSessionData($token);
        $this->assertEmpty($sessionBefore['country'] ?? null);

        $this->postJson(route('public.ajax.checkout.update'), [
            'address' => [
                'country' => 'FR',
            ],
        ]);

        $sessionAfter = OrderHelper::getOrderSessionData($token);
        $this->assertEquals('FR', $sessionAfter['country'] ?? null);
    }

    public function test_checkout_update_changes_country_in_session(): void
    {
        $token = OrderHelper::getOrderSessionToken();

        OrderHelper::setOrderSessionData($token, [
            'country' => 'FR',
            'state' => '',
            'city' => '',
        ]);

        $this->postJson(route('public.ajax.checkout.update'), [
            'address' => [
                'country' => 'DE',
            ],
        ]);

        $sessionAfter = OrderHelper::getOrderSessionData($token);
        $this->assertEquals('DE', $sessionAfter['country'] ?? null);
    }

    public function test_checkout_update_preserves_country_when_not_in_request(): void
    {
        $token = OrderHelper::getOrderSessionToken();

        OrderHelper::setOrderSessionData($token, [
            'country' => 'FR',
            'state' => 'Paris',
            'city' => 'Paris',
        ]);

        $this->postJson(route('public.ajax.checkout.update'));

        $sessionAfter = OrderHelper::getOrderSessionData($token);
        $this->assertEquals('FR', $sessionAfter['country'] ?? null);
    }

    // ========================================
    // PublicUpdateTaxCheckoutController: session country update
    // ========================================

    public function test_tax_update_saves_country_to_session(): void
    {
        $token = OrderHelper::getOrderSessionToken();

        OrderHelper::setOrderSessionData($token, [
            'state' => '',
            'city' => '',
        ]);

        $this->postJson(route('public.ajax.checkout.update-tax'), [
            'address' => [
                'country' => 'DE',
                'state' => '',
                'city' => '',
            ],
        ]);

        $sessionAfter = OrderHelper::getOrderSessionData($token);
        $this->assertEquals('DE', $sessionAfter['country'] ?? null);
    }
}
