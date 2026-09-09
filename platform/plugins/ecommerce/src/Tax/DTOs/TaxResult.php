<?php

namespace Botble\Ecommerce\Tax\DTOs;

class TaxResult
{
    /**
     * @param TaxComponent[] $components
     */
    public function __construct(
        public readonly float $total_tax = 0,
        public readonly float $tax_rate = 0,
        public readonly array $components = [],
        public readonly bool $price_includes_tax = false,
        public readonly array $metadata = [],
    ) {
    }

    public function merge(self $other): self
    {
        return new self(
            total_tax: $this->total_tax + $other->total_tax,
            tax_rate: $this->tax_rate + $other->tax_rate,
            components: array_merge($this->components, $other->components),
            price_includes_tax: $this->price_includes_tax || $other->price_includes_tax,
            metadata: array_merge($this->metadata, $other->metadata),
        );
    }

    public static function zero(): self
    {
        return new self();
    }

    public function toArray(): array
    {
        return [
            'total_tax' => $this->total_tax,
            'tax_rate' => $this->tax_rate,
            'components' => array_map(fn (TaxComponent $c) => $c->toArray(), $this->components),
            'price_includes_tax' => $this->price_includes_tax,
            'metadata' => $this->metadata,
        ];
    }
}
