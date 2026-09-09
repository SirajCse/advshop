<?php

namespace Botble\Ecommerce\Tax\DTOs;

use Botble\Ecommerce\Models\Product;

/**
 * @property-read string|null $customer_tax_id PII: VAT/GST number. Do not log or expose in error traces.
 */
class TaxContext
{
    public function __construct(
        public readonly Product $product,
        public readonly ?string $country = null,
        public readonly ?string $state = null,
        public readonly ?string $city = null,
        public readonly ?string $zip_code = null,
        public readonly ?string $customer_tax_class = null,
        public readonly ?string $customer_tax_id = null,
        public readonly ?string $seller_country = null,
        public readonly ?string $seller_state = null,
        public readonly int $quantity = 1,
        public readonly float $price = 0,
        public readonly array $metadata = [],
    ) {
    }

    public function withSellerLocation(?string $seller_country, ?string $seller_state, array $extra_metadata = []): self
    {
        return new self(
            product: $this->product,
            country: $this->country,
            state: $this->state,
            city: $this->city,
            zip_code: $this->zip_code,
            customer_tax_class: $this->customer_tax_class,
            customer_tax_id: $this->customer_tax_id,
            seller_country: $seller_country,
            seller_state: $seller_state,
            quantity: $this->quantity,
            price: $this->price,
            metadata: array_merge($this->metadata, $extra_metadata),
        );
    }

    public static function fromArray(array $data, Product $product): self
    {
        return new self(
            product: $product,
            country: $data['country'] ?? null,
            state: $data['state'] ?? null,
            city: $data['city'] ?? null,
            zip_code: $data['zip_code'] ?? null,
            customer_tax_class: $data['customer_tax_class'] ?? null,
            customer_tax_id: $data['customer_tax_id'] ?? null,
            seller_country: $data['seller_country'] ?? null,
            seller_state: $data['seller_state'] ?? null,
            quantity: (int) ($data['quantity'] ?? 1),
            price: (float) ($data['price'] ?? 0),
            metadata: $data['metadata'] ?? [],
        );
    }
}
