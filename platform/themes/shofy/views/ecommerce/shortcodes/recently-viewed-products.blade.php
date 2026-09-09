@if ($products->isNotEmpty())
    @php
        $shortcode->is_loop = 'no';
    @endphp

    {!! Theme::partial('shortcodes.ecommerce-products.slider', compact('shortcode', 'products')) !!}
@endif
