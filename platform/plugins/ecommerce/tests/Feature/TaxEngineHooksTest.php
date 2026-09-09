<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Tax\Contracts\TaxCalculatorInterface;
use Botble\Ecommerce\Tax\DTOs\TaxComponent;
use Botble\Ecommerce\Tax\DTOs\TaxContext;
use Botble\Ecommerce\Tax\DTOs\TaxResult;
use Botble\Ecommerce\Tax\TaxEngineManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaxEngineHooksTest extends BaseTestCase
{
    use RefreshDatabase;

    protected TaxEngineManager $engine;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->engine = app(TaxEngineManager::class);

        $tax = Tax::query()->create([
            'title' => 'Standard Tax',
            'percentage' => 10,
            'priority' => 1,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
        ]);

        $this->product->taxes()->attach($tax->id);
        $this->product->load('taxes.rules');
    }

    public function test_custom_calculator_overrides_default(): void
    {
        $custom = new class () implements TaxCalculatorInterface {
            public function calculate(TaxContext $context): TaxResult
            {
                return new TaxResult(total_tax: 99, tax_rate: 99);
            }

            public function supports(TaxContext $context): bool
            {
                return true;
            }
        };

        $this->engine->register('custom', $custom, priority: 100);

        $context = new TaxContext(product: $this->product, quantity: 1, price: 100);
        $result = $this->engine->calculate($context);

        $this->assertEquals(99, $result->tax_rate);
    }

    public function test_unsupported_custom_calculator_falls_to_default(): void
    {
        $custom = new class () implements TaxCalculatorInterface {
            public function calculate(TaxContext $context): TaxResult
            {
                return new TaxResult(total_tax: 99, tax_rate: 99);
            }

            public function supports(TaxContext $context): bool
            {
                return false;
            }
        };

        $this->engine->register('custom', $custom, priority: 100);

        $context = new TaxContext(product: $this->product, quantity: 1, price: 100);
        $result = $this->engine->calculate($context);

        $this->assertEquals(10, $result->tax_rate);
    }

    public function test_context_build_filter_enriches_context(): void
    {
        $capturedContext = null;

        add_filter('ecommerce_tax_context_build', function ($context) use (&$capturedContext) {
            if ($context instanceof TaxContext) {
                $capturedContext = $context->withSellerLocation('IN', 'MH');
            }

            return $capturedContext;
        }, 99);

        $context = new TaxContext(product: $this->product, quantity: 1, price: 100);
        $this->engine->calculate($context);

        $this->assertNotNull($capturedContext);
        $this->assertEquals('IN', $capturedContext->seller_country);
        $this->assertEquals('MH', $capturedContext->seller_state);
    }

    public function test_tax_result_filter_modifies_result(): void
    {
        add_filter('ecommerce_tax_result', function ($result) {
            if ($result instanceof TaxResult) {
                return new TaxResult(
                    total_tax: $result->total_tax * 2,
                    tax_rate: $result->tax_rate,
                    components: $result->components,
                );
            }

            return $result;
        }, 99);

        $context = new TaxContext(product: $this->product, quantity: 1, price: 100);
        $result = $this->engine->calculate($context);

        $this->assertEquals(20, $result->total_tax);
    }

    public function test_exemption_check_returns_zero_tax(): void
    {
        add_filter('ecommerce_tax_exemption_check', function ($exempt, $product, $context) {
            return true;
        }, 99, 3);

        $context = new TaxContext(product: $this->product, quantity: 1, price: 100);
        $result = $this->engine->calculate($context);

        $this->assertEquals(0, $result->total_tax);
        $this->assertEquals(0, $result->tax_rate);
    }

    public function test_tax_rate_for_product_filter_overrides_rate(): void
    {
        add_filter('ecommerce_tax_rate_for_product', function ($rate) {
            return 25.0;
        }, 99);

        $context = new TaxContext(product: $this->product, quantity: 1, price: 100);
        $result = $this->engine->calculate($context);

        $this->assertEquals(25.0, $result->tax_rate);
        $this->assertEquals(25.0, $result->total_tax);
    }

    public function test_tax_components_filter_replaces_components(): void
    {
        add_filter('ecommerce_tax_components', function ($components, $product, $context) {
            return [
                new TaxComponent(name: 'CGST', code: 'cgst', rate: 9, amount: 9),
                new TaxComponent(name: 'SGST', code: 'sgst', rate: 9, amount: 9),
            ];
        }, 99, 3);

        $context = new TaxContext(product: $this->product, quantity: 1, price: 100);
        $result = $this->engine->calculate($context);

        $this->assertCount(2, $result->components);
        $this->assertEquals('CGST', $result->components[0]->name);
        $this->assertEquals('SGST', $result->components[1]->name);
    }

    public function test_tax_calculated_action_fires(): void
    {
        $actionCalled = false;

        add_action('ecommerce_tax_calculated', function ($result, $context) use (&$actionCalled): void {
            $actionCalled = true;
        }, 99, 2);

        $context = new TaxContext(product: $this->product, quantity: 1, price: 100);
        $this->engine->calculate($context);

        $this->assertTrue($actionCalled);
    }
}
