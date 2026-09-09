@php
    Theme::layout('full-width');
    Theme::set('pageTitle', __('Shopping Cart'));
@endphp

<section class="tp-cart-area pb-120 @if (! theme_option('theme_breadcrumb_enabled', true)) pt-50 @endif">
    <div class="container">
        @if ($products->isNotEmpty())
            <div class="row">
                <div class="col-xl-9 col-lg-8">
                    <x-core::form method="post" :url="route('public.cart.update')" class="cart-form">
                        <div class="tp-cart-list mb-25 mr-30 bb-ecommerce-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="tp-cart-header-product">{{ __('Product') }}</th>
                                        <th class="tp-cart-header-price">{{ __('Price') }}</th>
                                        <th class="tp-cart-header-quantity">{{ __('Quantity') }}</th>
                                        <th class="tp-cart-header-total">{{ __('Total') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(Cart::instance('cart')->content() as $key => $cartItem)
                                        @php
                                            $product = $products->find($cartItem->id);
                                        @endphp

                                        @continue(empty($product))

                                        <tr>
                                            <td class="ps-3 cart-product-content">
                                                {!! apply_filters('ecommerce_cart_before_item_content', null, $cartItem) !!}

                                                <a href="{{ $product->original_product->url }}">
                                                    {{ RvMedia::image($cartItem->options['image'], $product->original_product->name, 'thumb') }}
                                                </a>
                                                <div class="tp-cart-title">
                                                    <input type="hidden" name="items[{{ $key }}][rowId]" value="{{ $cartItem->rowId }}">
                                                    <a href="{{ $product->original_product->url }}" class="ms-0">{{ $product->original_product->name }}</a>
                                                    <div class="small">
                                                        {!! $product->stock_status_html !!}
                                                    </div>

                                                    @if (is_plugin_active('marketplace') && $product->original_product->store?->id)
                                                        <div class="small">
                                                            <span>{{ __('Vendor:') }}</span>
                                                            <a href="{{ $product->original_product->store->url }}" class="small fw-medium">{{ $product->original_product->store->name }}</a>
                                                        </div>
                                                    @endif

                                                    <div class="small">{{ $cartItem->options['attributes'] ?? '' }}</div>

                                                    @if (EcommerceHelper::isEnabledProductOptions() && !empty($cartItem->options['options']))
                                                        {!! render_product_options_html($cartItem->options['options'], $product->price()->getPrice()) !!}
                                                    @endif

                                                    @include(
                                                        EcommerceHelper::viewPath('includes.cart-item-options-extras'),
                                                        ['options' => $cartItem->options]
                                                    )

                                                    {!! apply_filters('ecommerce_cart_after_item_content', null, $cartItem) !!}
                                                </div>
                                            </td>
                                            <td class="tp-cart-price" data-title="{{ __('Price') }}">
                                                @include(EcommerceHelper::viewPath('includes.product-price'), [
                                                    'priceWrapperClassName' => 'tp-product-price-wrapper',
                                                    'priceClassName' => 'tp-product-price new-price',
                                                    'priceOriginalWrapperClassName' => '',
                                                    'priceOriginalClassName' => 'text-muted',
                                                    'priceFormatted' => format_price($cartItem->price),
                                                ])
                                            </td>
                                            <td class="tp-cart-quantity" data-title="{{ __('Quantity') }}">
                                                @include(Theme::getThemeNamespace('views.ecommerce.includes.cart-quantity'))
                                            </td>
                                            <td class="tp-cart-total" data-title="{{ __('Total') }}">
                                                {{ format_price($cartItem->price * $cartItem->qty) }}
                                            </td>
                                            <td class="tp-cart-action" data-title="{{ __('Remove') }}">
                                                <button
                                                    class="tp-cart-action-btn"
                                                    aria-label="{{ __('Remove') }}"
                                                    data-url="{{ route('public.cart.remove', $cartItem->rowId) }}"
                                                    data-bb-toggle="remove-from-cart"
                                                    {!! EcommerceHelper::jsAttributes('remove-from-cart', $product, ['data-product-quantity' => $cartItem->qty]) !!}
                                                >
                                                    <x-core::icon name="ti ti-trash" />
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-core::form>
                    <div class="tp-cart-bottom">
                        <div class="tp-cart-coupon">
                            <x-core::form :url="route('public.coupon.apply')" method="post" id="coupon-form">
                                <div class="tp-cart-coupon-input-box">
                                    <label>{{ __('Coupon Code:') }}</label>
                                    <div class="tp-cart-coupon-input d-flex align-items-center">
                                        <input type="text" placeholder="{{ __('Enter Coupon Code') }}" name="coupon_code" value="{{ BaseHelper::stringify(old('coupon_code', session('applied_coupon_code'))) }}" />
                                        <button type="submit" @disabled(session('applied_coupon_code'))>{{ __('Apply') }}</button>
                                    </div>
                                </div>
                            </x-core::form>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    {!! apply_filters('ecommerce_cart_sidebar_before_checkout', null, $products) !!}

                    <div class="tp-cart-checkout-wrapper">
                        <div class="tp-cart-checkout-top d-flex align-items-center justify-content-between">
                            <span class="tp-cart-checkout-top-title">{{ __('Subtotal') }}</span>
                            <span class="tp-cart-checkout-top-price">{{ format_price(Cart::instance('cart')->rawSubTotal()) }}</span>
                        </div>
                        {!! apply_filters('ecommerce_cart_after_subtotal', null, Cart::instance('cart')->products()) !!}
                        @if (EcommerceHelper::isTaxEnabled() && Cart::instance('cart')->rawTax() > 0)
                            <div class="tp-cart-checkout-tax d-flex align-items-center justify-content-between mb-2">
                                <span class="tp-cart-checkout-tax-title">{{ __('Tax') }}</span>
                                <span class="tp-cart-checkout-tax-price">{{ format_price(Cart::instance('cart')->rawTax()) }}</span>
                            </div>
                        @endif
                        @if ($couponDiscountAmount > 0 && session('applied_coupon_code'))
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    {{ __('Coupon') }}
                                    <span class="small">({{ session('applied_coupon_code') }})</span>
                                    <a class="small btn-link text-danger lh-1" data-bb-toggle="remove-coupon" href="{{ route('public.coupon.remove') }}">{{ __('Remove') }}</a>
                                </div>
                                <span>{{ format_price($couponDiscountAmount) }}</span>
                            </div>
                        @endif
                        @if ($promotionDiscountAmount)
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span>{{ __('Promotion') }}</span>
                                <span>{{ format_price($promotionDiscountAmount) }}</span>
                            </div>
                        @endif
                        <div class="tp-cart-checkout-total d-flex align-items-center justify-content-between mt-3 mb-0">
                            <span>{{ __('Total') }}</span>
                            <span>{{ ($promotionDiscountAmount + $couponDiscountAmount) > Cart::instance('cart')->rawTotal() ? format_price(0) : format_price(Cart::instance('cart')->rawTotal() - $promotionDiscountAmount - $couponDiscountAmount) }}</span>
                        </div>
                        <p class="small">{{ __('(Shipping fees not included)') }}</p>
                        <div class="tp-cart-checkout-proceed">
                            <a href="{{ route('public.checkout.information', OrderHelper::getOrderSessionToken()) }}" class="tp-cart-checkout-btn w-100">
                                {{ __('Proceed to Checkout') }}
                            </a>
                        </div>

                        <a href="{{ route('public.products') }}" class="btn-link d-block text-center mt-3">
                            {{ __('Continue Shopping') }}
                        </a>

                        {!! apply_filters('ecommerce_cart_sidebar_after_checkout', null, $products) !!}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center pt-50 pb-50">
                <div class="empty-cart-icon mb-30">
                    <x-core::icon name="ti ti-shopping-cart" style="width: 80px; height: 80px; color: #ccc;" />
                </div>
                <h3 class="mb-15">{{ __('Your cart is empty') }}</h3>
                <p class="text-muted mb-30">{{ __('Looks like you haven\'t added anything to your cart yet.') }}</p>
                <a href="{{ route('public.products') }}" class="tp-cart-checkout-btn">
                    <x-core::icon name="ti ti-shopping-bag" class="me-2" />
                    {{ __('Start Shopping') }}
                </a>
            </div>
        @endif
    </div>
</section>
