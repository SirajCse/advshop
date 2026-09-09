<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\OrderProductTaxComponent;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Tax\DTOs\TaxContext;
use Botble\Ecommerce\Tax\TaxEngineManager;
use Illuminate\Support\Facades\DB;

class StoreTaxComponentsListener
{
    public function __construct(protected TaxEngineManager $engine)
    {
    }

    public function handle(OrderProduct $orderProduct): void
    {
        if (! EcommerceHelper::isTaxEnabled()) {
            return;
        }

        if (! $orderProduct->tax_amount || $orderProduct->tax_amount <= 0) {
            return;
        }

        if ($orderProduct->taxComponents()->exists()) {
            return;
        }

        $product = $orderProduct->product;

        if (! $product instanceof Product || ! $product->id) {
            return;
        }

        $address = $orderProduct->order->address;

        $context = new TaxContext(
            product: $product,
            country: $address?->country,
            state: $address?->state,
            city: $address?->city,
            zip_code: $address?->zip_code,
            quantity: $orderProduct->qty,
            price: (float) ($orderProduct->price ?? 0),
        );

        $result = $this->engine->calculate($context);

        $componentsData = [];

        foreach ($result->components as $component) {
            $componentsData[] = [
                'order_product_id' => $orderProduct->id,
                'name' => $component->name,
                'code' => $component->code,
                'rate' => $component->rate,
                'amount' => $component->amount,
                'jurisdiction' => $component->jurisdiction,
                'metadata' => $component->metadata ? json_encode($component->metadata) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($componentsData) {
            DB::transaction(function () use ($orderProduct, $componentsData, $result): void {
                OrderProductTaxComponent::query()->insert($componentsData);

                $orderProduct->updateQuietly([
                    'tax_breakdown' => array_map(fn ($c) => $c->toArray(), $result->components),
                ]);
            });

            do_action('ecommerce_tax_order_stored', $orderProduct, $result);
        }
    }
}
