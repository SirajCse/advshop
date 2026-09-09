<?php

namespace Botble\Ecommerce\Services\Products;

use Botble\Ecommerce\Enums\UpSellPriceType;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Product;
use Closure;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

class ProductUpSalePriceService extends ProductPriceHandlerService
{
    protected array $appliedProducts = [];

    protected bool $cartContextLoaded = false;

    protected bool $autoLoadDisabled = false;

    public function disableAutoLoad(): void
    {
        $this->autoLoadDisabled = true;
    }

    public function enableAutoLoad(): void
    {
        $this->autoLoadDisabled = false;
    }

    public function applyProduct(Product $product): void
    {
        $product->loadMissing('upSales');

        if ($product->upSales->isEmpty()) {
            return;
        }

        foreach ($product->upSales as $upSaleProduct) {
            $this->appliedProducts[$upSaleProduct->getKey()] = $upSaleProduct;

            if ($upSaleProduct->variations()->exists()) {
                $upSaleProduct->loadMissing('variations.product');
                $upSaleProduct->variations->each(function ($variation): void {
                    $this->appliedProducts[$variation->product->getKey()] = $variation->product;
                });
            }
        }
    }

    public function applyProducts(Collection|array $products): void
    {
        foreach ($products as $product) {
            $this->applyProduct($product);
        }
    }

    public function getAppliedProducts(): array
    {
        return $this->appliedProducts;
    }

    public function clearAppliedProducts(): void
    {
        $this->appliedProducts = [];
        $this->cartContextLoaded = false;
    }

    /**
     * Check if we're in a cart/checkout context where up-sale pricing from cart should apply.
     * This should only match routes where we DISPLAY cart/checkout, not add-to-cart actions.
     */
    protected function isCartContext(): bool
    {
        try {
            // Never auto-load during add-to-cart (controller handles it explicitly)
            if (request()->is('*/add-to-cart') || request()->is('cart/add-to-cart')) {
                return false;
            }

            $routeName = request()->route()?->getName() ?? '';

            $route = request()->route();

            if (! $route) {
                return false;
            }

            // Explicitly exclude add-to-cart routes
            if (str_contains($routeName, 'add-to-cart') || str_contains($routeName, 'add-by-url')) {
                return false;
            }

            // Routes where we should auto-load up-sale context from cart
            $cartDisplayRoutes = [
                'public.cart', // Cart page
                'public.cart.update', // Cart update
                'public.cart.remove', // Remove from cart
            ];

            // Only apply on cart display/update routes, checkout, and payment pages
            return in_array($routeName, $cartDisplayRoutes)
                || str_starts_with($routeName, 'public.checkout')
                || str_starts_with($routeName, 'payments.')
                || str_starts_with($routeName, 'public.ajax.cart');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Load up-sale context from cart items if not already loaded.
     * This ensures up-sale pricing works during checkout/refresh.
     * Only loads in cart/checkout context to avoid affecting regular product pages.
     */
    protected function loadUpSaleContextFromCart(): void
    {
        // Skip if auto-loading is disabled (e.g., during add-to-cart)
        if ($this->autoLoadDisabled) {
            return;
        }

        if ($this->cartContextLoaded) {
            return;
        }

        $this->cartContextLoaded = true;

        // Only load cart context on cart/checkout pages
        if (! $this->isCartContext()) {
            return;
        }

        try {
            $cart = Cart::instance('cart');

            if ($cart->isEmpty()) {
                return;
            }

            $cartContent = $cart->content();
            $referenceProductSlugs = [];

            // Collect all unique reference product slugs from cart items
            foreach ($cartContent as $cartItem) {
                $reference = $cartItem->options['extras']['upsale_reference_product'] ?? null;

                if ($reference && ! in_array($reference, $referenceProductSlugs)) {
                    $referenceProductSlugs[] = $reference;
                }
            }

            if (empty($referenceProductSlugs)) {
                return;
            }

            // Load each reference product and apply up-sale pricing
            $getProductService = app(GetProductWithUpSalesBySlugService::class);

            foreach ($referenceProductSlugs as $slug) {
                $referenceProduct = $getProductService->handle($slug);

                if ($referenceProduct) {
                    $this->applyProduct($referenceProduct);
                }
            }
        } catch (\Throwable $e) {
            // Silently fail if cart is not available
        }
    }

    public function handle(Product $product, Closure $next)
    {
        // Auto-load up-sale context from cart if appliedProducts is empty
        if (empty($this->appliedProducts)) {
            $this->loadUpSaleContextFromCart();
        }

        if (empty($this->appliedProducts)) {
            return $next($product);
        }

        // IMPORTANT: When processing products from cart context (Cart::products()),
        // each product has a cartItem attached. Only apply upsale pricing to products
        // whose cart item explicitly has the upsale reference. This prevents non-bundle
        // items from getting the bundle discount when both exist in cart.
        if (isset($product->cartItem)) {
            $cartItemReference = $product->cartItem->options['extras']['upsale_reference_product'] ?? null;
            if (! $cartItemReference) {
                // This cart item doesn't have upsale reference, skip upsale pricing
                return $next($product);
            }
        }

        $originalProduct = $product->original_product;
        $upSaleProduct = null;
        $upSaleOriginalProduct = null;

        if ($originalProduct
            && key_exists($originalProductId = $originalProduct->getKey(), $this->appliedProducts)) {
            $upSaleOriginalProduct = $this->appliedProducts[$originalProductId];
        }

        if (key_exists($productId = $product->getKey(), $this->appliedProducts)) {
            $upSaleProduct = $this->appliedProducts[$productId];
        }

        if (! $upSaleProduct && ! $upSaleOriginalProduct) {
            return $next($product);
        }

        $pivot = null;

        if ($upSaleProduct && $upSaleProduct->pivot && $upSaleProduct->pivot->price) {
            $pivot = $upSaleProduct->pivot;
        } elseif ($upSaleOriginalProduct && $upSaleOriginalProduct->pivot && $upSaleOriginalProduct->pivot->price) {
            $pivot = $upSaleOriginalProduct->pivot;
        }

        if ($pivot) {
            $product = $this->calculateSalePrice($product, $pivot);
        }

        return $next($product);
    }

    protected function calculateSalePrice(Product $product, Pivot $pivot): Product
    {
        $price = (float) $pivot->price;
        $priceType = $pivot->price_type;
        $salePrice = $finalPrice = $product->getFinalPrice();

        if ($priceType == UpSellPriceType::FIXED) {
            $salePrice = $finalPrice - $price;
        } elseif ($priceType == UpSellPriceType::PERCENT) {
            $salePrice = $finalPrice - ($finalPrice * $price / 100);
        }

        if ($salePrice < $finalPrice) {
            $product->setFinalPrice($salePrice);
        }

        return $product;
    }
}
