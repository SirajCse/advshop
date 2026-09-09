<?php

namespace Botble\Ecommerce\Tax\Contracts;

use Botble\Ecommerce\Models\TaxRule;

interface TaxZoneResolverInterface
{
    public function resolve(
        string $country,
        ?string $state = null,
        ?string $city = null,
        ?string $zipCode = null
    ): ?TaxRule;
}
