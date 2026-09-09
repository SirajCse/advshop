<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Tax\Contracts\TaxCalculatorInterface;
use Botble\Ecommerce\Tax\DTOs\TaxContext;
use Botble\Ecommerce\Tax\DTOs\TaxResult;
use Botble\Ecommerce\Tax\TaxEngineManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaxEngineManagerTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function createMockCalculator(bool $supports = true, float $tax = 10): TaxCalculatorInterface
    {
        return new class ($supports, $tax) implements TaxCalculatorInterface {
            public function __construct(private bool $supports_flag, private float $tax)
            {
            }

            public function calculate(TaxContext $context): TaxResult
            {
                return new TaxResult(total_tax: $this->tax, tax_rate: $this->tax);
            }

            public function supports(TaxContext $context): bool
            {
                return $this->supports_flag;
            }
        };
    }

    public function test_manager_is_singleton(): void
    {
        $manager1 = app(TaxEngineManager::class);
        $manager2 = app(TaxEngineManager::class);

        $this->assertSame($manager1, $manager2);
    }

    public function test_default_calculator_registered(): void
    {
        $manager = app(TaxEngineManager::class);

        $this->assertTrue($manager->has('default'));
    }

    public function test_register_and_retrieve_calculators(): void
    {
        $manager = new TaxEngineManager();
        $calc = $this->createMockCalculator();

        $manager->register('test', $calc);

        $this->assertTrue($manager->has('test'));
        $this->assertArrayHasKey('test', $manager->calculators());
    }

    public function test_higher_priority_calculator_wins(): void
    {
        $manager = new TaxEngineManager();
        $low = $this->createMockCalculator(supports: true, tax: 5);
        $high = $this->createMockCalculator(supports: true, tax: 20);

        $manager->register('low', $low, priority: 0);
        $manager->register('high', $high, priority: 10);

        $product = Product::query()->create([
            'name' => 'Test',
            'price' => 100,
        ]);
        $context = new TaxContext(product: $product);

        $resolved = $manager->resolveCalculator($context);

        $this->assertSame($high, $resolved);
    }

    public function test_unsupported_calculator_skipped(): void
    {
        $manager = new TaxEngineManager();
        $unsupported = $this->createMockCalculator(supports: false, tax: 5);
        $supported = $this->createMockCalculator(supports: true, tax: 20);

        $manager->register('unsupported', $unsupported, priority: 10);
        $manager->register('supported', $supported, priority: 5);

        $product = Product::query()->create([
            'name' => 'Test',
            'price' => 100,
        ]);
        $context = new TaxContext(product: $product);

        $resolved = $manager->resolveCalculator($context);

        $this->assertSame($supported, $resolved);
    }

    public function test_throws_when_no_calculator_supports(): void
    {
        $manager = new TaxEngineManager();
        $manager->register('none', $this->createMockCalculator(supports: false));

        $product = Product::query()->create([
            'name' => 'Test',
            'price' => 100,
        ]);
        $context = new TaxContext(product: $product);

        $this->expectException(\RuntimeException::class);
        $manager->resolveCalculator($context);
    }

    public function test_tax_context_from_array(): void
    {
        $product = Product::query()->create([
            'name' => 'Test',
            'price' => 100,
        ]);

        $context = TaxContext::fromArray([
            'country' => 'US',
            'state' => 'CA',
            'quantity' => 2,
            'price' => 50.0,
        ], $product);

        $this->assertSame($product->id, $context->product->id);
        $this->assertEquals('US', $context->country);
        $this->assertEquals('CA', $context->state);
        $this->assertNull($context->city);
        $this->assertEquals(2, $context->quantity);
        $this->assertEquals(50.0, $context->price);
    }

    public function test_has_returns_false_for_unregistered(): void
    {
        $manager = new TaxEngineManager();

        $this->assertFalse($manager->has('nonexistent'));
    }
}
