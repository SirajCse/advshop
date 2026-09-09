@php
    Theme::asset()->container('footer')->usePath()->add('range-slider', 'js/range-slider.js');
    $listingLayout = products_listing_layout();

    $hasFilters = EcommerceHelper::hasAnyProductFilters();
@endphp

{!! apply_filters('ads_render', null, 'listing_page_before') !!}

<div @class(['row', 'flex-row-reverse' => $listingLayout === 'right-sidebar' && $hasFilters])>
    <div @class(['col-xl-3 col-lg-4' => $listingLayout !== 'no-sidebar' && $hasFilters, 'col-12' => $listingLayout === 'no-sidebar'])>
        @include(Theme::getThemeNamespace('views.ecommerce.includes.filters-sidebar'))
    </div>

    @if ($listingLayout !== 'no-sidebar' && $hasFilters)
        <div class="col-xl-9 col-lg-8">
            @endif
            <div class="tp-shop-main-wrapper">
                @include(EcommerceHelper::viewPath('includes.product-listing-page-description'))

                @include(EcommerceHelper::viewPath('includes.product-filters-top'))

                <div class="bb-product-items-wrapper tp-shop-item-primary">
                    @include(Theme::getThemeNamespace('views.ecommerce.includes.product-items'))
                </div>
            </div>
            @if ($listingLayout !== 'no-sidebar')
        </div>
    @endif
</div>

{!! apply_filters('ads_render', null, 'listing_page_after') !!}
