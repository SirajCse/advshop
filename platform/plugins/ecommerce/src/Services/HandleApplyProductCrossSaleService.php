<?php

namespace Botble\Ecommerce\Services;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Services\Products\ProductCrossSalePriceService;
use Botble\Ecommerce\Services\Products\ProductUpSalePriceService;
use Illuminate\Support\Arr;

class HandleApplyProductCrossSaleService
{
    public function __construct(
        protected ProductCrossSalePriceService $productCrossSalePriceService,
        protected ProductUpSalePriceService $productUpSalePriceService
    ) {
    }

    public function handle(): void
    {
        $cart = Cart::instance('cart');

        if ($cart->isEmpty()) {
            return;
        }

        $ids = $cart->content()->pluck('id')->all();

        $products = get_products([
            'condition' => [
                ['ec_products.id', 'IN', $ids],
                'ec_products.status' => BaseStatusEnum::PUBLISHED,
            ],
            'with' => [
                'crossSales',
                'variationInfo.configurableProduct',
            ],
        ]);

        if ($products->isEmpty()) {
            return;
        }

        $crossSaleProducts = [];

        foreach ($products as $product) {
            if (! $product->is_variation) {
                $crossSaleProducts[] = $product;

                continue;
            }

            $crossSaleProducts[] = $product->original_product;
        }

        if (empty($crossSaleProducts)) {
            return;
        }

        $this->productCrossSalePriceService->applyProducts($crossSaleProducts);

        // Disable auto-loading of up-sale context to prevent up-sale pricing from being
        // incorrectly applied to products without cartItem attached. This ensures only
        // cross-sale pricing is applied here, not up-sale pricing which depends on
        // individual cart item's upsale_reference_product.
        $this->productUpSalePriceService->disableAutoLoad();

        $productPrices = [];

        try {
            foreach ($products as $product) {
                $productPrices[$product->getKey()] = $product->front_sale_price;
            }
        } finally {
            $this->productUpSalePriceService->enableAutoLoad();
        }

        foreach ($cart->content() as $rowId => $cartItem) {
            if (! isset($productPrices[$cartItem->id])) {
                continue;
            }

            if (apply_filters('ecommerce_skip_cart_item_price_update', false, $cartItem)) {
                continue;
            }

            $newPrice = $productPrices[$cartItem->id];
            $options = $cartItem->options->toArray();

            $newPriceWithOptions = $newPrice;
            if (
                EcommerceHelper::isEnabledProductOptions() &&
                ($productOptions = Arr::get($options, 'options', [])) &&
                is_array($productOptions)
            ) {
                $priceResult = $cart->getPriceByOptions($newPrice, $productOptions);
                $newPriceWithOptions = $priceResult['price'];
            }

            if ($cartItem->price == $newPriceWithOptions) {
                continue;
            }

            $cart->removeQuietly($rowId);

            $cart->addQuietly(
                $cartItem->id,
                $cartItem->name,
                $cartItem->qty,
                $newPrice,
                $options
            );
        }
    }
}
