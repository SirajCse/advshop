<?php

namespace Botble\Ecommerce\Tax;

use Botble\Ecommerce\Models\TaxRule;
use Botble\Ecommerce\Tax\Contracts\TaxZoneResolverInterface;
use Illuminate\Support\Collection;

class TaxZoneResolver implements TaxZoneResolverInterface
{
    /**
     * Resolve the best matching tax rule from a collection of rules.
     * Priority: zip > country+state+city > country+state > country.
     */
    public function resolve(
        string $country,
        ?string $state = null,
        ?string $city = null,
        ?string $zipCode = null
    ): ?TaxRule {
        return null;
    }

    /**
     * Resolve from a specific collection of rules (used by DefaultTaxCalculator per-tax).
     */
    public function resolveFromRules(
        Collection $rules,
        ?string $country,
        ?string $state = null,
        ?string $city = null,
        ?string $zipCode = null
    ): ?TaxRule {
        $rule = null;

        if ($zipCode) {
            $rule = $rules->firstWhere('zip_code', $zipCode);
        }

        if (! $rule && $country && $state && $city) {
            $rule = $rules
                ->where('country', $country)
                ->where('state', $state)
                ->where('city', $city)
                ->first();
        }

        if (! $rule && $country && $state) {
            $rule = $rules
                ->where('country', $country)
                ->where('state', $state)
                ->whereNull('city')
                ->first();
        }

        if (! $rule && $country) {
            $rule = $rules
                ->where('country', $country)
                ->whereNull('state')
                ->whereNull('city')
                ->first();
        }

        return $rule;
    }
}
