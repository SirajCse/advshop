<?php

namespace Botble\Ecommerce\Tax\DTOs;

class TaxLineItem
{
    /**
     * @param TaxComponent[] $components
     */
    public function __construct(
        public readonly int|string $product_id,
        public readonly float $price,
        public readonly int $quantity,
        public readonly float $tax_rate,
        public readonly float $tax_amount,
        public readonly array $components = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->product_id,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'components' => array_map(fn (TaxComponent $c) => $c->toArray(), $this->components),
        ];
    }
}
