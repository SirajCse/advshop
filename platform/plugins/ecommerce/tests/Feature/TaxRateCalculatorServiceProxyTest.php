<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Models\TaxRule;
use Botble\Ecommerce\Services\TaxRateCalculatorService;
use Botble\Ecommerce\Tax\DTOs\TaxContext;
use Botble\Ecommerce\Tax\TaxEngineManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaxRateCalculatorServiceProxyTest extends BaseTestCase
{
    use RefreshDatabase;

    protected TaxRateCalculatorService $proxy;

    protected TaxEngineManager $engine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->proxy = app(TaxRateCalculatorService::class);
        $this->engine = app(TaxEngineManager::class);
    }

    public function test_proxy_returns_same_rate_as_engine(): void
    {
        $tax = Tax::query()->create([
            'title' => 'VAT',
            'percentage' => 10,
            'priority' => 1,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
        ]);

        $product->taxes()->attach($tax->id);
        $product->load('taxes.rules');

        $proxyRate = $this->proxy->execute($product, 'US', 'CA');

        $context = new TaxContext(
            product: $product,
            country: 'US',
            state: 'CA',
            quantity: 1,
            price: $product->price,
        );
        $engineRate = $this->engine->calculate($context)->tax_rate;

        $this->assertEquals($engineRate, $proxyRate);
    }

    public function test_proxy_with_zip_code_rule(): void
    {
        $tax = Tax::query()->create([
            'title' => 'Sales Tax',
            'percentage' => 8,
            'priority' => 1,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        TaxRule::query()->create([
            'tax_id' => $tax->id,
            'zip_code' => '90210',
            'percentage' => 9.5,
        ]);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 50,
        ]);

        $product->taxes()->attach($tax->id);
        $product->load('taxes.rules');

        $rate = $this->proxy->execute($product, 'US', 'CA', null, '90210');

        $this->assertEquals(9.5, $rate);
    }

    public function test_proxy_returns_zero_when_no_tax(): void
    {
        $product = Product::query()->create([
            'name' => 'No Tax Product',
            'price' => 100,
        ]);

        $rate = $this->proxy->execute($product, 'US');

        $this->assertEquals(0.0, $rate);
    }

    public function test_proxy_fires_ecommerce_tax_rate_calculated_filter(): void
    {
        $filterCalled = false;

        add_filter('ecommerce_tax_rate_calculated', function ($rate) use (&$filterCalled) {
            $filterCalled = true;

            return $rate;
        }, 99);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
        ]);

        $this->proxy->execute($product, 'US');

        $this->assertTrue($filterCalled);
    }

    public function test_proxy_with_country_state_city_rule(): void
    {
        $tax = Tax::query()->create([
            'title' => 'State Tax',
            'percentage' => 5,
            'priority' => 1,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        TaxRule::query()->create([
            'tax_id' => $tax->id,
            'country' => 'US',
            'state' => 'CA',
            'city' => 'Los Angeles',
            'percentage' => 9.5,
        ]);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
        ]);

        $product->taxes()->attach($tax->id);
        $product->load('taxes.rules');

        $rate = $this->proxy->execute($product, 'US', 'CA', 'Los Angeles');

        $this->assertEquals(9.5, $rate);
    }

    public function test_proxy_with_multiple_taxes(): void
    {
        $tax1 = Tax::query()->create([
            'title' => 'Tax 1',
            'percentage' => 5,
            'priority' => 1,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $tax2 = Tax::query()->create([
            'title' => 'Tax 2',
            'percentage' => 3,
            'priority' => 2,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
        ]);

        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->load('taxes.rules');

        $rate = $this->proxy->execute($product);

        $this->assertEquals(8.0, $rate);
    }
}
