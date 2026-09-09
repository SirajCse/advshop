<?php

namespace Botble\Ecommerce\Services;

use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Tax\DTOs\TaxContext;
use Botble\Ecommerce\Tax\TaxEngineManager;

/**
 * @deprecated Use TaxEngineManager directly. Kept for backward compatibility.
 */
class TaxRateCalculatorService
{
    public function __construct(protected TaxEngineManager $engine)
    {
    }

    public function execute(
        Product $product,
        ?string $country = null,
        ?string $state = null,
        ?string $city = null,
        ?string $zipCode = null
    ): float {
        $context = new TaxContext(
            product: $product,
            country: $country,
            state: $state,
            city: $city,
            zip_code: $zipCode,
            quantity: 1,
            price: (float) ($product->price ?? 0),
        );

        $result = $this->engine->calculate($context);

        return (float) apply_filters(
            'ecommerce_tax_rate_calculated',
            $result->tax_rate,
            $product,
            $country,
            $state,
            $city,
            $zipCode
        );
    }
}
