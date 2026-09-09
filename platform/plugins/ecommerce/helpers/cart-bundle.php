<?php

use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Supports\CartBundleHelper;

if (! function_exists('is_cart_bundle_item')) {
    /**
     * Check if a cart item is a bundle item (has up-sale reference).
     */
    function is_cart_bundle_item(object $cartItem): bool
    {
        return app(CartBundleHelper::class)->isBundleItem($cartItem);
    }
}

if (! function_exists('get_cart_bundle_reference')) {
    /**
     * Get the bundle reference slug from a cart item.
     */
    function get_cart_bundle_reference(object $cartItem): ?string
    {
        return app(CartBundleHelper::class)->getBundleReference($cartItem);
    }
}

if (! function_exists('get_cart_bundle_reference_product')) {
    /**
     * Get the reference product for a bundle item.
     */
    function get_cart_bundle_reference_product(object $cartItem): ?Product
    {
        return app(CartBundleHelper::class)->getBundleReferenceProduct($cartItem);
    }
}

if (! function_exists('render_cart_bundle_badge')) {
    /**
     * Render the bundle badge HTML.
     */
    function render_cart_bundle_badge(object $cartItem): string
    {
        return app(CartBundleHelper::class)->renderBundleBadge($cartItem);
    }
}

if (! function_exists('get_cart_quantity_attributes')) {
    /**
     * Get quantity input attributes for a cart item.
     */
    function get_cart_quantity_attributes(object $cartItem, Product $product, string $inputName = 'qty'): array
    {
        return app(CartBundleHelper::class)->getQuantityAttributes($cartItem, $product, $inputName);
    }
}

if (! function_exists('should_show_cart_quantity_controls')) {
    /**
     * Check if quantity controls should be shown for a cart item.
     */
    function should_show_cart_quantity_controls(object $cartItem): bool
    {
        return app(CartBundleHelper::class)->shouldShowQuantityControls($cartItem);
    }
}

if (! function_exists('build_cart_item_attributes_string')) {
    /**
     * Build HTML attributes string from array.
     */
    function build_cart_item_attributes_string(array $attributes): string
    {
        return app(CartBundleHelper::class)->buildAttributesString($attributes);
    }
}
