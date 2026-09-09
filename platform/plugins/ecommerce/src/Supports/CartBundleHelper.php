<?php

namespace Botble\Ecommerce\Supports;

use Botble\Ecommerce\Models\Product;
use Illuminate\Support\Arr;

class CartBundleHelper
{
    protected array $referenceProductCache = [];

    /**
     * Check if a cart item is a bundle item (has up-sale reference).
     */
    public function isBundleItem(object $cartItem): bool
    {
        return ! empty($this->getBundleReference($cartItem));
    }

    /**
     * Get the bundle reference slug from a cart item.
     */
    public function getBundleReference(object $cartItem): ?string
    {
        return Arr::get($cartItem->options ?? [], 'extras.upsale_reference_product');
    }

    /**
     * Get the reference product for a bundle item.
     */
    public function getBundleReferenceProduct(object $cartItem): ?Product
    {
        $reference = $this->getBundleReference($cartItem);

        if (! $reference) {
            return null;
        }

        if (isset($this->referenceProductCache[$reference])) {
            return $this->referenceProductCache[$reference];
        }

        $product = Product::query()
            ->where('slug', $reference)
            ->first();

        $this->referenceProductCache[$reference] = $product;

        return $product;
    }

    /**
     * Render the bundle badge HTML.
     */
    public function renderBundleBadge(object $cartItem): string
    {
        if (! $this->isBundleItem($cartItem)) {
            return '';
        }

        $reference = $this->getBundleReference($cartItem);
        $referenceProduct = $this->getBundleReferenceProduct($cartItem);

        return view('plugins/ecommerce::themes.includes.cart-bundle-badge', [
            'reference' => $reference,
            'referenceProduct' => $referenceProduct,
        ])->render();
    }

    /**
     * Get quantity input attributes for a cart item.
     * Returns an array of HTML attributes.
     */
    public function getQuantityAttributes(object $cartItem, Product $product, string $inputName = 'qty'): array
    {
        $isBundleItem = $this->isBundleItem($cartItem);

        $attributes = [
            'name' => $inputName,
            'type' => 'number',
            'value' => $isBundleItem ? 1 : $cartItem->qty,
        ];

        if ($isBundleItem) {
            $attributes['min'] = 1;
            $attributes['max'] = 1;
            $attributes['readonly'] = 'readonly';
            $attributes['disabled'] = false;
            $attributes['style'] = 'pointer-events: none; background: #f5f5f5;';
            $attributes['data-bundle-item'] = 'true';
        } else {
            $attributes['min'] = $product->min_cart_quantity ?? 1;
            $attributes['max'] = $product->max_cart_quantity ?? 999;
            $attributes['data-bb-toggle'] = 'update-cart';
        }

        return $attributes;
    }

    /**
     * Check if quantity controls should be shown for a cart item.
     * Bundle items should not have +/- buttons.
     */
    public function shouldShowQuantityControls(object $cartItem): bool
    {
        return ! $this->isBundleItem($cartItem);
    }

    /**
     * Build HTML attributes string from array.
     */
    public function buildAttributesString(array $attributes): string
    {
        $parts = [];

        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $parts[] = $key;
            } elseif ($value !== false && $value !== null) {
                $parts[] = $key . '="' . e($value) . '"';
            }
        }

        return implode(' ', $parts);
    }

    /**
     * Clear the reference product cache.
     */
    public function clearCache(): void
    {
        $this->referenceProductCache = [];
    }
}
