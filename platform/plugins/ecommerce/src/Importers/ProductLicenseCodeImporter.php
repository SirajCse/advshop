<?php

namespace Botble\Ecommerce\Importers;

use Botble\DataSynchronize\Contracts\Importer\WithMapping;
use Botble\DataSynchronize\Importer\ImportColumn;
use Botble\DataSynchronize\Importer\Importer;
use Botble\Ecommerce\Enums\ProductLicenseCodeStatusEnum;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductLicenseCode;
use Illuminate\Support\Arr;

class ProductLicenseCodeImporter extends Importer implements WithMapping
{
    public function getLabel(): string
    {
        return trans('plugins/ecommerce::products.license_codes.import.name');
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getValidateUrl(): string
    {
        return route('tools.data-synchronize.import.product-license-codes.validate');
    }

    public function getImportUrl(): string
    {
        return route('tools.data-synchronize.import.product-license-codes.store');
    }

    public function getDownloadExampleUrl(): ?string
    {
        return route('tools.data-synchronize.import.product-license-codes.download-example');
    }

    public function columns(): array
    {
        return [
            ImportColumn::make('product_id')
                ->label(trans('plugins/ecommerce::products.license_codes.import.columns.product_id'))
                ->rules(['required']),
            ImportColumn::make('license_code')
                ->label(trans('plugins/ecommerce::products.license_codes.import.columns.license_code'))
                ->rules(['required', 'string', 'max:255']),
        ];
    }

    public function examples(): array
    {
        return [
            [
                'product_id' => 1,
                'license_code' => 'XXXX-XXXX-XXXX-XXXX',
            ],
            [
                'product_id' => 1,
                'license_code' => 'YYYY-YYYY-YYYY-YYYY',
            ],
            [
                'product_id' => 2,
                'license_code' => 'ABCD-1234-EFGH-5678',
            ],
            [
                'product_id' => 'product-sku-123',
                'license_code' => '550e8400-e29b-41d4-a716-446655440000',
            ],
            [
                'product_id' => 'product-sku-123',
                'license_code' => 'LICENSE-KEY-12345',
            ],
        ];
    }

    public function handle(array $data): int
    {
        $total = 0;
        $productCache = [];

        foreach ($data as $row) {
            $productIdentifier = Arr::get($row, 'product_id');
            $licenseCode = Arr::get($row, 'license_code');

            if (empty($productIdentifier) || empty($licenseCode)) {
                continue;
            }

            // Check if license code already exists
            if (ProductLicenseCode::query()->where('license_code', $licenseCode)->exists()) {
                continue;
            }

            // Cache product lookups for performance
            if (! isset($productCache[$productIdentifier])) {
                $product = $this->findProduct($productIdentifier);
                $productCache[$productIdentifier] = $product;
            } else {
                $product = $productCache[$productIdentifier];
            }

            if (! $product) {
                continue;
            }

            ProductLicenseCode::query()->create([
                'product_id' => $product->id,
                'license_code' => $licenseCode,
                'status' => ProductLicenseCodeStatusEnum::AVAILABLE,
            ]);

            $total++;
        }

        return $total;
    }

    protected function findProduct(int|string $identifier): ?Product
    {
        // Try to find by ID first
        if (is_numeric($identifier)) {
            $product = Product::query()->find($identifier);
            if ($product) {
                return $product;
            }
        }

        // Try to find by SKU
        return Product::query()->where('sku', $identifier)->first();
    }

    public function map(mixed $row): array
    {
        return [
            'product_id' => Arr::get($row, 'product_id'),
            'license_code' => trim((string) Arr::get($row, 'license_code')),
        ];
    }
}
