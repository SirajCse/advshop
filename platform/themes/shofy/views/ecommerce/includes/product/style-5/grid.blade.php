<div @class(['tp-product-item-5 p-relative white-bg mb-40', $class ?? null])>
    <div class="tp-product-thumb-5 w-img fix mb-15">
        <a href="{{ $product->url }}">
            {{ RvMedia::image($product->image, $product->name, $style === 2 ? 'thumb' : 'medium', true) }}
        </a>

        @include(Theme::getThemeNamespace('views.ecommerce.includes.product.badges'))

        @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-5.actions'))
    </div>
    <div class="tp-product-content-5">
        {!! apply_filters('ecommerce_before_product_item_content_renderer', null, $product) !!}

        @if (is_plugin_active('marketplace') && $product->store?->id)
            <div class="tp-product-tag-5">
                <span><a href="{{ $product->store->url }}">{{ $product->store->name }}</a> {!! $product->store->badge !!}</span>
            </div>
        @endif

        <h3 @class(['tp-product-title-2', theme_option('ecommerce_product_name_lines', '2') === '1' ? 'line-clamp-1' : 'line-clamp-2'])>
            <a href="{{ $product->url }}" title="{{ $product->name }}">{{ $product->name }}</a>
        </h3>

        <div @class(['mt-2 tp-product-price-review' => theme_option('product_listing_review_style', 'default') !== 'default' && EcommerceHelper::isReviewEnabled() && ($product->reviews_avg || theme_option('ecommerce_hide_rating_star_when_is_zero', 'no') === 'no') && (!EcommerceHelper::hideRatingWhenNoReviews() || $product->reviews_count > 0)])>
            @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-5.rating'))

            @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-5.price'))
        </div>

        {!! apply_filters('ecommerce_after_product_item_content_renderer', null, $product) !!}
    </div>
</div>
