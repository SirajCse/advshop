@if ($product instanceof \Botble\Ecommerce\Models\Product && $product->exists)
    <div class="tp-product-sm-item d-flex align-items-center">
        <div class="tp-product-thumb mr-25 fix">
            <a href="{{ $product->url }}">
                {{ RvMedia::image($product->image, $product->name, 'thumb') }}
            </a>
        </div>
        <div class="tp-product-sm-content">
            @if (is_plugin_active('marketplace') && $product->store?->id)
                <div class="tp-product-category tp-store-name">
                    <a href="{{ $product->store->url }}">{{ $product->store->name }}</a> {!! $product->store->badge !!}
                </div>
            @endif

            <h3 class="tp-product-title">
                <a href="{{ $product->url }}">{{ $product->name }}</a>
            </h3>

                <div @class(['tp-product-price-review' => theme_option('product_listing_review_style', 'default') !== 'default' && EcommerceHelper::isReviewEnabled() && ($product->reviews_avg || theme_option('ecommerce_hide_rating_star_when_is_zero', 'no') === 'no')])>
                @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-1.rating'))

                @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-1.price'))
            </div>
        </div>
    </div>
@endif
