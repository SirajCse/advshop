@php
    Theme::set('pageTitle', $collection->name);
@endphp

<section class="tp-shop-area @if (! theme_option('theme_breadcrumb_enabled', true)) pt-50 @endif">
    <div class="container position-relative">
        {!! dynamic_sidebar('products_by_collection_top_sidebar') !!}

        @include(Theme::getThemeNamespace('views.ecommerce.includes.products-listing'), ['pageName' => $collection->name, 'pageDescription' => $collection->description])

        {!! dynamic_sidebar('products_by_collection_bottom_sidebar') !!}
    </div>
</section>