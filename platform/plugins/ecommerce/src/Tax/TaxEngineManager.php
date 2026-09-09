<?php

namespace Botble\Ecommerce\Tax;

use Botble\Ecommerce\Tax\Contracts\TaxCalculatorInterface;
use Botble\Ecommerce\Tax\DTOs\TaxContext;
use Botble\Ecommerce\Tax\DTOs\TaxResult;

class TaxEngineManager
{
    /**
     * @var array<string, array{calculator: TaxCalculatorInterface, priority: int}>
     */
    protected array $calculators = [];

    protected bool $sorted = false;

    public function register(string $name, TaxCalculatorInterface $calculator, int $priority = 0): void
    {
        $this->calculators[$name] = [
            'calculator' => $calculator,
            'priority' => $priority,
        ];

        $this->sorted = false;
    }

    public function calculate(TaxContext $context): TaxResult
    {
        $filtered_context = apply_filters('ecommerce_tax_context_build', $context, $context->product);

        if ($filtered_context instanceof TaxContext) {
            $context = $filtered_context;
        }

        $calculator = $this->resolveCalculator($context);

        $result = $calculator->calculate($context);

        $filtered_result = apply_filters('ecommerce_tax_result', $result, $context);

        if ($filtered_result instanceof TaxResult) {
            $result = $filtered_result;
        }

        do_action('ecommerce_tax_calculated', $result, $context);

        return $result;
    }

    public function resolveCalculator(TaxContext $context): TaxCalculatorInterface
    {
        $this->sortCalculators();

        foreach ($this->calculators as $entry) {
            if ($entry['calculator']->supports($context)) {
                return $entry['calculator'];
            }
        }

        throw new \RuntimeException('No tax calculator supports the given context. Ensure DefaultTaxCalculator is registered.');
    }

    /**
     * @return array<string, TaxCalculatorInterface>
     */
    public function calculators(): array
    {
        return array_map(fn (array $entry) => $entry['calculator'], $this->calculators);
    }

    public function has(string $name): bool
    {
        return isset($this->calculators[$name]);
    }

    protected function sortCalculators(): void
    {
        if ($this->sorted) {
            return;
        }

        uasort($this->calculators, fn (array $a, array $b) => $b['priority'] <=> $a['priority']);

        $this->sorted = true;
    }
}
