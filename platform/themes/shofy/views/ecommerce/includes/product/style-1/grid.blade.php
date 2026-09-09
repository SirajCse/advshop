<div @class(['tp-product-item transition-3 mb-25', 'tp-product-item-3 tp-product-style-primary mb-50' => $style === 3, $class ?? null])>
    <div class="tp-product-thumb p-relative fix m-img">
        <a href="{{ $product->url }}">
            {{ RvMedia::image($product->image, $product->name, 'medium') }}
        </a>

        @include(Theme::getThemeNamespace('views.ecommerce.includes.product.badges'))

        @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-1.actions'))
    </div>

    <div class="tp-product-content">
        {!! apply_filters('ecommerce_before_product_item_content_renderer', null, $product) !!}

        @if (is_plugin_active('marketplace') && $product->store?->id)
            <div class="tp-product-category tp-store-name">
                <a href="{{ $product->store->url }}">{{ $product->store->name }}</a> {!! $product->store->badge !!}
            </div>
        @endif
        <h3 @class(['tp-product-title', theme_option('ecommerce_product_name_lines', '2') === '1' ? 'line-clamp-1' : 'line-clamp-2'])>
            <a href="{{ $product->url }}" title="{{ $product->name }}">
                {{ $product->name }}
            </a>
        </h3>

        <div @class(['tp-product-price-review' => theme_option('product_listing_review_style', 'default') !== 'default' && EcommerceHelper::isReviewEnabled() && ($product->reviews_avg || theme_option('ecommerce_hide_rating_star_when_is_zero', 'no') === 'no')])>
            @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-1.rating'))

            @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-1.price'))
        </div>

        @if ($withCountdown ?? false)
            @include(Theme::getThemeNamespace('views.ecommerce.includes.product.countdown'), compact('endDate', 'flashSale', 'product'))
        @endif

        {!! apply_filters('ecommerce_after_product_item_content_renderer', null, $product) !!}
    </div>
</div>
