@php
    $customer = auth('customer')->user();
@endphp

<header class="header--mobile">
    <div class="header__left">
        <button class="ps-drawer-toggle">
            <x-core::icon name="ti ti-menu-2" />
        </button>
    </div>
    <div class="header__center">
        <a
            class="ps-logo"
            href="{{ route('marketplace.vendor.dashboard') }}"
        >
            @if ($logo = theme_option('logo_vendor_dashboard', Theme::getLogo()))
                <img
                    src="{{ RvMedia::getImageUrl($logo) }}"
                    alt="{{ Theme::getSiteTitle() }}"
                >
            @endif
        </a>
    </div>
    <div class="header__right d-flex align-items-center gap-2">
        <a class="header__site-link" href="{{ route('customer.overview') }}" title="{{ trans('plugins/marketplace::marketplace.go_to_customer_dashboard') }}">
            <x-core::icon name="ti ti-user" />
        </a>
        <a class="header__site-link" href="{{ route('customer.logout') }}" title="{{ trans('plugins/marketplace::marketplace.logout') }}">
            <x-core::icon name="ti ti-logout" />
        </a>
    </div>
</header>
<aside class="ps-drawer--mobile">
    <div class="ps-drawer__header">
        <h4 class="fs-3 mb-0">Menu</h4>
        <button class="ps-drawer__close">
            <x-core::icon name="ti ti-x" />
        </button>
    </div>
    <div class="ps-drawer__content">
        @include(MarketplaceHelper::viewPath('vendor-dashboard.layouts.menu'))

        <div class="ps-drawer__footer mt-4 pt-3 border-top">
            <ul class="menu">
                <li>
                    <a href="{{ route('customer.overview') }}">
                        <x-core::icon name="ti ti-user" />
                        {{ trans('plugins/marketplace::marketplace.go_to_customer_dashboard') }}
                    </a>
                </li>
                <li>
                    <a href="{{ BaseHelper::getHomepageUrl() }}">
                        <x-core::icon name="ti ti-home" />
                        {{ trans('plugins/marketplace::marketplace.go_to_homepage') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>
<div class="ps-site-overlay"></div>
<main class="ps-main">
    <div class="ps-main__sidebar">
        <div class="ps-sidebar">
            <div class="ps-sidebar__top">
                <div class="ps-block--user-wellcome">
                    <div class="ps-block__left">
                        <img
                            src="{{ $customer->store->logo_url }}"
                            alt="{{ $customer->store->name }}"
                            class="avatar avatar-lg"
                        />
                    </div>
                    <div class="ps-block__right">
                        <p>{{ trans('plugins/marketplace::marketplace.hello') }}, {{ $customer->name }}</p>
                        <small>{{ trans('plugins/marketplace::marketplace.joined_on_date', ['date' => $customer->created_at->translatedFormat('M d, Y')]) }}</small>

                        @if ($customer?->store)
                            <a href="{{ $customer->store->url }}" target="_blank" class="d-block mt-3">
                                <x-core::icon name="ti ti-building-store" />
                                {{ trans('plugins/marketplace::marketplace.view_your_store') }}
                            </a>
                        @endif
                        <a href="{{ route('customer.overview') }}" class="d-block mt-2">
                            <x-core::icon name="ti ti-user" />
                            {{ trans('plugins/marketplace::marketplace.go_to_customer_dashboard') }}
                        </a>
                    </div>
                    <div class="ps-block__action">
                        <a href="{{ route('customer.logout') }}">
                            <x-core::icon name="ti ti-logout" />
                        </a>
                    </div>
                </div>
                <div class="ps-block--earning-count">
                    <small>{{ trans('plugins/marketplace::marketplace.balance') }}</small>
                    <h3 class="mt-1">{{ format_price($customer->balance) }}</h3>
                </div>
            </div>
            <div class="ps-sidebar__content">
                <div class="ps-sidebar__center">
                    @include(MarketplaceHelper::viewPath('vendor-dashboard.layouts.menu'))
                    {!! apply_filters('marketplace_vendor_sidebar_menu_items', '') !!}
                </div>
                <div class="ps-sidebar__footer">
                    <div class="ps-copyright">
                        @if ($logo)
                            <a href="{{ BaseHelper::getHomepageUrl() }}" title="{{ $siteTitle = Theme::getSiteTitle() }}">
                                <img
                                    src="{{ RvMedia::getImageUrl($logo) }}"
                                    alt="{{ $siteTitle }}"
                                    style="max-height: 40px;"
                                >
                            </a>
                        @endif
                        <p>{!! BaseHelper::clean(str_replace('%Y', Carbon\Carbon::now()->year, theme_option('copyright'))) !!}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div
        class="ps-main__wrapper"
        id="vendor-dashboard"
    >
        <header class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fs-1 mb-0 text-truncate me-3">{{ page_title()->getTitle(false) }}</h3>
            <div class="d-flex align-items-center gap-4">
                @if (is_plugin_active('language'))
                    {!! apply_filters('marketplace_vendor_dashboard_language_switcher', view(MarketplaceHelper::viewPath('vendor-dashboard.partials.language-switcher'))->render()) !!}
                @endif

                <div class="d-none d-md-flex align-items-center gap-3">
                    <a href="{{ route('customer.overview') }}" class="text-uppercase">
                        <x-core::icon name="ti ti-user" />
                        <span>{{ trans('plugins/marketplace::marketplace.go_to_customer_dashboard') }}</span>
                    </a>
                    <span class="text-muted">|</span>
                    <a href="{{ BaseHelper::getHomepageUrl() }}" target="_blank" class="text-uppercase">
                        <span>{{ trans('plugins/marketplace::marketplace.go_to_homepage') }}</span>
                        <x-core::icon name="ti ti-arrow-right" />
                    </a>
                </div>
            </div>
        </header>

        <div id="app">
            @yield('content')
        </div>
    </div>
</main>
