@if (EcommerceHelper::isReviewEnabled() && ($product->reviews_avg || theme_option('ecommerce_hide_rating_star_when_is_zero', 'no') === 'no') && (!EcommerceHelper::hideRatingWhenNoReviews() || $product->reviews_count > 0))
    @if(theme_option('product_listing_review_style', 'default') === 'default')
        <div class="tp-product-rating d-flex align-items-center">
            <div class="tp-product-rating-icon tp-product-rating-icon-2">
                @include(EcommerceHelper::viewPath('includes.rating-star'), ['avg' => $product->reviews_avg])
            </div>
            <div class="tp-product-rating-text">
                <a href="{{ $product->url }}#product-review" data-bb-toggle="scroll-to-review">
                    <span class="d-none d-sm-block">{{ __('(:count reviews)', ['count' => number_format($product->reviews_count)]) }}</span>
                    <span class="d-block d-sm-none">{{ __('(:count)', ['count' => number_format($product->reviews_count)]) }}</span>
                </a>
            </div>
        </div>
    @else
        <div class="d-flex align-items-center tp-product-rating-simple gap-1">
            <x-core::icon name="ti ti-star-filled" class="text-warning" />
            <span>{{ round($product->reviews_avg, 1) ?: 0 }}</span>
        </div>
    @endif
@endif
