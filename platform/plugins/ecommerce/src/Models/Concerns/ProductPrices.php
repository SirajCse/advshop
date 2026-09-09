<?php

namespace Botble\Ecommerce\Models\Concerns;

use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;
use Botble\Ecommerce\Facades\Discount as DiscountFacade;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\FlashSale as FlashSaleFacade;
use Botble\Ecommerce\Models\Currency;
use Botble\Ecommerce\Services\Products\ProductPriceService;
use Botble\Ecommerce\ValueObjects\ProductPrice;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait ProductPrices
{
    protected float $originalPrice = 0;

    protected float $finalPrice = 0;

    protected ProductPrice $priceObject;

    public function price(): ProductPrice
    {
        return $this->priceObject ??= ProductPrice::make($this);
    }

    protected function frontSalePrice(): Attribute
    {
        return Attribute::get(
            fn () => app(ProductPriceService::class)->getPrice($this)
        );
    }

    protected function originalPrice(): Attribute
    {
        return Attribute::get(
            fn () => app(ProductPriceService::class)->getOriginalPrice($this)
        );
    }

    public function getFlashSalePrice(): float|false|null
    {
        if (! FlashSaleFacade::isEnabled()) {
            return 0;
        }

        $flashSale = FlashSaleFacade::getFacadeRoot()->flashSaleForProduct($this);

        if ($flashSale && $flashSale->pivot->quantity > $flashSale->pivot->sold) {
            return $flashSale->pivot->price;
        }

        return $this->getConvertedPrice();
    }

    public function getDiscountPrice(): float|int|null
    {
        $productIds = array_unique([$this->getKey(), $this->original_product->id]);

        $promotion = DiscountFacade::getFacadeRoot()
            ->promotionForProduct($productIds);

        if (! $promotion) {
            return $this->getConvertedPrice();
        }

        $price = $this->getConvertedPrice();
        switch ($promotion->type_option) {
            case DiscountTypeOptionEnum::SAME_PRICE:
                $price = $promotion->value;

                break;
            case DiscountTypeOptionEnum::AMOUNT:
                $price = $price - $promotion->value;
                if ($price < 0) {
                    $price = 0;
                }

                break;
            case DiscountTypeOptionEnum::PERCENTAGE:
                $price = $price - ($price * $promotion->value / 100);
                if ($price < 0) {
                    $price = 0;
                }

                break;
        }

        return $price;
    }

    protected function frontSalePriceWithTaxes(): Attribute
    {
        return Attribute::get(function (): ?float {
            if ($this->price_includes_tax) {
                return $this->front_sale_price;
            }

            if (! EcommerceHelper::isDisplayProductIncludingTaxes()) {
                return $this->front_sale_price;
            }

            return $this->front_sale_price + $this->front_sale_price * ($this->total_taxes_percentage / 100);
        });
    }

    protected function priceWithTaxes(): Attribute
    {
        return Attribute::get(function (): ?float {
            $convertedPrice = $this->getConvertedPrice();

            if ($this->price_includes_tax) {
                return $convertedPrice;
            }

            if (! EcommerceHelper::isDisplayProductIncludingTaxes()) {
                return $convertedPrice;
            }

            return $convertedPrice + $convertedPrice * ($this->total_taxes_percentage / 100);
        });
    }

    protected function priceInTable(): Attribute
    {
        return Attribute::get(function () {
            $price = format_price($this->front_sale_price);
            $convertedPrice = $this->getConvertedPrice();

            if ($this->isOnSale()) {
                $price .= sprintf(' <del class="text-danger">%s</del>', format_price($convertedPrice));
            }

            return $price;
        });
    }

    protected function salePercent(): Attribute
    {
        return Attribute::get(function (): int {
            $convertedPrice = $this->getConvertedPrice();
            $rawSale = $this->getRawSalePrice();

            // Use the product's own sale_price (converted) for the percentage,
            // ignoring pipeline reductions (cross-sale, up-sale).
            $effectiveSale = $rawSale !== null && $rawSale > 0
                ? ($this->getConvertedSalePrice() ?? $rawSale)
                : $convertedPrice;

            if ($effectiveSale == 0 && $convertedPrice !== 0) {
                return 100;
            }

            if (! $effectiveSale || ! $convertedPrice) {
                return 0;
            }

            return (int) round(($convertedPrice - $effectiveSale) / $convertedPrice * 100);
        });
    }

    public function isOnSale(): bool
    {
        // A product is on sale only when its own sale_price is genuinely lower
        // than its base price. Use raw database values to avoid pipeline pollution
        // (cross-sale/up-sale handlers that reduce front_sale_price at runtime).
        $rawSale = $this->getRawSalePrice();
        $base = $this->getRawPrice();

        return $rawSale !== null && $rawSale > 0 && $base - $rawSale > 0.00001;
    }

    public function getOriginalPrice(): float
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice(?float $price): static
    {
        $this->originalPrice = (float) $price;

        return $this;
    }

    public function getFinalPrice(): float
    {
        return $this->finalPrice;
    }

    public function setFinalPrice(?float $price): static
    {
        $this->finalPrice = (float) $price;

        return $this;
    }

    /**
     * Convert price from product's currency to default currency.
     */
    protected function convertToDefaultCurrency(?float $price): ?float
    {
        if ($price === null || $price == 0) {
            return $price;
        }

        $sourceCurrency = $this->getSourceCurrency();

        if (! $sourceCurrency || $sourceCurrency->is_default) {
            return $price;
        }

        if ($sourceCurrency->exchange_rate <= 0) {
            return $price;
        }

        return $price / $sourceCurrency->exchange_rate;
    }

    /**
     * Get product's source currency (variations inherit from parent).
     */
    public function getSourceCurrency(): ?Currency
    {
        if ($this->is_variation && $this->original_product && $this->original_product->getKey() !== $this->getKey()) {
            return $this->original_product->getSourceCurrency();
        }

        if (! $this->currency_code) {
            return null;
        }

        static $currencyCache = [];

        if (! isset($currencyCache[$this->currency_code])) {
            $currencyCache[$this->currency_code] = Currency::query()
                ->where('title', $this->currency_code)
                ->first();
        }

        return $currencyCache[$this->currency_code];
    }

    /**
     * Get converted price (in default currency) for display.
     */
    public function getConvertedPrice(): float
    {
        return $this->convertToDefaultCurrency($this->getRawPrice()) ?? 0;
    }

    /**
     * Get converted sale price (in default currency) for display.
     */
    public function getConvertedSalePrice(): ?float
    {
        return $this->convertToDefaultCurrency($this->getRawSalePrice());
    }

    /**
     * Get raw price without conversion (for form display).
     */
    public function getRawPrice(): float
    {
        return (float) ($this->attributes['price'] ?? 0);
    }

    /**
     * Get raw sale price without conversion (for form display).
     */
    public function getRawSalePrice(): ?float
    {
        return $this->attributes['sale_price'] ?? null;
    }

    /**
     * Get raw cost per item without conversion (for form display).
     */
    public function getRawCostPerItem(): ?float
    {
        return $this->attributes['cost_per_item'] ?? null;
    }

    /**
     * Get display price (converted to default currency).
     * Use this in views instead of $product->price for proper currency conversion.
     */
    protected function displayPrice(): Attribute
    {
        return Attribute::get(fn () => $this->getConvertedPrice());
    }

    /**
     * Get display sale price (converted to default currency).
     * Use this in views instead of $product->sale_price for proper currency conversion.
     */
    protected function displaySalePrice(): Attribute
    {
        return Attribute::get(fn () => $this->getConvertedSalePrice());
    }
}
