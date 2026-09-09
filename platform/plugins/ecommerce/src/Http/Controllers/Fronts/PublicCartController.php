<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\AdsTracking\FacebookPixel;
use Botble\Ecommerce\AdsTracking\GoogleTagManager;
use Botble\Ecommerce\Cart\Cart as CartInstance;
use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Http\Requests\CartRequest;
use Botble\Ecommerce\Http\Requests\UpdateCartRequest;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\Option;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductAttribute;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Services\AbandonedCartService;
use Botble\Ecommerce\Services\HandleApplyCouponService;
use Botble\Ecommerce\Services\HandleApplyPromotionsService;
use Botble\Ecommerce\Services\HandleRemoveCouponService;
use Botble\Ecommerce\Services\Products\GetProductWithUpSalesBySlugService;
use Botble\Ecommerce\Services\Products\ProductUpSalePriceService;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Throwable;

class PublicCartController extends BaseController
{
    protected ?array $cachedCartData = null;

    protected ?object $cachedCartInstance = null;

    public function __construct(
        protected HandleApplyPromotionsService $applyPromotionsService,
        protected HandleApplyCouponService $handleApplyCouponService,
        protected HandleRemoveCouponService $handleRemoveCouponService
    ) {
    }

    protected function getCartInstance(): CartInstance
    {
        if ($this->cachedCartInstance === null) {
            $this->cachedCartInstance = Cart::instance('cart');
        }

        return $this->cachedCartInstance;
    }

    protected function persistCart(): void
    {
        if (auth('customer')->check()) {
            Cart::instance('cart')->storeForCustomerQuietly(auth('customer')->id());
        } else {
            $identifier = $this->getOrCreateGuestCartIdentifier();
            Cart::instance('cart')->updateOrStoreQuietly($identifier);
        }
    }

    protected function getOrCreateGuestCartIdentifier(): string
    {
        $identifier = request()->cookie('guest_cart_id');

        if (! $identifier) {
            $identifier = (string) Str::uuid();
            cookie()->queue('guest_cart_id', $identifier, 60 * 24 * 30);
        }

        return $identifier;
    }

    public function index(Request $request)
    {
        if ($token = $request->query('token')) {
            try {
                $abandonedCartService = app(AbandonedCartService::class);
                $abandonedCart = $abandonedCartService->recoverCart($token);

                if ($abandonedCart) {
                    session()->flash('success', __('Your cart has been restored! Complete your purchase now.'));
                }
            } catch (Throwable) {
                // Recovery failed — redirect to cart page gracefully
            }

            return redirect()->route('public.cart');
        }

        $promotionDiscountAmount = 0;
        $couponDiscountAmount = 0;

        $products = new Collection();
        $crossSellProducts = new Collection();

        if (Cart::instance('cart')->isNotEmpty()) {
            [$products, $promotionDiscountAmount, $couponDiscountAmount] = $this->getCartData();

            $crossSellProducts = get_cart_cross_sale_products(
                $products->pluck('original_product.id')->all(),
                (int) theme_option('number_of_cross_sale_product', 4)
            ) ?: new Collection();
        }

        $title = __('Shopping Cart');

        SeoHelper::setTitle(theme_option('ecommerce_cart_seo_title') ?: $title)
            ->setDescription(theme_option('ecommerce_cart_seo_description'));

        Theme::breadcrumb()->add($title, route('public.cart'));

        app(GoogleTagManager::class)->viewCart();

        return Theme::scope(
            'ecommerce.cart',
            compact('promotionDiscountAmount', 'couponDiscountAmount', 'products', 'crossSellProducts'),
            'plugins/ecommerce::themes.cart'
        )->render();
    }

    public function store(CartRequest $request)
    {
        $response = $this->httpResponse();

        /**
         * @var Product $product
         */
        $product = Product::query()
            ->find($request->input('id'));

        if (! $product) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/ecommerce::products.cart.product_not_exists'));
        }

        if ($product->variations->isNotEmpty() && ! $product->is_variation && $product->defaultVariation->product->id) {
            $product = $product->defaultVariation->product;
        }

