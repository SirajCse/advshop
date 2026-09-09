<?php

namespace Botble\Ecommerce\Tax;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Tax\Contracts\TaxCalculatorInterface;
use Botble\Ecommerce\Tax\DTOs\TaxComponent;
use Botble\Ecommerce\Tax\DTOs\TaxContext;
use Botble\Ecommerce\Tax\DTOs\TaxResult;

class DefaultTaxCalculator implements TaxCalculatorInterface
{
    public function calculate(TaxContext $context): TaxResult
    {
        $is_exempt = apply_filters('ecommerce_tax_exemption_check', false, $context->product, $context);

        if ($is_exempt) {
            return TaxResult::zero();
        }

        $tax_rate = $this->resolveRate($context);

        $tax_rate = (float) apply_filters('ecommerce_tax_rate_for_product', $tax_rate, $context->product, $context);

        $amount = EcommerceHelper::roundPrice(
            $context->quantity * ($context->price * $tax_rate / 100)
        );

        $components = [
            new TaxComponent(
                name: 'Tax',
                code: 'tax',
                rate: $tax_rate,
                amount: $amount,
            ),
        ];

        /** @var TaxComponent[] $components */
        $components = apply_filters('ecommerce_tax_components', $components, $context->product, $context);

        return new TaxResult(
            total_tax: $amount,
            tax_rate: $tax_rate,
            components: $components,
            price_includes_tax: (bool) $context->product->price_includes_tax,
        );
    }

    public function supports(TaxContext $context): bool
    {
        return true;
    }

    /**
     * Resolves tax rate using exact same logic as original TaxRateCalculatorService.
     * Priority: zip > country+state+city > country+state > country > base percentage.
     */
    protected function resolveRate(TaxContext $context): float
    {
        $tax_rate = 0;
        $taxes = $context->product->taxes->where('status', BaseStatusEnum::PUBLISHED);

        if ($taxes->isNotEmpty()) {
            foreach ($taxes as $tax) {
                if ($tax->rules && $tax->rules->isNotEmpty()) {
                    $rule = null;

                    if ($context->zip_code) {
                        $rule = $tax->rules->firstWhere('zip_code', $context->zip_code);
                    }

                    if (! $rule && $context->country && $context->state && $context->city) {
                        $rule = $tax->rules
                            ->where('country', $context->country)
                            ->where('state', $context->state)
                            ->where('city', $context->city)
                            ->first();
                    }

                    if (! $rule && $context->country && $context->state) {
                        $rule = $tax->rules
                            ->where('country', $context->country)
                            ->where('state', $context->state)
                            ->whereNull('city')
                            ->first();
                    }

                    if (! $rule && $context->country) {
                        $rule = $tax->rules
                            ->where('country', $context->country)
                            ->whereNull('state')
                            ->whereNull('city')
                            ->first();
                    }

                    if ($rule) {
                        $tax_rate += $rule->percentage;
                    } else {
                        $tax_rate += $tax->percentage;
                    }
                } else {
                    $tax_rate += $tax->percentage;
                }
            }
        } elseif ($default_tax_rate = get_ecommerce_setting('default_tax_rate')) {
            $tax_rate = Tax::query()->where('id', $default_tax_rate)->value('percentage');
        }

        return (float) $tax_rate;
    }
}
