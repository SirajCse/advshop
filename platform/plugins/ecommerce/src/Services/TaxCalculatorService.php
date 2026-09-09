<?php

namespace Botble\Ecommerce\Services;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Services\Data\CalculateTaxData;
use Botble\Ecommerce\Tax\DTOs\TaxContext;
use Botble\Ecommerce\Tax\TaxEngineManager;

class TaxCalculatorService
{
    public function __construct(protected TaxEngineManager $engine)
    {
    }

    public function execute(CalculateTaxData $input): array
    {
        if (! EcommerceHelper::isTaxEnabled()) {
            return [
                'tax_amount' => 0,
                'tax_rates' => [],
            ];
        }

        $taxRates = [];
        $taxAmount = 0;

        $products = Product::query()
            ->whereIn('id', collect($input->products)->pluck('id')->all())
            ->get();

        foreach ($input->products as $inputProduct) {
            $product = $products->firstWhere('id', $inputProduct['id']);

            if (! $product instanceof Product) {
                continue;
            }

            $quantity = $inputProduct['quantity'] ?? 1;
            $price = (float) ($inputProduct['price'] ?? $product->price ?? 0);

            $context = new TaxContext(
                product: $product,
                country: $input->country,
                state: $input->state,
                city: $input->city,
                zip_code: $input->zipCode,
                quantity: $quantity,
                price: $price,
            );

            $result = $this->engine->calculate($context);

            if ($result->total_tax > 0) {
                $taxAmount += $result->total_tax;

                $taxRates[] = [
                    'product_id' => $product->id,
                    'tax_rate' => $result->tax_rate,
                    'price' => $price,
                    'quantity' => $quantity,
                    'tax_amount' => $result->total_tax,
                    'components' => array_map(fn ($c) => $c->toArray(), $result->components),
                ];
            }
        }

        return [
            'tax_amount' => $taxAmount,
            'tax_rates' => $taxRates,
        ];
    }
}