        // Apply up-sale pricing if reference product is provided
        $isUpSaleBundle = false;
        if ($request->filled('reference_product_for_upsale')) {
            $referenceProductSlug = $request->input('reference_product_for_upsale');
            $referenceProduct = app(GetProductWithUpSalesBySlugService::class)
                ->handle($referenceProductSlug);

            // Security: Validate that the reference product is actually in the cart
            if ($referenceProduct && $this->isProductInCart($referenceProduct)) {
                // Get the parent product ID to check for any variation of this product
                $parentProductId = $product->original_product?->id ?? $product->id;

                // Check if any variation of this product already exists in cart with bundle discount (limit 1 product per bundle)
                $existingBundleItem = Cart::instance('cart')->content()->first(function ($item) use ($parentProductId, $referenceProductSlug) {
                    // Check if this cart item has the same upsale reference
                    if (($item->options['extras']['upsale_reference_product'] ?? null) !== $referenceProductSlug) {
                        return false;
                    }

                    // Check if this cart item belongs to the same parent product
                    $cartProduct = Product::query()->find($item->id);
                    if (! $cartProduct) {
                        return false;
                    }

                    $cartProductParentId = $cartProduct->original_product?->id ?? $cartProduct->id;

                    return $cartProductParentId == $parentProductId;
                });

                if ($existingBundleItem) {
                    return $response
                        ->setError()
                        ->setMessage(trans('plugins/ecommerce::products.cart.bundle_item_already_in_cart'));
                }

                app(ProductUpSalePriceService::class)->applyProduct($referenceProduct);
                $isUpSaleBundle = true;

                // Store reference in extras for cart persistence
                $extras = $request->input('extras', []);
                $extras['upsale_reference_product'] = $referenceProductSlug;
                $request->merge(['extras' => $extras]);

                // Force quantity to 1 for bundle items
                $request->merge(['qty' => 1]);
            }
        }

        // Disable auto-loading of up-sale context for non-bundle items to ensure regular price
        if (! $isUpSaleBundle) {
            app(ProductUpSalePriceService::class)->disableAutoLoad();
            app(ProductUpSalePriceService::class)->clearAppliedProducts();
        }

        $originalProduct = $product->original_product;

        if ($product->isOutOfStock()) {
            return $response
                ->setError()
                ->setMessage(
                    trans(
                        'plugins/ecommerce::products.cart.out_of_stock',
                        ['product' => $originalProduct->name ?: $product->name]
                    )
                );
        }

        try {
            do_action('ecommerce_before_add_to_cart', $product);
        } catch (Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }

        $maxQuantity = $product->max_cart_quantity;

        $requestQuantity = $request->integer('qty', 1);

        $existingAddedToCart = Cart::instance('cart')->content()->firstWhere('id', $product->id);

        if ($existingAddedToCart) {
            $requestQuantity += $existingAddedToCart->qty;
        }

