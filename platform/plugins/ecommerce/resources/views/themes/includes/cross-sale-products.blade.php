@if(request()->ajax() && isset($products) && isset($parentProduct))
    @if($products->isNotEmpty())
        @php
            $carouselConfig = apply_filters('ecommerce_cross_sale_carousel_config', [
                'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
                'appendArrows' => '.ec-cross-sale-arrows',
                'arrows' => true,
                'prevArrow' => '<button type="button" class="slick-prev slick-arrow" aria-label="' . __('Previous') . '"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg></button>',
                'nextArrow' => '<button type="button" class="slick-next slick-arrow" aria-label="' . __('Next') . '"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg></button>',
                'dots' => false,
                'autoplay' => false,
                'infinite' => false,
                'autoplaySpeed' => 3000,
                'speed' => 800,
                'slidesToShow' => 5,
                'slidesToScroll' => 1,
                'swipeToSlide' => true,
                'responsive' => [
                    [
                        'breakpoint' => 1400,
                        'settings' => [
                            'slidesToShow' => 4,
                        ],
                    ],
                    [
                        'breakpoint' => 1199,
                        'settings' => [
                            'slidesToShow' => 3,
                        ],
                    ],
                    [
                        'breakpoint' => 991,
                        'settings' => [
                            'slidesToShow' => 2,
                        ],
                    ],
                    [
                        'breakpoint' => 575,
                        'settings' => [
                            'arrows' => true,
                            'slidesToShow' => 2,
                            'slidesToScroll' => 1,
                        ],
                    ],
                ],
            ]);
        @endphp
        <section class="ec-cross-sale-section">
            <div class="container">
                <div class="ec-cross-sale-wrapper">
                    <div class="ec-cross-sale-header">
                        <div class="ec-cross-sale-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                        </div>
                        <div class="ec-cross-sale-title">
                            <h4>{{ __('Frequently Bought Together') }}</h4>
                            <p>{{ __('Customers who viewed this item also bought') }}</p>
                        </div>
                    </div>

                    <div class="ec-cross-sale-slider">
                        <div class="ec-cross-sale-carousel slick-slides-carousel" data-slick="{{ json_encode($carouselConfig) }}">
                            @foreach ($products as $index => $product)
                                @php
                                    $productPrice = $product->price();
                                    $salePrice = $productPrice->getPrice();
                                    $originalPrice = $productPrice->getPriceOriginal();
                                    $hasDiscount = $salePrice < $originalPrice;
                                    $shouldShowPrice =
                                        (! EcommerceHelper::hideProductPrice() || EcommerceHelper::isCartEnabled())
                                        && (! EcommerceHelper::hideProductPriceWhenZero() || $salePrice > 0);
                                @endphp
                                <div class="ec-cross-sale-slide">
                                    <div class="ec-cross-sale-card">
                                        @if($index > 0)
                                            <div class="ec-cross-sale-plus">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="ec-cross-sale-card-inner">
                                            <div class="ec-cross-sale-thumb">
                                                <a href="{{ $product->url }}">
                                                    {{ RvMedia::image($product->image, $product->name, 'medium', true) }}
                                                </a>
                                            </div>
                                            <div class="ec-cross-sale-content">
                                                <h3 class="ec-cross-sale-name">
                                                    <a href="{{ $product->url }}" title="{{ $product->name }}">
                                                        {{ $product->name }}
                                                    </a>
                                                </h3>
                                                @if ($shouldShowPrice)
                                                    <div class="ec-cross-sale-price">
                                                        <span class="ec-cross-sale-price-current">{{ format_price($salePrice) }}</span>
                                                        @if($hasDiscount)
                                                            <span class="ec-cross-sale-price-old">{{ format_price($originalPrice) }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                                @if(EcommerceHelper::isCartEnabled())
                                                    <button
                                                        type="button"
                                                        @if($hasVariations = $product->hasVariations)
                                                            data-bb-toggle="quick-shop"
                                                            data-url="{{ route('public.ajax.quick-shop', ['slug' => $product->slug, 'reference_product' => $parentProduct->slug]) }}"
                                                        @else
                                                            data-bb-toggle="add-to-cart"
                                                            data-show-toast-on-success="false"
                                                            data-url="{{ route('public.cart.add-to-cart') }}"
                                                            data-id="{{ $product->id }}"
                                                            {!! EcommerceHelper::jsAttributes('add-to-cart', $product) !!}
                                                        @endif
                                                        class="ec-cross-sale-add-btn {{ $hasVariations ? 'has-options' : '' }}"
                                                        @disabled($product->isOutOfStock())
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <circle cx="9" cy="21" r="1"></circle>
                                                            <circle cx="20" cy="21" r="1"></circle>
                                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                                        </svg>
                                                        @if ($hasVariations)
                                                            {{ __('Select Options') }}
                                                        @else
                                                            {{ __('Add to Cart') }}
                                                        @endif
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="ec-cross-sale-arrows"></div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@elseif(isset($parentProduct))
    <div data-bb-toggle="block-lazy-loading" data-url="{{ route('public.ajax.cross-sale-products', $parentProduct) }}">
        <section class="ec-cross-sale-skeleton">
            <div class="container">
                <div class="ec-cross-sale-skeleton-wrapper">
                    <div class="ec-cross-sale-skeleton-header">
                        <div class="skeleton skeleton-icon"></div>
                        <div class="skeleton-title-group">
                            <div class="skeleton skeleton-title"></div>
                            <div class="skeleton skeleton-subtitle"></div>
                        </div>
                    </div>
                    <div class="ec-cross-sale-skeleton-slider">
                        @for ($i = 0; $i < 5; $i++)
                            <div class="ec-cross-sale-skeleton-card">
                                <div class="skeleton skeleton-thumb"></div>
                                <div class="skeleton-content">
                                    <div class="skeleton skeleton-name"></div>
                                    <div class="skeleton skeleton-price"></div>
                                    <div class="skeleton skeleton-btn"></div>
                                </div>
                            </div>
                        @endfor
                    </div>
                    <div class="ec-cross-sale-skeleton-scrollbar">
                        <div class="skeleton-drag"></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endif
