<?php

namespace Botble\Ecommerce\Services\Products;

use Botble\Ecommerce\Models\Product;
use Illuminate\Support\Facades\Pipeline;

class ProductPriceService
{
    protected array $priceHandlers = [
        ProductSalePriceService::class,
        ProductFlashSalePriceService::class,
        ProductDiscountPriceService::class,
        ProductCrossSalePriceService::class,
        ProductUpSalePriceService::class,
    ];

    public function __construct(
        protected float $finalPrice = 0,
        protected ?Product $product = null
    ) {
    }

    public function getPrice(Product $product): float
    {
        $this->product = $product;

        $this->product->setFinalPrice($product->getConvertedPrice());

        $this->applyPriceHandlers();

        return $this->product->getFinalPrice();
    }

    public function getOriginalPrice(Product $product): float
    {
        $this->product = $product;

        $this->product->setOriginalPrice($product->getConvertedPrice());

        $this->applyPriceHandlers();

        return $this->product->getOriginalPrice();
    }

    protected function applyPriceHandlers(): Product
    {
        return Pipeline::send($this->product)
            ->through($this->priceHandlers)
            ->thenReturn();
    }
}
