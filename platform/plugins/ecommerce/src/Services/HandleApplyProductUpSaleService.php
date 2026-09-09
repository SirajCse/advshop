<?php

namespace Botble\Ecommerce\Services;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Services\Products\GetProductWithUpSalesBySlugService;
use Botble\Ecommerce\Services\Products\ProductUpSalePriceService;
use Illuminate\Support\Arr;

class HandleApplyProductUpSaleService
{
    public function __construct(
        protected GetProductWithUpSalesBySlugService $getProductWithUpSalesBySlugService,
        protected ProductUpSalePriceService $productUpSalePriceService
    ) {
    }

    public function handle(): void
    {
        $cart = Cart::instance('cart');

        if ($cart->isEmpty()) {
            return;
        }

        $cartContent = $cart->content();

        // Build a map of parent product slugs that are in cart
        $cartProductIds = $cartContent->pluck('id')->filter()->unique()->toArray();
        $parentSlugsInCart = $this->getParentSlugsInCart($cartProductIds);

        // Group cart items by their up-sale reference product
        $referenceGroups = [];

        foreach ($cartContent as $cartItem) {
            $reference = $cartItem->options['extras']['upsale_reference_product'] ?? null;

            if (! $reference) {
                continue;
            }

            // Security: Skip if the reference product is not in cart
            if (! in_array($reference, $parentSlugsInCart)) {
                // Remove invalid reference from cart item
                $this->removeInvalidUpSaleReference($cart, $cartItem);

                continue;
            }

            if (! isset($referenceGroups[$reference])) {
                $referenceGroups[$reference] = [];
            }

            $referenceGroups[$reference][] = $cartItem;
        }

        if (empty($referenceGroups)) {
            return;
        }

        // For each reference product, apply up-sale pricing to its items
        foreach ($referenceGroups as $referenceSlug => $cartItems) {
            $referenceProduct = $this->getProductWithUpSalesBySlugService->handle($referenceSlug);

            if (! $referenceProduct) {
                continue;
            }

            // Apply the reference product to the up-sale pricing service
            $this->productUpSalePriceService->applyProduct($referenceProduct);

            // Get all product IDs from these cart items
            $productIds = array_map(fn ($item) => $item->id, $cartItems);

            // Load products with up-sale pricing now applied
            $products = get_products([
                'condition' => [
                    ['ec_products.id', 'IN', $productIds],
                    'ec_products.status' => BaseStatusEnum::PUBLISHED,
                ],
            ]);

            if ($products->isEmpty()) {
                continue;
            }

            // Build a map of product prices (now with up-sale discount applied)
            $productPrices = [];
            foreach ($products as $product) {
                $productPrices[$product->getKey()] = $product->front_sale_price;
            }

            // Update cart items with the new prices
            foreach ($cartItems as $cartItem) {
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

                $cart->removeQuietly($cartItem->rowId);

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

    /**
     * Get all parent product slugs that are in the cart.
     * This includes both direct products and parent products of variations.
     */
    protected function getParentSlugsInCart(array $cartProductIds): array
    {
        if (empty($cartProductIds)) {
            return [];
        }

        $products = Product::query()
            ->whereIn('id', $cartProductIds)
            ->with('variationInfo.configurableProduct')
            ->get();

        $slugs = [];

        foreach ($products as $product) {
            $originalProduct = $product->original_product;

            if ($originalProduct && $originalProduct->slug) {
                $slugs[] = $originalProduct->slug;
            }
        }

        return array_unique($slugs);
    }

    /**
     * Remove invalid up-sale reference from cart item and reset to original price.
     */
    protected function removeInvalidUpSaleReference($cart, $cartItem): void
    {
        if (apply_filters('ecommerce_skip_cart_item_price_update', false, $cartItem)) {
            return;
        }

        $options = $cartItem->options->toArray();
        unset($options['extras']['upsale_reference_product']);

        // Get original price without up-sale discount
        $this->productUpSalePriceService->clearAppliedProducts();

        $product = Product::query()->find($cartItem->id);

        if (! $product) {
            return;
        }

        $originalPrice = $product->front_sale_price;

        $cart->removeQuietly($cartItem->rowId);

        $cart->addQuietly(
            $cartItem->id,
            $cartItem->name,
            $cartItem->qty,
            $originalPrice,
            $options
        );
    }
}
