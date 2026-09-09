@php
    Theme::layout('full-width');
    Theme::set('pageTitle', __('Wishlist'));
@endphp

<section class="tp-wishlist-area @if (! theme_option('theme_breadcrumb_enabled', true)) pt-50 @endif">
    <div class="container">
        @if ($products->total() && $products->loadMissing(['options', 'options.values']))
            <div class="tp-cart-list mb-45">
                <table class="table">
                    <thead>
                    <tr>
                        <th colspan="2" class="tp-cart-header-product">{{ __('Product') }}</th>
                        <th class="tp-cart-header-price">{{ __('Price') }}</th>
                        <th class="tp-cart-header-quantity">{{ __('Quantity') }}</th>
                        <th>{{ __('Action') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td class="tp-cart-img">
                                <a href="{{ $product->original_product->url }}">
                                    {{ RvMedia::image($product->image, $product->name, 'thumb') }}
                                </a>
                            </td>
                            <td class="ps-3">
                                <div class="tp-cart-title">
                                    <a href="{{ $product->original_product->url }}" class="ms-0">
                                        {{ $product->name }}

                                        <span class="small">
                                            ({!! $product->stock_status_html !!})
                                        </span>
                                    </a>
                                </div>

                                @if (is_plugin_active('marketplace') && $product->original_product->store?->id)
                                    <div class="small">
                                        <span>{{ __('Vendor:') }}</span>
                                        <a href="{{ $product->original_product->store->url }}" class="fw-medium">{{ $product->original_product->store->name }}</a>
                                    </div>
                                @endif

                                @if ($product->sku)
                                    <div class="small">
                                        <span>{{ __('SKU:') }}</span>
                                        <span>{{ $product->sku }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="tp-cart-price">
                                @include(EcommerceHelper::viewPath('includes.product-price'), [
                                    'priceWrapperClassName' => '',
                                    'priceClassName' => '',
                                    'priceOriginalWrapperClassName' => '',
                                    'priceOriginalClassName' => 'text-muted',
                                ])
                            </td>

                            <td class="tp-cart-quantity">
                                <div class="tp-product-quantity mt-10 mb-10">
                                    <span class="tp-cart-minus" data-bb-toggle="decrease-qty">
                                        <svg width="10" height="2" viewBox="0 0 10 2" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 1H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <input
                                        class="tp-cart-input"
                                        type="number"
                                        name="qty"
                                        aria-label="{{ __('Quantity') }}"
                                        value="{{ $product->min_cart_quantity }}"
                                        min="{{ $product->min_cart_quantity }}"
                                        max="{{ $product->max_cart_quantity }}"
                                    />
                                    <span class="tp-cart-plus" data-bb-toggle="increase-qty">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 1V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M1 5H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                            </td>

                            <td class="tp-cart-add-to-cart">
                                <button
                                    type="submit"
                                    class="tp-btn tp-btn-2 tp-btn-blue"
                                    data-bb-toggle="add-to-cart"
                                    data-show-toast-on-success="false"
                                    data-url="{{ route('public.cart.add-to-cart') }}"
                                    data-id="{{ $product->original_product->id }}"
                                    {!! EcommerceHelper::jsAttributes('add-to-cart', $product) !!}
                                >
                                    {{ __('Add To Cart') }}
                                </button>
                            </td>

                            @if($canRemoveWishlist)
                                <td class="tp-cart-action">
                                    <button class="tp-cart-action-btn" data-bb-toggle="remove-from-wishlist" data-url="{{ route('public.wishlist.remove', $product) }}">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                fill-rule="evenodd"
                                                clip-rule="evenodd"
                                                d="M9.53033 1.53033C9.82322 1.23744 9.82322 0.762563 9.53033 0.46967C9.23744 0.176777 8.76256 0.176777 8.46967 0.46967L5 3.93934L1.53033 0.46967C1.23744 0.176777 0.762563 0.176777 0.46967 0.46967C0.176777 0.762563 0.176777 1.23744 0.46967 1.53033L3.93934 5L0.46967 8.46967C0.176777 8.76256 0.176777 9.23744 0.46967 9.53033C0.762563 9.82322 1.23744 9.82322 1.53033 9.53033L5 6.06066L8.46967 9.53033C8.76256 9.82322 9.23744 9.82322 9.53033 9.53033C9.82322 9.23744 9.82322 8.76256 9.53033 8.46967L6.06066 5L9.53033 1.53033Z"
                                                fill="currentColor"
                                            />
                                        </svg>
                                        <span>{{ __('Remove') }}</span>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="tp-cart-bottom">
                <div class="d-flex flex-wrap gap-4 align-items-center justify-content-between">
                    <div class="tp-cart-update">
                        <a href="{{ route('public.cart') }}" class="tp-cart-update-btn">{{ __('Go To Cart') }}</a>
                    </div>
                    @if(EcommerceHelper::isWishlistSharingEnabled())
                        <div>
                            <span class="d-inline-block me-1">{{ __('Share:') }}</span>

                            {!! Theme::renderSocialSharing(route('public.wishlist', ['code' => EcommerceHelper::getWishlistCode()])) !!}
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="text-center pt-50 pb-50">
                <div class="empty-wishlist-icon mb-30">
                    <x-core::icon name="ti ti-heart" style="width: 80px; height: 80px; color: #ccc;" />
                </div>
                <h3 class="mb-15">{{ __('Your wishlist is empty') }}</h3>
                <p class="text-muted mb-30">{{ __('Save your favorite items here so you can easily find them later.') }}</p>
                <a href="{{ route('public.products') }}" class="tp-cart-checkout-btn">
                    <x-core::icon name="ti ti-shopping-bag" class="me-2" />
                    {{ __('Start Shopping') }}
                </a>
            </div>
        @endif
    </div>
</section>
