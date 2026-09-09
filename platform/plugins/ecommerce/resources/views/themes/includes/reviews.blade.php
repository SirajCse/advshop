@if (EcommerceHelper::isReviewEnabled())
    @php
        $version = EcommerceHelper::getAssetVersion();

        Theme::asset()->add('lightgallery-css', 'vendor/core/plugins/ecommerce/libraries/lightgallery/css/lightgallery.min.css', version: $version);
        Theme::asset()->add('front-ecommerce-css', 'vendor/core/plugins/ecommerce/css/front-ecommerce.css', version: $version);
        Theme::asset()->add('front-review-css', 'vendor/core/plugins/ecommerce/css/front-review.css', version: $version);
        Theme::asset()->container('footer')->add('lightgallery-js', 'vendor/core/plugins/ecommerce/libraries/lightgallery/js/lightgallery.min.js', ['jquery'], version: $version);
        Theme::asset()->container('footer')->add('lg-thumbnail-js', 'vendor/core/plugins/ecommerce/libraries/lightgallery/plugins/lg-thumbnail.min.js', ['lightgallery-js'], version: $version);
        Theme::asset()->container('footer')->add('review-js', 'vendor/core/plugins/ecommerce/js/front-review.js', ['lightgallery-js', 'lg-thumbnail-js'], version: $version);

        $showAvgRating ??= $product->reviews->isNotEmpty();
    @endphp

    <div class="d-flex flex-column gap-5 product-review-container">
        <div class="row g-3">
            @if ($showAvgRating)
                <div class="col-12 col-md-4">
                    <div class="product-review-number">
                        <h3 class="product-review-number-title">{{ trans('plugins/ecommerce::review.customer_reviews') }}</h3>

                        <div class="product-review-summary">
                            <div class="product-review-summary-value">
                                <span>
                                    {{ number_format($product->reviews_avg ?: 0, 2) }}
                                </span>
                            </div>
                            <div class="product-review-summary-rating">
                                @include(EcommerceHelper::viewPath('includes.rating-star'), ['avg' => $product->reviews_avg, 'size' => 80])
                                <p>
                                    @if ($product->reviews_count === 1)
                                        ({{ trans('plugins/ecommerce::review.1_review') }})
                                    @else
                                        ({{ trans('plugins/ecommerce::review.count_reviews', ['count' => number_format($product->reviews_count)]) }})
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="product-review-progress">
                            @foreach (EcommerceHelper::getReviewsGroupedByProductId($product->id, $product->reviews_count) as $item)
                                <div @class(['product-review-progress-bar', 'disabled' => ! $item['count'], 'clickable' => $item['count']])
                                     @if($item['count'])
                                        data-bb-toggle="review-star-filter-bar"
                                        data-star="{{ $item['star'] }}"
                                        role="button"
                                        tabindex="0"
                                        title="{{ trans('plugins/ecommerce::review.filter_by_star_reviews', ['star' => $item['star']]) }}"
                                     @endif>
                                    <span class="product-review-progress-bar-title">
                                        @if($item['star'] == 1)
                                            {{ trans('plugins/ecommerce::review.number_star_singular', ['number' => $item['star']]) }}
                                        @else
                                            {{ trans('plugins/ecommerce::review.number_stars_plural', ['number' => $item['star']]) }}
                                        @endif
                                    </span>
                                    <div class="progress product-review-progress-bar-value">
                                        <div
                                            class="progress-bar"
                                            role="progressbar"
                                            aria-valuenow="{{ $item['percent'] }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                            style="width: {{ $item['percent'] }}%"
                                        ></div>
                                    </div>
                                    <span class="product-review-progress-bar-percent">
                                        {{ $item['percent'] }}%
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @include($reviewFormView ?? EcommerceHelper::viewPath('includes.review-form'))
        </div>

        @if (EcommerceHelper::isCustomerReviewImageUploadEnabled() && get_ecommerce_setting('display_uploaded_customer_review_images_list', true) && ($reviewImagesCount = count($product->review_images)) > 0)
            <div class="review-images-container">
                <h4 class="mb-3">{{ trans('plugins/ecommerce::review.images_from_customer', ['count' => number_format($reviewImagesCount)]) }}</h4>

                <div class="row g-1 review-images">
                    @foreach ($product->review_images as $image)
                        <a href="{{ RvMedia::getImageUrl($image) }}" class="col-3 col-md-2 col-xl-1 position-relative" @style(['display: none !important' => $loop->iteration > 12])>
                            <img src="{{ RvMedia::getImageUrl($image, 'thumb') }}" alt="{{ $product->name }}" class="img-thumbnail">
                            @if ($loop->iteration === 12)
                                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-75 rounded"></div>
                                <div class="position-absolute top-50 start-50 translate-middle text-white">
                                    <span class="badge bg-dark">+{{ trans('plugins/ecommerce::review.count_more', ['count' => number_format($reviewImagesCount - 12)]) }}</span>
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($product->reviews->isNotEmpty())
            <div class="position-relative review-list-container" data-ajax-url="{{ route('public.ajax.reviews', $product->id) }}">
                <h4 class="mb-3">{{ trans('plugins/ecommerce::review.total_reviews_for_product', ['total' => number_format($product->reviews_count), 'product' => $product->name]) }}</h4>

                <div class="d-flex align-items-center justify-content-between mb-4 product-review-controls">
                    <div class="d-flex gap-2 review-control-buttons">
                        <button type="button" class="btn review-control-btn" data-bb-toggle="review-search-toggle" title="{{ trans('plugins/ecommerce::review.search_reviews') }}">
                            <x-core::icon name="ti ti-search" class="me-1" />
                            {{ trans('plugins/ecommerce::ecommerce.search') }}
                        </button>
                        <button type="button" class="btn review-control-btn" data-bb-toggle="review-filter-toggle" title="{{ trans('plugins/ecommerce::review.filter_by_stars') }}">
                            <x-core::icon name="ti ti-filter" class="me-1" />
                            {{ trans('plugins/ecommerce::ecommerce.filter') }}
                        </button>
                        <button type="button" class="btn review-control-btn" data-bb-toggle="review-sort-toggle" title="{{ trans('plugins/ecommerce::review.sort_reviews') }}">
                            <x-core::icon name="ti ti-sort-ascending" class="me-1" />
                            {{ trans('plugins/ecommerce::ecommerce.sort') }}
                        </button>
                    </div>
                    <button type="button" class="btn review-clear-btn d-none" data-bb-toggle="review-clear-filters" title="{{ trans('plugins/ecommerce::ecommerce.clear_all_filters') }}">
                        <x-core::icon name="ti ti-x" class="me-1" />
                        {{ trans('plugins/ecommerce::ecommerce.clear') }}
                    </button>
                </div>

                <div class="review-search-container d-none mb-3">
                    <div class="position-relative">
                        <input
                            type="search"
                            class="form-control review-search-input"
                            placeholder="{{ trans('plugins/ecommerce::review.search_reviews_1') }}"
                            data-bb-toggle="review-search"
                        >
                        <div class="position-absolute top-50 end-0 translate-middle-y pe-3">
                            <x-core::icon name="ti ti-search" class="text-muted" />
                        </div>
                    </div>
                </div>

                <div class="review-filter-container d-none mb-3">
                    <select class="form-select review-star-filter" data-bb-toggle="review-star-filter">
                        <option value="">{{ trans('plugins/ecommerce::review.all_stars') }}</option>
                        <option value="5">{{ trans('plugins/ecommerce::review.5_stars') }}</option>
                        <option value="4">{{ trans('plugins/ecommerce::review.4_stars') }}</option>
                        <option value="3">{{ trans('plugins/ecommerce::review.3_stars') }}</option>
                        <option value="2">{{ trans('plugins/ecommerce::review.2_stars') }}</option>
                        <option value="1">{{ trans('plugins/ecommerce::review.1_star') }}</option>
                    </select>
                </div>

                <div class="review-sort-container d-none mb-3">
                    <select class="form-select review-sort-select" data-bb-toggle="review-sort">
                        <option value="newest">{{ trans('plugins/ecommerce::ecommerce.newest') }}</option>
                        <option value="oldest">{{ trans('plugins/ecommerce::ecommerce.oldest') }}</option>
                        <option value="highest_rating">{{ trans('plugins/ecommerce::review.highest_rating') }}</option>
                        <option value="lowest_rating">{{ trans('plugins/ecommerce::review.lowest_rating') }}</option>
                    </select>
                </div>

                <div class="review-list"></div>
            </div>
        @else
            <p class="text-muted text-center">{{ trans('plugins/ecommerce::review.looks_like_there_are_no_reviews_yet') }} </p>
        @endif
    </div>
@endif
