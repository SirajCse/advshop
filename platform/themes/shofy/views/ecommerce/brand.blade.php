@php
    Theme::set('pageTitle', $brand->name);
@endphp

<section class="tp-shop-area @if (! theme_option('theme_breadcrumb_enabled', true)) pt-50 @endif">
    <div class="container position-relative">
        {!! dynamic_sidebar('products_by_brand_top_sidebar') !!}

        @include(Theme::getThemeNamespace('views.ecommerce.includes.products-listing'), ['pageName' => $brand->name, 'pageDescription' => $brand->description])

        {!! dynamic_sidebar('products_by_brand_bottom_sidebar') !!}
    </div>
</section>
