<?php

namespace Botble\Ecommerce\Tax\DTOs;

class TaxComponent
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly float $rate,
        public readonly float $amount,
        public readonly ?string $jurisdiction = null,
        public readonly array $metadata = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'rate' => $this->rate,
            'amount' => $this->amount,
            'jurisdiction' => $this->jurisdiction,
            'metadata' => $this->metadata,
        ];
    }
}
