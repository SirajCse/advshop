@php
    /**
     * Shofy-styled vendor vacation notice.
     *
     * Used in two places:
     *   - The single-store banner — the marketplace plugin's store-detail-banner
     *     partial already @includes MarketplaceHelper::viewPath('includes.vacation-notice'),
     *     and this theme override takes precedence over the plugin's generic alert.
     *   - The product detail page, injected via the ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML
     *     filter (which also resolves through MarketplaceHelper::viewPath()).
     *
     * Inputs:
     *   $store  \Botble\Marketplace\Models\Store  (required)
     *
     * Styling lives in assets/sass/components/_marketplace-vacation.scss
     * (class: .bb-store-vacation-notice) using shofy --tp-* tokens.
     */
@endphp

@if (! empty($store) && method_exists($store, 'isOnVacation') && $store->isOnVacation())
    <div class="bb-store-vacation-notice" role="status">
        <span class="bb-store-vacation-notice__icon" aria-hidden="true">
            <x-core::icon name="ti ti-beach" />
        </span>
        <span class="bb-store-vacation-notice__body">
            <strong class="bb-store-vacation-notice__title">
                {{ trans('plugins/marketplace::store.forms.vacation_badge') }}
            </strong>
            <span class="bb-store-vacation-notice__text">
                @if (! empty($store->vacation_message))
                    {{ $store->vacation_message }}
                @else
                    {{ trans('plugins/marketplace::store.forms.vacation_default_notice', ['store' => $store->name]) }}
                @endif
            </span>
        </span>
    </div>
@endif