        if (! $product->canAddToCart($requestQuantity)) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/ecommerce::products.cart.max_quantity_detail', ['quantity' => $maxQuantity, 'product' => $product->name]));
        }

        $outOfQuantity = false;
        $cartContent = Cart::instance('cart')->content();
        $existingItem = $cartContent->firstWhere('id', $product->id);

        if ($existingItem) {
            $originalQuantity = $product->quantity;
            $product->quantity = (int) $product->quantity - $existingItem->qty;

            if ($product->quantity < 0) {
                $product->quantity = 0;
            }

            if ($product->isOutOfStock()) {
                $outOfQuantity = true;
            }

            $product->quantity = $originalQuantity;
        }

        $product->quantity = (int) $product->quantity - $request->integer('qty', 1);

        if (
            EcommerceHelper::isEnabledProductOptions() &&
            DB::table('ec_options')
                ->where('product_id', $originalProduct->id)
                ->where('required', true)
                ->exists()
        ) {
            if (! $request->input('options')) {
                return $response
                    ->setError()
                    ->setData(['next_url' => $originalProduct->url])
                    ->setMessage(trans('plugins/ecommerce::products.cart.select_options'));
            }

            $requiredOptions = DB::table('ec_options')
                ->where('product_id', $originalProduct->id)
                ->where('required', true)
                ->get();

            $message = null;

            foreach ($requiredOptions as $requiredOption) {
                if (! $request->input('options.' . $requiredOption->id . '.values')) {
                    $message .= trans(
                        'plugins/ecommerce::product-option.add_to_cart_value_required',
                        ['value' => $requiredOption->name]
                    );
                }
            }

            if ($message) {
                return $response
                    ->setError()
                    ->setMessage(trans('plugins/ecommerce::products.cart.select_options'));
            }
        }

        if ($outOfQuantity) {
            return $response
                ->setError()
                ->setMessage(trans(
                    'plugins/ecommerce::products.cart.out_of_stock',
                    ['product' => $originalProduct->name ?: $product->name]
                ));
        }

        try {
            $cartItems = OrderHelper::handleAddCart($product, $request);
        } catch (Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }

        $cartItem = Arr::first(array_filter($cartItems, fn ($item) => $item['id'] == $product->id));

        $response->setMessage(trans(
            'plugins/ecommerce::products.cart.added_to_cart_success',
            ['product' => $originalProduct->name ?: $product->name]
        ));

        $responseData = [
            'status' => true,
            'content' => $cartItems,
            'extra_data' => app(GoogleTagManager::class)->formatProductTrackingData($originalProduct, $cartItem['qty']),
        ];

        app(GoogleTagManager::class)->addToCart(
            $originalProduct,
            $cartItem['qty'],
            $cartItem['subtotal'],
            $product->sku,
        );

        app(FacebookPixel::class)->addToCart(
            $originalProduct,
            $cartItem['qty'],
            $cartItem['subtotal'],
        );

        $token = OrderHelper::getOrderSessionToken();
        $nextUrl = route('public.checkout.information', $token);

        if (EcommerceHelper::getQuickBuyButtonTarget() == 'cart') {
            $nextUrl = route('public.cart');
        }

        if ($request->input('checkout')) {
            Cart::instance('cart')->refresh();

            $responseData['next_url'] = $nextUrl;

            $this->applyAutoCouponCode();
            $this->persistCart();

            if ($request->ajax() && $request->wantsJson()) {
                return $response->setData($responseData);
            }

            return $response
                ->setData($responseData)
                ->setNextUrl($nextUrl);
        }

        $this->persistCart();

        return $response
            ->setData([
                ...$this->getDataForResponse(),
                ...$responseData,
            ]);
    }

    public function addByUrl(Request $request, int|string $product)
    {
        $productModel = Product::query()
            ->where(function ($query) use ($product): void {
                $query->where('id', $product)
                    ->orWhere('slug', $product);
            })
            ->first();

        if (! $productModel) {
            return redirect()
                ->route('public.cart')
                ->with('error_msg', trans('plugins/ecommerce::products.cart.product_not_exists'));
        }

        $originalProduct = $productModel->original_product;

        if ($productModel->variations->isNotEmpty() && ! $productModel->is_variation) {
            $variationProduct = $this->findVariationFromRequest($request, $productModel);

            if ($variationProduct) {
                $productModel = $variationProduct;
            } elseif ($productModel->defaultVariation?->product?->id) {
                $productModel = $productModel->defaultVariation->product;
            }
        }

        if ($productModel->isOutOfStock()) {
            return redirect()
                ->route('public.cart')
                ->with('error_msg', trans(
                    'plugins/ecommerce::products.cart.out_of_stock',
                    ['product' => $originalProduct->name ?: $productModel->name]
                ));
        }

        $quantity = max(1, $request->integer('qty', 1));

        $existingAddedToCart = Cart::instance('cart')->content()->firstWhere('id', $productModel->id);

        $totalQuantity = $quantity;
        if ($existingAddedToCart) {
            $totalQuantity += $existingAddedToCart->qty;
        }

        if (! $productModel->canAddToCart($totalQuantity)) {
            return redirect()
                ->route('public.cart')
                ->with('error_msg', trans(
                    'plugins/ecommerce::products.cart.max_quantity_detail',
                    ['quantity' => $productModel->max_cart_quantity, 'product' => $productModel->name]
                ));
        }

        $options = [];
        if (EcommerceHelper::isEnabledProductOptions()) {
            $options = $this->parseOptionsFromRequest($request, $originalProduct);
        }

        try {
            do_action('ecommerce_before_add_to_cart', $productModel);
        } catch (Exception $e) {
            return redirect()
                ->route('public.cart')
                ->with('error_msg', $e->getMessage());
        }

        $request->merge([
            'qty' => $quantity,
            'id' => $productModel->id,
            'options' => $options,
        ]);

        try {
            OrderHelper::handleAddCart($productModel, $request);
        } catch (Exception $e) {
            return redirect()
                ->route('public.cart')
                ->with('error_msg', $e->getMessage());
        }

        $this->persistCart();

        return redirect()
            ->route('public.cart')
            ->with('success_msg', trans(
                'plugins/ecommerce::products.cart.added_to_cart_success',
                ['product' => $originalProduct->name ?: $productModel->name]
            ));
    }

    public function update(UpdateCartRequest $request)
    {
        if ($request->has('checkout')) {
            $token = OrderHelper::getOrderSessionToken();

            return $this
                ->httpResponse()
                ->setNextUrl(route('public.checkout.information', $token));
        }

        $data = $request->input('items', []);

        $outOfQuantity = false;
        foreach ($data as $item) {
            $cartItem = Cart::instance('cart')->get($item['rowId']);

            if (! $cartItem) {
                continue;
            }

            // Limit bundle items to quantity of 1
            $isBundleItem = ! empty($cartItem->options['extras']['upsale_reference_product']);
            if ($isBundleItem) {
                $item['values']['qty'] = 1;
            }

            /**
             * @var Product $product
             */
            $product = Product::query()->find($cartItem->id);

            if ($product) {
                $originalQuantity = $product->quantity;
                $product->quantity = (int) $product->quantity - (int) Arr::get($item, 'values.qty', 0) + 1;

                if ($product->quantity < 0) {
                    $product->quantity = 0;
                }

                if ($product->isOutOfStock()) {
                    $outOfQuantity = true;
                } else {
                    Cart::instance('cart')->update($item['rowId'], Arr::get($item, 'values'));
                }

                $product->quantity = $originalQuantity;
            }
        }

        if ($outOfQuantity) {
            return $this
                ->httpResponse()
                ->setError()
                ->setData($this->getDataForResponse())
                ->setMessage(trans('plugins/ecommerce::products.cart.not_enough_quantity'));
        }

        $this->persistCart();

        return $this
            ->httpResponse()
            ->setData($this->getDataForResponse())
            ->setMessage(trans('plugins/ecommerce::products.cart.updated_cart_success'));
    }

    public function destroy(string $id)
    {
        try {
            $cartItem = Cart::instance('cart')->get($id);

            if (! $cartItem) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage(trans('plugins/ecommerce::products.cart.item_not_found'));
            }

            $product = Product::query()->find($cartItem->id);

            $googleTagManager = app(GoogleTagManager::class);

            if ($product) {
                $trackingData = $googleTagManager->formatProductTrackingData($product->original_product, $cartItem->qty);
            }

            $googleTagManager->removeFromCart($cartItem);

            $removedProductSlug = $product?->original_product?->slug;

            Cart::instance('cart')->remove($id);

            if ($removedProductSlug) {
                $this->resetUpSaleItemsForRemovedParent($removedProductSlug);
            }

            $this->persistCart();

            // Mark the cart as freshly synced so the restore middleware
            // does not overwrite the session with stale DB content on the
            // next request — the DB updated_at is set after the session
            // updated_at, which the middleware interprets as "DB is newer"
            // and restores the old content (including the just-removed item).
            session(['cart_last_restored_at' => now()->toIso8601String()]);

            $responseData = [
                ...$this->getDataForResponse(),
            ];

            if (isset($trackingData)) {
                $responseData['extra_data'] = $trackingData;
            }

            return $this
                ->httpResponse()
                ->setData($responseData)
                ->setMessage(trans('plugins/ecommerce::products.cart.removed_from_cart_success', ['product' => $product?->name ?? '']));
        } catch (Throwable) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/ecommerce::products.cart.item_not_found'));
        }
    }

    protected function resetUpSaleItemsForRemovedParent(string $parentSlug): void
    {
        $cart = Cart::instance('cart');
        $cartContent = $cart->content();

        // Check if any other item in cart still references the same parent product
        // (e.g., another variation of the parent is still in cart)
        $parentProductIds = $cartContent->pluck('id')->filter()->unique()->toArray();

        if (! empty($parentProductIds)) {
            $parentProducts = Product::query()
                ->whereIn('id', $parentProductIds)
                ->with('variationInfo.configurableProduct')
                ->get();

            $parentStillInCart = $parentProducts->contains(function ($product) use ($parentSlug) {
                $originalProduct = $product->original_product;

                return $originalProduct && $originalProduct->slug === $parentSlug;
            });

            if ($parentStillInCart) {
                return;
            }
        }

        $itemsToReset = $cartContent->filter(function ($item) use ($parentSlug) {
            return ($item->options['extras']['upsale_reference_product'] ?? null) === $parentSlug;
        });

        if ($itemsToReset->isEmpty()) {
            return;
        }

        $productIds = $itemsToReset->pluck('id')->toArray();

        // Step 1: First remove the upsale_reference_product from all cart items
        // This prevents the pricing service from re-loading the up-sale context
        $itemData = [];
        foreach ($itemsToReset as $cartItem) {
            $options = $cartItem->options->toArray();
            unset($options['extras']['upsale_reference_product']);

            $itemData[$cartItem->id] = [
                'rowId' => $cartItem->rowId,
                'name' => $cartItem->name,
                'qty' => $cartItem->qty,
                'options' => $options,
            ];

            $cart->removeQuietly($cartItem->rowId);
            $cart->addQuietly($cartItem->id, $cartItem->name, $cartItem->qty, $cartItem->price, $options);
        }

        // Step 2: Clear the singleton state so it won't use cached up-sale prices
        app(ProductUpSalePriceService::class)->clearAppliedProducts();

        // Step 3: Get fresh prices (now without up-sale discount since reference is removed)
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        // Step 4: Update cart items with the correct prices
        $updatedContent = $cart->content();
        foreach ($updatedContent as $cartItem) {
            if (! isset($itemData[$cartItem->id])) {
                continue;
            }

            $product = $products->get($cartItem->id);

            if (! $product) {
                continue;
            }

            $originalPrice = $product->front_sale_price;

            // Only update if price actually changed
            if ($cartItem->price != $originalPrice) {
                $cart->removeQuietly($cartItem->rowId);
                $cart->addQuietly(
                    $cartItem->id,
                    $cartItem->name,
                    $cartItem->qty,
                    $originalPrice,
                    $cartItem->options->toArray()
                );
            }
        }
    }

    /**
     * Check if a product (or any of its variations) is in the cart.
     * Used for security validation of up-sale pricing.
     */
    protected function isProductInCart(Product $product): bool
    {
        $cart = Cart::instance('cart');
        $cartContent = $cart->content();

        if ($cartContent->isEmpty()) {
            return false;
        }

        $cartProductIds = $cartContent->pluck('id')->filter()->unique()->toArray();

        if (empty($cartProductIds)) {
            return false;
        }

        $cartProducts = Product::query()
            ->whereIn('id', $cartProductIds)
            ->with('variationInfo.configurableProduct')
            ->get();

        $productSlug = $product->slug;

        return $cartProducts->contains(function ($cartProduct) use ($productSlug, $product) {
            // Direct match
            if ($cartProduct->id == $product->id) {
                return true;
            }

            // Check if cart item is a variation of the product
            $originalProduct = $cartProduct->original_product;

            return $originalProduct && $originalProduct->slug === $productSlug;
        });
    }

    public function empty()
    {
        Cart::instance('cart')->destroy();

        if (auth('customer')->check()) {
            Cart::instance('cart')->deleteCustomerCart(auth('customer')->id());
        } else {
            // For guests, persist the empty cart state so the middleware
            // does not restore the stale DB content on the next request.
            $identifier = $this->getOrCreateGuestCartIdentifier();
            Cart::instance('cart')->updateOrStoreQuietly($identifier);
            session(['cart_last_restored_at' => now()->toIso8601String()]);
        }

        return $this
            ->httpResponse()
            ->setData(Cart::instance('cart')->content())
            ->setMessage(trans('plugins/ecommerce::products.cart.empty_success'));
    }

    protected function getCartData(): array
    {
        if ($this->cachedCartData !== null) {
            return $this->cachedCartData;
        }

        $cartInstance = $this->getCartInstance();
        $products = $cartInstance->products();

        $cartData = [
            'rawTotal' => $cartInstance->rawTotal(),
            'cartItems' => $cartInstance->content(),
            'countCart' => $cartInstance->count(),
            'productItems' => $products,
        ];

        $promotionDiscountAmount = $this->applyPromotionsService->execute(null, $cartData);

        $couponDiscountAmount = $this->applyAutoCouponCode();

        $sessionData = OrderHelper::getOrderSessionData();

        if (session()->has('applied_coupon_code')) {
            $sessionData['promotion_discount_amount'] = $promotionDiscountAmount;
            $appliedCouponCode = session('applied_coupon_code');
            $discount = $this->handleApplyCouponService->getCouponData($appliedCouponCode, $sessionData);

            if (! $discount) {
                // Coupon no longer exists or has expired - drop it.
                $this->handleRemoveCouponService->execute();
                $couponDiscountAmount = 0;
            } elseif ($discount->type_option == DiscountTypeOptionEnum::SHIPPING) {
                $couponDiscountAmount = (float) Arr::get($sessionData, 'coupon_discount_amount', 0);
            } else {
                // Re-apply against the current cart so the discount tracks quantity
                // changes and its conditions (minimum order, product eligibility, flash
                // sale) are re-checked; drop it when it no longer qualifies.
                $couponResult = $this->handleApplyCouponService->execute($appliedCouponCode, $sessionData, $cartData);

                if (Arr::get($couponResult, 'error')) {
                    $this->handleRemoveCouponService->execute();
                    $couponDiscountAmount = 0;
                } else {
                    $couponDiscountAmount = max((float) Arr::get($couponResult, 'data.discount_amount', 0), 0);
                }
            }
        }

        $this->cachedCartData = [$products, $promotionDiscountAmount, $couponDiscountAmount];

        return $this->cachedCartData;
    }

    protected function getDataForResponse(): array
    {
        $cartContent = null;

        $cartInstance = $this->getCartInstance();

        $cartData = $this->getCartData();

        [$products, $promotionDiscountAmount, $couponDiscountAmount] = $cartData;

        $cartCount = $cartInstance->count();
        $cartSubTotal = $cartInstance->rawSubTotal();
        $cartContentData = $cartInstance->content();

        if (Route::is('public.cart.*')) {
            $crossSellProducts = collect();
            if ($products->isNotEmpty()) {
                $productIds = $products->pluck('original_product.id')->filter()->unique()->all();

                if (! empty($productIds)) {
                    $crossSellProducts = get_cart_cross_sale_products(
                        $productIds,
                        (int) theme_option('number_of_cross_sale_product', 4)
                    ) ?: collect();
                }
            }

            $cartContent = view(
                EcommerceHelper::viewPath('cart'),
                compact('products', 'promotionDiscountAmount', 'couponDiscountAmount', 'crossSellProducts')
            )->render();
        }

        $additionalData = apply_filters('ecommerce_cart_additional_data', [], $cartData);

        return apply_filters('ecommerce_cart_data_for_response', [
            'count' => $cartCount,
            'total_price' => format_price($cartSubTotal),
            'content' => $cartContentData,
            'cart_content' => $cartContent,
            ...$additionalData,
        ], $cartData);
    }

    protected function applyAutoCouponCode(): float
    {
        $couponDiscountAmount = 0;

        if ($couponCode = session('auto_apply_coupon_code')) {
            $coupon = Discount::query()
                ->where('code', $couponCode)
                ->where('apply_via_url', true)
                ->where('type', DiscountTypeEnum::COUPON)
                ->exists();

            if ($coupon) {
                $couponData = $this->handleApplyCouponService->execute($couponCode);

                if (! Arr::get($couponData, 'error')) {
                    $couponDiscountAmount = Arr::get($couponData, 'data.discount_amount', 0);
                }
            }
        }

        return (float) $couponDiscountAmount;
    }

    public function unsubscribe(string $token)
    {
        $abandonedCartService = app(AbandonedCartService::class);
        $result = $abandonedCartService->unsubscribe($token);

        SeoHelper::setTitle(__('Unsubscribe'));

        if ($result) {
            return Theme::scope(
                'ecommerce.abandoned-cart-unsubscribed',
                ['success' => true],
                'plugins/ecommerce::themes.abandoned-cart-unsubscribed'
            )->render();
        }

        return Theme::scope(
            'ecommerce.abandoned-cart-unsubscribed',
            ['success' => false],
            'plugins/ecommerce::themes.abandoned-cart-unsubscribed'
        )->render();
    }

    protected function findVariationFromRequest(Request $request, Product $product): ?Product
    {
        $attributeSets = $product->productAttributeSets()->get(['ec_product_attribute_sets.id', 'ec_product_attribute_sets.slug']);

        if ($attributeSets->isEmpty()) {
            return null;
        }

        $attributeIds = [];

        foreach ($attributeSets as $attributeSet) {
            $attributeSlug = $request->query($attributeSet->slug);

            if (! $attributeSlug) {
                continue;
            }

            $attribute = ProductAttribute::query()
                ->where('attribute_set_id', $attributeSet->id)
                ->where('slug', $attributeSlug)
                ->first();

            if ($attribute) {
                $attributeIds[] = $attribute->id;
            }
        }

        if (empty($attributeIds)) {
            return null;
        }

        $variation = ProductVariation::getVariationByAttributes($product->id, $attributeIds);

        return $variation?->product;
    }

    protected function parseOptionsFromRequest(Request $request, Product $product, bool $useDefaults = true): array
    {
        $productOptions = Option::query()
            ->where('product_id', $product->id)
            ->with('values')
            ->get();

        if ($productOptions->isEmpty()) {
            return [];
        }

        $options = [];

        foreach ($productOptions as $option) {
            $optionSlug = Str::slug($option->name);
            $requestValue = $request->query($optionSlug);

            $matchedValues = [];

            if ($requestValue !== null) {
                if (is_array($requestValue)) {
                    foreach ($requestValue as $value) {
                        $matchedValue = $option->values->first(
                            fn ($v) => Str::slug($v->option_value) === Str::slug($value)
                        );
                        if ($matchedValue) {
                            $matchedValues[] = $matchedValue->option_value;
                        }
                    }
                } else {
                    $matchedValue = $option->values->first(
                        fn ($v) => Str::slug($v->option_value) === Str::slug($requestValue)
                    );
                    if ($matchedValue) {
                        $matchedValues = $matchedValue->option_value;
                    }
                }
            } elseif ($useDefaults && $option->values->isNotEmpty()) {
                $firstValue = $option->values->first();
                if ($firstValue) {
                    $matchedValues = $firstValue->option_value;
                }
            }

            if (! empty($matchedValues)) {
                $options[$option->id] = [
                    'option_type' => $option->option_type,
                    'values' => $matchedValues,
                ];
            }
        }

        return $options;
    }
}
