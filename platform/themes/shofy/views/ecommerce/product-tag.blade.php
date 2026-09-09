@php
    Theme::set('pageTitle', $tag->name);
@endphp

<section class="tp-shop-area @if (! theme_option('theme_breadcrumb_enabled', true)) pt-50 @endif">
    <div class="container position-relative">
        {!! dynamic_sidebar('products_by_tag_top_sidebar') !!}

        @include(Theme::getThemeNamespace('views.ecommerce.includes.products-listing'), ['pageName' => $tag->name, 'pageDescription' => $tag->content ? \Botble\Shortcode\Facades\Shortcode::compile($tag->content, true)->toHtml() : $tag->description])

        {!! dynamic_sidebar('products_by_tag_bottom_sidebar') !!}
    </div>
</section>
