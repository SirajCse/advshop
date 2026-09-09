@if (theme_option('enabled_bottom_menu_bar_on_mobile', true))
    <div
        id="tp-bottom-menu-sticky"
        @class(['tp-mobile-menu d-lg-none', 'menu--footer__hide_text' => theme_option('bottom_bar_menu_show_text', 'yes') != 'yes'])
        style="--bottom-bar-menu-text-font-size: {{ theme_option('bottom_bar_menu_text_font_size', 13) }}px;"
    >
        <div class="container">
            <div @class([
                    'row',
                    'row-cols-5' => is_plugin_active('ecommerce') && EcommerceHelper::isWishlistEnabled(),
                    'row-cols-4' => is_plugin_active('ecommerce') && ! EcommerceHelper::isWishlistEnabled(),
                    'row-cols-2' => ! is_plugin_active('ecommerce')
                ])
            >
                @if (is_plugin_active('ecommerce'))
                    <div class="col">
                        <div class="text-center tp-mobile-item">
                            <a href="{{ route('public.products') }}" class="tp-mobile-item-btn">
                                <x-core::icon name="ti ti-shopping-bag" />
                                <span class="text-truncate" title="{{ __('Store') }}">{{ __('Store') }}</span>
                            </a>
                        </div>
                    </div>
                @endif
                <div class="col">
                    <div class="text-center tp-mobile-item">
                        <button class="tp-mobile-item-btn tp-search-open-btn">
                            <x-core::icon name="ti ti-search" />
                            <span class="text-truncate" title="{{ __('Search') }}">{{ __('Search') }}</span>
                        </button>
                    </div>
                </div>
                @if (is_plugin_active('ecommerce'))
                    @if (EcommerceHelper::isWishlistEnabled())
                        <div class="col">
                            <div class="text-center tp-mobile-item">
                                <a href="{{ route('public.wishlist') }}" class="tp-mobile-item-btn">
                                    <x-core::icon name="ti ti-heart" />
                                    <span class="text-truncate" title="{{ __('Wishlist') }}">{{ __('Wishlist') }}</span>
                                </a>
                            </div>
                        </div>
                    @endif
                    <div class="col">
                        <div class="text-center tp-mobile-item">
                            <a
                                href="{{ auth('customer')->check() ? route('customer.overview') : route('customer.login') }}"
                                class="tp-mobile-item-btn"
                                @auth('customer')
                                    title="{{ auth('customer')->user()->name }}"
                                @endauth
                            >
                                <x-core::icon name="ti ti-user" />
                                <span class="text-truncate" title="{{ __('Account') }}">{{ __('Account') }}</span>
                            </a>
                        </div>
                    </div>
                @endif
                <div class="col">
                    <div class="text-center tp-mobile-item">
                        <button class="tp-mobile-item-btn tp-offcanvas-open-btn">
                            <x-core::icon name="ti ti-menu-2" />
                            <span class="text-truncate" title="{{ __('Menu') }}">{{ __('Menu') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
