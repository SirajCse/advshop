<?php

namespace Botble\Ecommerce\AdsTracking;

use Botble\Ecommerce\Cart\CartItem;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\SeoHelper\Facades\SeoHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class GoogleTagManager
{
    protected array $dataLayer = [];

    public function isEnabled(): bool
    {
        $enabled = get_ecommerce_setting('google_tag_manager_enabled', false);

        if (is_string($enabled)) {
            $enabled = $enabled === '1' || $enabled === 'true';
        }

        if (! $enabled) {
            return false;
        }

        $type = setting('google_tag_manager_type');

        if (! $type) {
            if (setting('gtm_container_id')) {
                $type = 'gtm';
            } elseif (setting('google_tag_manager_code')) {
                $type = 'code';
            } elseif (setting('custom_tracking_header_js') || setting('custom_tracking_body_html')) {
                $type = 'custom';
            } elseif (setting('google_tag_manager_id') || setting('google_analytics')) {
                $type = 'id';
            } else {
                return false;
            }
        }

        return match ($type) {
            'gtm' => (bool) setting('gtm_container_id'),
            'id' => (setting('google_tag_manager_id') || setting('google_analytics')),
            'custom' => (
                setting('custom_tracking_header_js') ||
                setting('custom_tracking_body_html') ||
                setting('google_tag_manager_code')
            ),
            'code' => (
                setting('google_tag_manager_code') ||
                setting('custom_tracking_header_js') ||
                setting('custom_tracking_body_html')
            ),
            default => (
                setting('gtm_container_id') ||
                setting('google_tag_manager_id') ||
                setting('google_analytics') ||
                setting('google_tag_manager_code') ||
                setting('custom_tracking_header_js') ||
                setting('custom_tracking_body_html')
            ),
        };
    }

    /**
     * Whether ecommerce events should be emitted via gtag('event', ...). Only a
     * direct GA4 / gtag.js install (type "id") defines a global gtag() and expects
     * that shape; GTM-container and custom-code setups read the raw flat dataLayer
     * push. Resolving this once per site (not per page) keeps the event schema
     * identical across the whole funnel.
     */
    public function shouldUseGtag(): bool
    {
        if (setting('gtm_container_id')) {
            return false;
        }

        $type = setting('google_tag_manager_type');

        if ($type) {
            return $type === 'id';
        }

        return (bool) (setting('google_tag_manager_id') || setting('google_analytics'));
    }

    public function viewItemList(array $items, string $name, array $attributes = []): self
    {
        $this->pushEvent('view_item_list', $items, [
            'item_list_id' => Str::snake($name),
            'item_list_name' => $name,
            ...$attributes,
        ]);

        return $this;
    }

    public function viewCategory(ProductCategory $category, int $productCount, array $attributes = []): self
    {
        if (! $this->isEnabled()) {
            return $this;
        }

        $this->dataLayer['category_view'] = [
            'categoryId' => (string) $category->getKey(),
            'categoryName' => $category->name,
            'productCount' => $productCount,
            ...$attributes,
        ];

        return $this;
    }

    public function viewItem(Product $item, array $attributes = []): self
    {
        $this->pushEvent('view_item', [$item], [
            'currency' => get_application_currency()->title,
            'value' => (float) $item->front_sale_price,
            ...$attributes,
        ]);

        return $this;
    }

    public function selectItem(Product $item, string $listName = '', int $index = 0, array $attributes = []): self
    {
        $this->pushEvent('select_item', [$item], [
            'item_list_id' => Str::snake($listName),
            'item_list_name' => $listName,
            'index' => $index,
            ...$attributes,
        ]);

        return $this;
    }

    public function search(string $searchTerm, array $items = [], array $attributes = []): self
    {
        $this->pushEvent('search', $items, [
            'search_term' => $searchTerm,
            ...$attributes,
        ]);

        return $this;
    }

    public function viewPromotion(array $items, string $promotionId = '', string $promotionName = '', array $attributes = []): self
    {
        $this->pushEvent('view_promotion', $items, [
            'promotion_id' => $promotionId,
            'promotion_name' => $promotionName,
            ...$attributes,
        ]);

        return $this;
    }

    public function selectPromotion(array $items, string $promotionId = '', string $promotionName = '', array $attributes = []): self
    {
        $this->pushEvent('select_promotion', $items, [
            'promotion_id' => $promotionId,
            'promotion_name' => $promotionName,
            ...$attributes,
        ]);

        return $this;
    }

    public function addToCart(Product $item, int $quantity, float $value, ?string $sku = null, array $attributes = []): self
    {
        // Use a dedicated transient attribute so the GA4 line quantity never collides
        // with the product's stock quantity (ec_products.quantity).
        $item->gtm_quantity = $quantity;

        // Callers pass the parent (original) product so name/brand/category resolve,
        // but a variable product's parent has no SKU. Carry the variation SKU through
        // a transient attribute so item_id stays identical to view_item/purchase
        // ("XI-165") instead of falling back to the numeric product id.
        if ($sku) {
            $item->gtm_sku = $sku;
        }

        $this->pushEvent('add_to_cart', [$item], [
            'currency' => get_application_currency()->title,
            'value' => $value,
            ...$attributes,
        ]);

        return $this;
    }

    public function viewCart(array $attributes = []): self
    {
        $cart = Cart::instance('cart');
        $products = $cart->products();

        $items = $cart->content()->map(function ($item) use ($products) {
            /**
             * @var Collection $products
             */
            $product = $products->find($item->id)->original_product;

            return new GoogleTagItem(
                $item->sku ?: $item->id,
                $item->name,
                $item->price,
                $item->qty,
                [
                    ...$this->formatItemAttributes($product),
                    'item_variant' => $item->options->attributes,
                ]
            );
        })->values()->all();

        $this->pushEvent('view_cart', $items, [
            'currency' => get_application_currency()->title,
            'value' => $cart->rawSubTotal(),
            ...$attributes,
        ]);

        return $this;
    }

    public function removeFromCart(CartItem $cartItem, array $attributes = []): self
    {
        $product = Product::query()->find($cartItem->id);

        if (! $product) {
            return $this;
        }

        $product = $product->original_product;
        $product->gtm_quantity = $cartItem->qty;

        $this->pushEvent('remove_from_cart', [$product], [
            'currency' => get_application_currency()->title,
            'value' => $cartItem->price * $cartItem->qty,
            ...$attributes,
        ]);

        return $this;
    }

    public function beginCheckout(array $items, float $value, ?string $coupon = null, array $attributes = []): self
    {
        // The passed products carry their stock quantity; map the real cart line
        // quantity (keyed by product id) so begin_checkout reports the cart qty.
        $quantities = Cart::instance('cart')->content()->pluck('qty', 'id');

        foreach ($items as $item) {
            if ($item instanceof Product) {
                $item->gtm_quantity = (int) ($quantities[$item->getKey()] ?? 1);
            }
        }

        // GA4 requires the event value to equal the sum of (price x quantity) of the
        // items array. The caller's $orderAmount is the grand total (after discount,
        // tax adjustments and currency conversion) and does not reconcile with the
        // gross line prices shown in items, so derive the value from the items here.
        $this->pushEvent('begin_checkout', $items, [
            'currency' => get_application_currency()->title,
            'value' => $this->computeItemsValue($items),
            'coupon' => $coupon,
            ...$attributes,
        ]);

        return $this;
    }

    public function purchase(Order $order, array $attributes = [], array $products = []): self
    {
        $products = $products ?: $order->getOrderProducts()->all();

        // getOrderProducts() returns Product models carrying stock quantity; map the
        // ordered line quantity (from ec_order_product.qty) keyed by product id.
        $quantities = $order->products->pluck('qty', 'product_id');

        foreach ($products as $product) {
            if ($product instanceof Product) {
                $product->gtm_quantity = (int) ($quantities[$product->getKey()] ?? 1);
            }
        }

        // value = the net grand total actually charged (after discount, incl. tax and
        // shipping) so ad-platform ROAS reflects real revenue, not the gross items sum.
        // discount exposes the deducted amount at event level. Cast all to float - the
        // Order model returns decimal-cast values as JSON strings otherwise.
        $purchaseAttributes = [
            'transaction_id' => $order->code,
            'currency' => get_application_currency()->title,
            'value' => (float) $order->amount,
            'tax' => (float) $order->tax_amount,
            'shipping' => (float) $order->shipping_amount,
            'discount' => (float) $order->discount_amount,
            'coupon' => $order->coupon_code,
            ...$attributes,
        ];

        if ($userData = $this->buildUserData($order)) {
            $purchaseAttributes['user_data'] = $userData;
        }

        $this->pushEvent('purchase', $products, $purchaseAttributes);

        return $this;
    }

    /**
     * Build the user_data object for Google Ads Enhanced Conversions / Meta Advanced
     * Matching. Returns plain (unhashed) normalised values for the GTM tags to hash.
     * Opt-in only - exposes customer PII in the page dataLayer, so gated behind the
     * gtm_user_data_enabled setting.
     */
    protected function buildUserData(Order $order): array
    {
        if (! setting('gtm_user_data_enabled', false)) {
            return [];
        }

        $address = $order->shippingAddress->getKey() ? $order->shippingAddress : $order->address;

        if (! $address || ! $address->getKey()) {
            return [];
        }

        $fullName = trim((string) $address->name);
        $firstName = $fullName !== '' ? explode(' ', $fullName)[0] : '';

        $userData = array_filter([
            // external_id = the logged-in customer's id, SHA256-hashed. Meta Advanced
            // Matching expects external_id pre-hashed (unlike email/phone/address, which
            // GA4/GTM hash automatically), and hashing avoids leaking the raw database id
            // into the page dataLayer while keeping a stable per-user identifier. Guest
            // orders have no stable user id, so it is omitted.
            'external_id' => $order->user_id ? hash('sha256', (string) $order->user_id) : '',
            'email' => strtolower(trim((string) ($address->email ?: ($order->user?->email ?? '')))),
            'phone_number' => preg_replace('/\s+/', '', (string) $address->phone),
            'first_name' => $firstName,
            'address' => trim((string) $address->address),
            // city/state are stored as location-plugin IDs when the location plugin is
            // active; the *_name accessors resolve them to text names and fall back to the
            // raw value when the store uses free-text addresses.
            'city' => trim((string) $address->city_name),
            'state' => trim((string) $address->state_name),
            // postal_code + country complete the address block for Enhanced Conversions /
            // Advanced Matching. country is sent as the raw 2-letter ISO code (GA4 expects
            // the ISO code here, not the resolved country name).
            'postal_code' => trim((string) $address->zip_code),
            'country' => trim((string) $address->country),
        ], fn ($value) => $value !== '' && $value !== null);

        return $userData;
    }

    public function refund(Order $order, array $attributes = []): self
    {
        $products = $order->getOrderProducts();

        $items = $products->all();

        $this->pushEvent('refund', $items, [
            'transaction_id' => $order->code,
            'currency' => get_application_currency()->title,
            'value' => (float) $order->amount,
            'tax' => (float) $order->tax_amount,
            'shipping' => (float) $order->shipping_amount,
            'discount' => (float) $order->discount_amount,
            'coupon' => $order->coupon_code,
            ...$attributes,
        ]);

        return $this;
    }

    public function signUp(string $method = 'email', array $attributes = []): self
    {
        if (! $this->isEnabled()) {
            return $this;
        }

        $this->dataLayer['sign_up'] = [
            'method' => $method,
            ...$attributes,
        ];

        return $this;
    }

    public function pushEvent(string $event, array|\Illuminate\Support\Collection $items, array $attributes = []): self
    {
        if (! $this->isEnabled()) {
            return $this;
        }

        if ($items instanceof Collection) {
            $firstItem = $items->first();
            if ($firstItem instanceof Product) {
                $items->loadMissing(['brand', 'categories']);
            }
            $items = $items->all();
        }

        $items = array_map(fn (GoogleTagItem $item) => $item->toArray(), $this->formatItems($items));

        $data = apply_filters('ecommerce.google_tag_manager.push_event', [
            ...$attributes,
            'items' => $items,
        ], $event, $items, $attributes);

        $this->dataLayer[$event] = $data;

        return $this;
    }

    public function render(): string
    {
        if (empty($this->dataLayer)) {
            return '';
        }

        $pushes = '';

        // Choose ONE push mechanism for the whole site so every event in the funnel
        // shares the same schema. Deciding per-event at runtime (typeof gtag) made
        // pages that had a global gtag emit the GA4 eventModel wrapper while pages
        // without it pushed flat objects - the exact mismatch reported across
        // view_item/add_to_cart vs begin_checkout/purchase.
        $useGtag = $this->shouldUseGtag();

        foreach ($this->dataLayer as $event => $data) {
            $eventName = json_encode($event);

            if ($useGtag) {
                $eventData = json_encode($data);
                $pushes .= <<<JS
                    gtag('event', $eventName, $eventData);
                JS;
            } else {
                $dataLayerEvent = json_encode(['event' => $event, ...$data]);
                $pushes .= <<<JS
                    window.dataLayer.push($dataLayerEvent);
                JS;
            }
        }

        return <<<HTML
            <script>
                window.dataLayer = window.dataLayer || [];
                $pushes
            </script>
        HTML;
    }

    public function pushScriptsToFooter(): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        add_filter(THEME_FRONT_FOOTER, function (?string $html) {
            return $html . view(EcommerceHelper::viewPath('includes.gtm-script'))->render() . $this->render();
        }, 999);

        add_filter('ecommerce_checkout_footer', function (?string $html) {
            return $html . SeoHelper::meta()->getAnalytics()->render() . $this->render();
        }, 999);
    }

    public function formatItems(array|\Illuminate\Support\Collection $items): array
    {
        if ($items instanceof \Illuminate\Support\Collection) {
            $items = $items->all();
        }

        $productsToLoad = array_filter($items, fn ($item) => ! ($item instanceof GoogleTagItem));

        if (! empty($productsToLoad)) {
            $brandIds = array_unique(array_filter(array_map(fn ($item) => $item->brand_id, $productsToLoad)));

            if (! empty($brandIds)) {
                $brands = Brand::query()
                    ->whereIn('id', $brandIds)
                    ->get()
                    ->keyBy('id');

                foreach ($productsToLoad as $product) {
                    if ($product->brand_id && $brands->has($product->brand_id)) {
                        $product->setRelation('brand', $brands->get($product->brand_id));
                    }
                }
            }

            $productsNeedingCategories = array_filter($productsToLoad, fn ($item) => ! $item->relationLoaded('categories'));
            if (! empty($productsNeedingCategories)) {
                $productIds = array_map(fn ($item) => $item->id, $productsNeedingCategories);
                $categories = ProductCategory::query()
                    ->join('ec_product_category_product', 'ec_product_categories.id', '=', 'ec_product_category_product.category_id')
                    ->whereIn('ec_product_category_product.product_id', $productIds)
                    ->select('ec_product_categories.*', 'ec_product_category_product.product_id')
                    ->get()
                    ->groupBy('product_id');

                foreach ($productsNeedingCategories as $product) {
                    if ($categories->has($product->id)) {
                        $product->setRelation('categories', $categories->get($product->id));
                    }
                }
            }
        }

        return array_map(function ($item) {
            if ($item instanceof GoogleTagItem) {
                return $item;
            }

            return new GoogleTagItem(
                id: $this->resolveItemId($item),
                name: $item->name,
                price: $this->resolveItemPrice($item),
                quantity: $this->resolveQuantity($item),
                attributes: $this->formatItemAttributes($item),
            );
        }, $items);
    }

    /**
     * Resolve the effective price the customer sees and pays - the active sale /
     * flash-sale / discount price (front_sale_price), not the struck-through regular
     * price. Falls back to the base price only when no sale price is available.
     */
    protected function resolveItemPrice(Product $item): float
    {
        return (float) ($item->front_sale_price ?: $item->price ?: 0);
    }

    /**
     * Resolve a stable item_id used identically across every event. Prefers the
     * transient gtm_sku (the variation SKU carried by add_to_cart when the parent
     * product has none), then the product's own SKU, falling back to the numeric
     * key only when no SKU exists.
     */
    protected function resolveItemId(Product $item): string|int
    {
        $gtmSku = $item->getAttribute('gtm_sku');

        if ($gtmSku) {
            return $gtmSku;
        }

        return $item->sku ?: $item->getKey();
    }

    /**
     * Sum (price x quantity) across the given items so the GA4 event `value`
     * reconciles exactly with the items array. Accepts raw Product models (line
     * quantity resolved the same way formatItems does) or pre-built GoogleTagItems.
     */
    protected function computeItemsValue(array $items): float
    {
        $value = 0.0;

        foreach ($items as $item) {
            if ($item instanceof GoogleTagItem) {
                $data = $item->toArray();
                $value += (float) ($data['price'] ?? 0) * (int) ($data['quantity'] ?? 1);

                continue;
            }

            if ($item instanceof Product) {
                $value += $this->resolveItemPrice($item) * $this->resolveQuantity($item);
            }
        }

        return round($value, 2);
    }

    /**
     * Resolve the GA4 line quantity for an item without ever leaking the product's
     * stock quantity (ec_products.quantity). Prefers the transient gtm_quantity set
     * by cart/checkout/purchase flows, then an order line `qty`, otherwise 1.
     */
    protected function resolveQuantity(Product $item): int
    {
        $gtmQuantity = $item->getAttribute('gtm_quantity');

        if ($gtmQuantity !== null) {
            return (int) $gtmQuantity;
        }

        $lineQuantity = $item->getAttribute('qty');

        if ($lineQuantity !== null) {
            return (int) $lineQuantity;
        }

        return 1;
    }

    public function formatItemAttributes(Product $product): array
    {
        $attributes = [];

        if ($product->brand) {
            $attributes['item_brand'] = $product->brand->name;
        }

        // Category pivot rows (ec_product_category_product) attach to the PARENT
        // product only; a variation row has none. begin_checkout and purchase carry
        // variation products (getOrderProducts / the cart line resolve to is_variation
        // rows), so resolve categories from the parent to keep item_category present
        // across the whole funnel - add_to_cart / view_item already pass the parent,
        // which is why simple products track everywhere but variable products lost
        // their category at checkout/purchase.
        $categoryProduct = $product->is_variation ? $product->original_product : $product;

        if ($categoryProduct && $categoryProduct->categories) {
            // Reset to 0-based keys. After Order::getOrderProducts() loads categories
            // via groupBy()->get($id), Laravel preserves the original collection keys,
            // so the 2nd product's first category would otherwise start at key 2+ and
            // its base item_category would be dropped on multi-item orders.
            foreach (collect($categoryProduct->categories)->values() as $key => $category) {
                $keyName = $key === 0 ? '' : $key + 1;
                $attributes["item_category$keyName"] = $category->name;
            }
        }

        return $attributes;
    }

    public function formatProductTrackingData(Product $product, int $quantity = 1): array
    {
        $product = $product->original_product;

        $attributes = $this->formatItemAttributes($product);

        $categories = $product->categories;
        if ($categories && $categories->isNotEmpty()) {
            $categoryNames = $categories->pluck('name')->toArray();
            foreach ($categoryNames as $index => $categoryName) {
                $key = $index === 0 ? 'item_category' : "item_category{$index}";
                $attributes[$key] = $categoryName;
            }
        }

        return [
            'item_id' => $product->sku ?: $product->getKey(),
            'item_name' => $product->name,
            'price' => $this->resolveItemPrice($product),
            'quantity' => $quantity,
            'item_brand' => $product->brand?->name ?? '',
            ...$attributes,
        ];
    }
}
