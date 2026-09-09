@if(request()->ajax() && isset($products))
    @if($products->isNotEmpty())
        @php
            $cartContent = Cart::instance('cart')->content();
            $cartProductIds = $cartContent->pluck('id')->filter()->unique()->toArray();
            $parentProductInCart = false;

            if (!empty($cartProductIds)) {
                $cartProducts = \Botble\Ecommerce\Models\Product::query()
                    ->whereIn('id', $cartProductIds)
                    ->with('variationInfo.configurableProduct')
                    ->get();

                $parentProductInCart = $cartProducts->contains(function ($product) use ($parentProduct) {
                    if ($product->id == $parentProduct->id) {
                        return true;
                    }
                    if ($product->is_variation && $product->variationInfo && $product->variationInfo->configurable_product_id) {
                        return $product->variationInfo->configurable_product_id == $parentProduct->id;
                    }
                    return false;
                });
            }

            $currency = get_application_currency();
            $currencyConfig = [
                'symbol' => $currency->symbol,
                'is_prefix' => $currency->is_prefix_symbol,
                'decimals' => $currency->decimals,
                'thousands_separator' => $currency->thousands_separator,
                'decimal_separator' => $currency->decimal_separator,
            ];
        @endphp
        <section class="ec-upsell-bundle" data-upsale-bundle data-currency-config="{{ json_encode($currencyConfig) }}">
            <div class="container">
                <div class="ec-upsell-bundle-wrapper">
                    <div class="ec-upsell-bundle-header">
                        <div class="ec-upsell-bundle-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                        </div>
                        <div class="ec-upsell-bundle-title">
                            <h4>{{ trans('plugins/ecommerce::products.up_sale.title') }}</h4>
                            <p>{{ trans('plugins/ecommerce::products.up_sale.description') }}</p>
                        </div>
                    </div>

                    <div class="ec-upsell-bundle-list">
                        @foreach ($products as $product)
                            @php
                                $productVariation = $product;
                                $selectedAttrs = collect();
                                $productImage = $product->image;

                                if ($product->variations->isNotEmpty()) {
                                    [$productImages, $productVariation, $selectedAttrs] = \Botble\Ecommerce\Facades\EcommerceHelper::getProductVariationInfo($product);
                                    if ($productImages && count($productImages) > 0) {
                                        $productImage = $productImages[0];
                                    }
                                }

                                $originalPrice = $productVariation->price()->getPriceOriginal();
                                $salePrice = $productVariation->price()->getPrice();

                                $bundleDiscountPrice = $product->pivot->price ?? 0;
                                $bundleDiscountType = $product->pivot->price_type ?? 'fixed';
                                $isPercentDiscount = $bundleDiscountType === 'percent' || $bundleDiscountType === \Botble\Ecommerce\Enums\UpSellPriceType::PERCENT;

                                $bundlePrice = $salePrice;
                                if ($bundleDiscountPrice > 0) {
                                    if ($isPercentDiscount) {
                                        $bundlePrice = $salePrice - ($salePrice * $bundleDiscountPrice / 100);
                                    } else {
                                        $bundlePrice = max(0, $salePrice - $bundleDiscountPrice);
                                    }
                                }

                                $hasDiscount = $bundlePrice < $salePrice || $salePrice < $originalPrice;
                                $showOriginalPrice = $bundlePrice < $salePrice ? $salePrice : ($salePrice < $originalPrice ? $originalPrice : null);

                                $displayPrice = $bundlePrice;
                                if (!$currency->is_default && $currency->exchange_rate > 0) {
                                    $displayPrice = $bundlePrice * $currency->exchange_rate;
                                }

                                $cartId = $productVariation->is_variation ? $productVariation->id : $product->id;
                            @endphp
                            <div
                                @class([
                                    'ec-upsell-bundle-item',
                                    'has-variations' => $product->variations->isNotEmpty(),
                                    'is-locked' => ! $parentProductInCart,
                                ])
                                data-product-id="{{ $product->id }}"
                                data-upsale-bundle-item
                                @unless($parentProductInCart)
                                    title="{{ trans('plugins/ecommerce::products.up_sale.unlock_discount', ['product' => $parentProduct->name]) }}"
                                @endunless
                            >
                                <div class="ec-upsell-bundle-item-inner">
                                    <div class="ec-upsell-bundle-checkbox">
                                        <input
                                            type="checkbox"
                                            class="ec-upsell-checkbox"
                                            data-upsale-checkbox
                                            aria-label="{{ __('Select :name', ['name' => $product->name]) }}"
                                            data-id="{{ $cartId }}"
                                            data-price="{{ $displayPrice }}"
                                            data-name="{{ $product->name }}"
                                            data-bundle-discount="{{ $bundleDiscountPrice }}"
                                            data-bundle-discount-type="{{ $isPercentDiscount ? 'percent' : 'fixed' }}"
                                            @checked($parentProductInCart)
                                            @disabled(! $parentProductInCart)
                                        >
                                        <span class="ec-upsell-checkmark">
                                            @unless($parentProductInCart)
                                                <svg class="ec-upsell-lock-icon" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                                </svg>
                                            @endunless
                                        </span>
                                    </div>

                                    <div class="ec-upsell-bundle-thumb">
                                        <a href="{{ $product->url }}">
                                            {{ RvMedia::image($productImage, $product->name, 'thumb', true) }}
                                        </a>
                                    </div>

                                    <div class="ec-upsell-bundle-info">
                                        <h5 class="ec-upsell-bundle-name">
                                            <a href="{{ $product->url }}" title="{{ $product->name }}">
                                                {{ Str::limit($product->name, 50) }}
                                            </a>
                                        </h5>
                                        <div class="ec-upsell-bundle-price">
                                            @if($showOriginalPrice)
                                                <span class="ec-upsell-price-original">{{ format_price($showOriginalPrice) }}</span>
                                            @endif
                                            <span class="ec-upsell-price-sale">{{ format_price($bundlePrice) }}</span>
                                            @if($bundleDiscountPrice > 0)
                                                {{-- Badge sits inline next to the sale price (was absolute on
                                                     the thumb and clipped product images). --}}
                                                <span class="ec-upsell-discount-badge ec-upsell-discount-badge--inline">
                                                    @if($isPercentDiscount)
                                                        -{{ (int) $bundleDiscountPrice }}%
                                                    @else
                                                        -{{ format_price($bundleDiscountPrice) }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>

                                        @if($product->variations->isNotEmpty())
                                            <div class="ec-upsell-attributes-wrapper" data-product-id="{{ $product->id }}">
                                                <input type="hidden" name="id" class="ec-upsell-variation-id" value="{{ $cartId }}" />
                                                {!! render_product_swatches($product, [
                                                    'selected' => $selectedAttrs,
                                                    'view' => 'plugins/ecommerce::themes.attributes.swatches-renderer-upsale',
                                                ]) !!}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="ec-upsell-bundle-action">
                                        <button
                                            type="button"
                                            class="ec-upsell-add-btn"
                                            data-upsale-add-btn
                                            aria-label="{{ __('Add :name to cart', ['name' => $product->name]) }}"
                                            data-url="{{ route('public.cart.add-to-cart') }}"
                                            data-id="{{ $cartId }}"
                                            data-parent-product="{{ $parentProduct->slug }}"
                                            @disabled(!$parentProductInCart)
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="9" cy="21" r="1"></circle>
                                                <circle cx="20" cy="21" r="1"></circle>
                                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(EcommerceHelper::isCartEnabled())
                        <div class="ec-upsell-bundle-footer">
                            @if(!$parentProductInCart)
                                <div class="ec-upsell-bundle-notice">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    <span>{{ trans('plugins/ecommerce::products.up_sale.unlock_discount', ['product' => $parentProduct->name]) }}</span>
                                </div>
                            @else
                                <div class="ec-upsell-bundle-total">
                                    <span class="ec-upsell-total-label">{{ trans('plugins/ecommerce::products.up_sale.selected_items_total') }}</span>
                                    <span class="ec-upsell-total-price" data-upsale-total-price data-base-price="0">
                                        {{ format_price(0) }}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    class="ec-upsell-bundle-add-all"
                                    data-upsale-add-all
                                    data-url="{{ route('public.cart.add-to-cart') }}"
                                    data-parent-product="{{ $parentProduct->slug }}"
                                    disabled
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                    </svg>
                                    {{ trans('plugins/ecommerce::products.up_sale.add_selected_to_cart') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif
@else
    <div data-bb-toggle="block-lazy-loading" data-url="{{ route('public.ajax.up-sale-products', $parentProduct) }}">
        <section class="ec-upsell-skeleton">
            <div class="container">
                <div class="ec-upsell-skeleton-wrapper">
                    <div class="ec-upsell-skeleton-header">
                        <div class="skeleton skeleton-icon"></div>
                        <div class="skeleton-title-group">
                            <div class="skeleton skeleton-title"></div>
                            <div class="skeleton skeleton-subtitle"></div>
                        </div>
                    </div>
                    <div class="ec-upsell-skeleton-items">
                        @for ($i = 0; $i < 2; $i++)
                            <div class="ec-upsell-skeleton-item">
                                <div class="skeleton skeleton-checkbox"></div>
                                <div class="skeleton skeleton-thumb"></div>
                                <div class="skeleton-info">
                                    <div class="skeleton skeleton-name"></div>
                                    <div class="skeleton skeleton-price"></div>
                                </div>
                                <div class="skeleton skeleton-action"></div>
                            </div>
                        @endfor
                    </div>
                    <div class="ec-upsell-skeleton-footer">
                        <div class="skeleton-total">
                            <div class="skeleton skeleton-label"></div>
                            <div class="skeleton skeleton-price"></div>
                        </div>
                        <div class="skeleton skeleton-btn"></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endif
