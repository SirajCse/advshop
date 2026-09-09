@if(request()->ajax() && isset($products))
    @if ($products->isNotEmpty())
        <section class="tp-related-product tp-related-product-modern">
            <div class="container">
                <div class="tp-related-header">
                    <div class="tp-related-header-content">
                        <span class="tp-related-subtitle">{{ __('Explore More') }}</span>
                        <h3 class="tp-related-title">{{ __('Related Products') }}</h3>
                    </div>
                    <div class="tp-related-nav">
                        <button class="tp-related-slider-button-prev tp-related-nav-btn" aria-label="{{ __('Previous') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>
                        <button class="tp-related-slider-button-next tp-related-nav-btn" aria-label="{{ __('Next') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="tp-product-related-slider">
                    <div class="tp-product-related-slider-active swiper-container" @if (theme_option('number_of_related_product', 4) < 6) data-items-per-view="{{ theme_option('number_of_related_product', 4) }}" @endif>
                        <div class="swiper-wrapper">
                            @foreach ($products as $product)
                                <div class="swiper-slide">
                                    @include(Theme::getThemeNamespace('views.ecommerce.includes.product-item'))
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="tp-related-swiper-scrollbar tp-swiper-scrollbar"></div>
                </div>
            </div>
        </section>
    @endif
@else
    <div data-bb-toggle="block-lazy-loading" data-url="{{ route('public.ajax.related-products', $product) }}">
        <section class="tp-related-skeleton">
            <div class="container">
                <div class="tp-related-skeleton-header">
                    <div class="skeleton-title-group">
                        <div class="skeleton skeleton-subtitle"></div>
                        <div class="skeleton skeleton-title"></div>
                    </div>
                    <div class="skeleton-nav">
                        <div class="skeleton skeleton-nav-btn"></div>
                        <div class="skeleton skeleton-nav-btn"></div>
                    </div>
                </div>
                <div class="tp-related-skeleton-slider">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="tp-related-skeleton-card">
                            <div class="skeleton skeleton-thumb"></div>
                            <div class="skeleton-content">
                                <div class="skeleton skeleton-name"></div>
                                <div class="skeleton-price-wrapper">
                                    <div class="skeleton skeleton-price"></div>
                                    <div class="skeleton skeleton-price-old"></div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
                <div class="tp-related-skeleton-scrollbar">
                    <div class="skeleton-drag"></div>
                </div>
            </div>
        </section>
    </div>
@endif
