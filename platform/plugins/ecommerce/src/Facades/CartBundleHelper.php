<?php

namespace Botble\Ecommerce\Facades;

use Botble\Ecommerce\Supports\CartBundleHelper as CartBundleHelperSupport;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isBundleItem(object $cartItem)
 * @method static string|null getBundleReference(object $cartItem)
 * @method static \Botble\Ecommerce\Models\Product|null getBundleReferenceProduct(object $cartItem)
 * @method static string renderBundleBadge(object $cartItem)
 * @method static array getQuantityAttributes(object $cartItem, \Botble\Ecommerce\Models\Product $product, string $inputName = 'qty')
 * @method static bool shouldShowQuantityControls(object $cartItem)
 * @method static string buildAttributesString(array $attributes)
 * @method static void clearCache()
 *
 * @see \Botble\Ecommerce\Supports\CartBundleHelper
 */
class CartBundleHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CartBundleHelperSupport::class;
    }
}
