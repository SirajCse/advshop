<?php

namespace Botble\Ecommerce\Importers;

use Botble\DataSynchronize\Contracts\Importer\WithMapping;
use Botble\DataSynchronize\Importer\ImportColumn;
use Botble\DataSynchronize\Importer\Importer;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductSpecificationAttributeTranslation;
use Botble\Ecommerce\Models\SpecificationAttribute;
use Botble\Ecommerce\Models\SpecificationTable;
use Botble\Language\Facades\Language;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ProductSpecificationImporter extends Importer implements WithMapping
{
    protected Collection $specificationTables;

    protected Collection $specificationAttributes;

    protected Collection $products;

    protected array $supportedLocales = [];

    protected ?string $defaultLanguage = null;

    public function __construct()
    {
        $this->specificationTables = collect();
        $this->specificationAttributes = collect();
        $this->products = collect();

        if (defined('LANGUAGE_MODULE_SCREEN_NAME') && defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
            $this->defaultLanguage = Language::getDefaultLanguage(['lang_code'])?->lang_code;
            $this->supportedLocales = collect(Language::getSupportedLocales())
                ->reject(fn ($locale, $key) => $key === $this->defaultLanguage)
                ->keys()
                ->all();
        }
    }

    public function getLabel(): string
    {
        return trans('plugins/ecommerce::product-specification.import.name');
    }

    public function getHeading(): string
    {
        return $this->getLabel();
    }

    public function getDoneMessage(int $count): string
    {
        return trans('plugins/ecommerce::product-specification.import.done_message', ['count' => $count]);
    }

    public function getExportUrl(): ?string
    {
        return Auth::user()->hasPermission('ecommerce.product-specifications.export')
            ? route('ecommerce.product-specifications.export.store')
            : null;
    }

    public function getValidateUrl(): string
    {
        return route('ecommerce.product-specifications.import.validate');
    }

    public function getImportUrl(): string
    {
        return route('ecommerce.product-specifications.import.store');
    }

    public function getDownloadExampleUrl(): string
    {
        return route('ecommerce.product-specifications.import.download-example');
    }

    public function columns(): array
    {
        $columns = [
            ImportColumn::make('name')
                ->rules(
                    ['required', 'string'],
                    trans('plugins/ecommerce::product-specification.import.rules.name')
                ),
            ImportColumn::make('specification_table')
                ->nullable()
                ->rules(
                    ['nullable', 'string'],
                    trans('plugins/ecommerce::product-specification.import.rules.specification_table')
                ),
            ImportColumn::make('specifications')
                ->nullable()
                ->rules(
                    ['nullable', 'string'],
                    trans('plugins/ecommerce::product-specification.import.rules.specifications')
                ),
        ];

        foreach ($this->supportedLocales as $locale) {
            $columns[] = ImportColumn::make("specifications_{$locale}")
                ->label("Specifications ({$locale})")
                ->nullable()
                ->rules(
                    ['nullable', 'string'],
                    trans('plugins/ecommerce::product-specification.import.rules.specifications_locale', ['locale' => $locale])
                );
        }

        return $columns;
    }

    public function map(mixed $row): array
    {
        return $row;
    }

    public function examples(): array
    {
        $products = Product::query()
            ->where('is_variation', 0)
            ->wherePublished()
            ->whereNotNull('specification_table_id')
            ->with(['specificationTable', 'specificationAttributes'])
            ->take(5)
            ->get();

        if ($products->isNotEmpty()) {
            return $products->map(function (Product $product) {
                $row = [
                    'name' => $product->name,
                    'specification_table' => $product->specificationTable?->name,
                    'specifications' => $this->formatSpecifications($product),
                ];

                foreach ($this->supportedLocales as $locale) {
                    $row["specifications_{$locale}"] = $this->formatSpecifications($product, $locale);
                }

                return $row;
            })->all();
        }

        $row = [
            'name' => 'Smartphone X Pro',
            'specification_table' => 'General Specification',
            'specifications' => 'Height:2cm|Width:30cm|Weight:1.5kg',
        ];

        foreach ($this->supportedLocales as $locale) {
            $row["specifications_{$locale}"] = 'Height:2cm|Width:30cm|Weight:1.5kg';
        }

        return [$row];
    }

    public function handle(array $data): int
    {
        $count = 0;

        foreach ($data as $row) {
            $productName = $row['name'] ?? null;

            if (! $productName) {
                continue;
            }

            $product = $this->resolveProduct($productName);

            if (! $product) {
                continue;
            }

            $specificationTableName = $row['specification_table'] ?? null;
            $specificationTableId = null;

            if ($specificationTableName) {
                $specificationTableId = $this->resolveSpecificationTableId($specificationTableName);
            }

            $product->specification_table_id = $specificationTableId;
            $product->saveQuietly();

            $specificationsString = $row['specifications'] ?? null;

            if ($specificationsString) {
                $this->syncSpecifications($product, $specificationsString);
            }

            foreach ($this->supportedLocales as $locale) {
                $localeSpecifications = $row["specifications_{$locale}"] ?? null;

                if ($localeSpecifications) {
                    $this->syncTranslations($product, $localeSpecifications, $locale);
                }
            }

            $count++;
        }

        return $count;
    }

    protected function resolveProduct(string $name): ?Product
    {
        if ($this->products->has($name)) {
            return $this->products->get($name);
        }

        $product = Product::query()
            ->where('name', $name)
            ->where('is_variation', 0)
            ->first();

        $this->products->put($name, $product);

        return $product;
    }

    protected function resolveSpecificationTableId(string $name): ?int
    {
        if ($this->specificationTables->has($name)) {
            return $this->specificationTables->get($name);
        }

        $table = SpecificationTable::query()
            ->withoutGlobalScopes()
            ->where('name', $name)
            ->first();

        $id = $table?->id;

        $this->specificationTables->put($name, $id);

        return $id;
    }

    protected function resolveSpecificationAttribute(string $name): ?SpecificationAttribute
    {
        if ($this->specificationAttributes->isEmpty()) {
            $this->specificationAttributes = SpecificationAttribute::query()
                ->withoutGlobalScopes()
                ->get()
                ->keyBy('name');
        }

        return $this->specificationAttributes->get($name);
    }

    protected function syncSpecifications(Product $product, string $specificationsString): void
    {
        $pairs = $this->parseSpecifications($specificationsString);

        $pivotData = [];

        $order = 0;

        foreach ($pairs as $attributeName => $value) {
            $attribute = $this->resolveSpecificationAttribute($attributeName);

            if (! $attribute) {
                continue;
            }

            if ($attribute->hasOptions() && $attribute->hasIdBasedOptions()) {
                $optionId = $attribute->getOptionIdByValue($value);

                if ($optionId) {
                    $value = $optionId;
                }
            }

            $pivotData[$attribute->id] = [
                'value' => $value,
                'hidden' => false,
                'order' => $order++,
            ];
        }

        if (! empty($pivotData)) {
            $product->specificationAttributes()->sync($pivotData);
        }
    }

    protected function syncTranslations(Product $product, string $specificationsString, string $locale): void
    {
        $pairs = $this->parseSpecifications($specificationsString);

        foreach ($pairs as $attributeName => $value) {
            $attribute = $this->resolveSpecificationAttribute($attributeName);

            if (! $attribute) {
                continue;
            }

            if ($attribute->hasOptions() && $attribute->hasIdBasedOptions()) {
                continue;
            }

            ProductSpecificationAttributeTranslation::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'attribute_id' => $attribute->id,
                    'lang_code' => $locale,
                ],
                [
                    'value' => $value,
                ]
            );
        }
    }

    protected function parseSpecifications(string $specificationsString): array
    {
        $pairs = [];

        foreach (explode('|', $specificationsString) as $pair) {
            $pair = trim($pair);

            if (! str_contains($pair, ':')) {
                continue;
            }

            [$name, $value] = explode(':', $pair, 2);

            $name = trim($name);
            $value = trim($value);

            if ($name !== '') {
                $pairs[$name] = $value;
            }
        }

        return $pairs;
    }

    protected function formatSpecifications(Product $product, ?string $locale = null): string
    {
        $parts = [];

        foreach ($product->specificationAttributes as $attribute) {
            $value = $attribute->pivot->value;

            if ($locale) {
                $value = ProductSpecificationAttributeTranslation::getDisplayValue(
                    $product,
                    $attribute,
                    $locale
                ) ?? $value;
            } elseif ($attribute->hasOptions() && $attribute->hasIdBasedOptions()) {
                $value = $attribute->getOptionValueById($value) ?? $value;
            }

            if ($value) {
                $parts[] = $attribute->name . ':' . $value;
            }
        }

        return implode('|', $parts);
    }
}
