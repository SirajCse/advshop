<?php

namespace Botble\Ecommerce\Exporters;

use Botble\DataSynchronize\Exporter\ExportColumn;
use Botble\DataSynchronize\Exporter\Exporter;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductSpecificationAttributeTranslation;
use Botble\Language\Facades\Language;
use Illuminate\Support\Collection;

class ProductSpecificationExporter extends Exporter
{
    protected array $supportedLocales = [];

    protected ?string $defaultLanguage = null;

    public function __construct()
    {
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
        return trans('plugins/ecommerce::product-specification.name');
    }

    public function columns(): array
    {
        $columns = [
            ExportColumn::make('name')
                ->disabled(),
            ExportColumn::make('specification_table')
                ->disabled(),
            ExportColumn::make('specifications')
                ->disabled(),
        ];

        foreach ($this->supportedLocales as $locale) {
            $columns[] = ExportColumn::make("specifications_{$locale}")
                ->label("Specifications ({$locale})")
                ->disabled();
        }

        return $columns;
    }

    public function collection(): Collection
    {
        return Product::query()
            ->where('is_variation', 0)
            ->wherePublished()
            ->with(['specificationTable', 'specificationAttributes'])
            ->get()
            ->map(function (Product $product) {
                $row = [
                    'name' => $product->name,
                    'specification_table' => $product->specificationTable?->name,
                    'specifications' => $this->formatSpecifications($product),
                ];

                foreach ($this->supportedLocales as $locale) {
                    $row["specifications_{$locale}"] = $this->formatSpecifications($product, $locale);
                }

                return $row;
            });
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
