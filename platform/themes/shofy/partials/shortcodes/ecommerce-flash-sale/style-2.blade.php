@php
    $title = $shortcode->title;
    $subtitle = $shortcode->subtitle;
    $showProducts = $shortcode->show_products === 'yes';
    $itemsPerView = (int) ($shortcode->items_per_view ?: 4);
@endphp

<section {!! $shortcode->htmlAttributes() !!}
    class="tp-deal-area pt-50 pb-35 p-relative z-index-1 fix scene"
    style="
        @if ($shortcode->background_color) background-color: {{ $shortcode->background_color }}; @endif
        @if ($shortcode->background_image)
            background-image: url({{ RvMedia::getImageUrl($shortcode->background_image) }}); background-size: cover;
        @endif
    "
>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-7">
                <div class="tp-deal-content text-center">
                    @if($title || $subtitle)
                        @if($subtitle)
                            <span class="tp-deal-title-pre">
                                {!! BaseHelper::clean($subtitle) !!}
                                {!! Theme::partial('section-title-shape') !!}
                            </span>
                        @endif
                        @if($title)
                            <h3 class="tp-deal-title">
                                {!! BaseHelper::clean($title) !!}
                            </h3>
                        @endif
                    @endif

                    <div class="tp-deal-countdown">
                        @include(Theme::getThemeNamespace('views.ecommerce.includes.product.countdown'), ['endDate' => $flashSale->end_date->endOfDay()])
                    </div>
                </div>
            </div>
        </div>

        @if($showProducts && $flashSale->products->isNotEmpty())
            <div class="row mt-50">
                <div class="col-xl-12">
                    <div class="tp-product-offer-slider fix">
                        <div class="tp-product-offer-slider-active swiper-container" data-items-per-view="{{ $itemsPerView }}">
                            <div class="swiper-wrapper">
                                @foreach($flashSale->products as $product)
                                    @include(Theme::getThemeNamespace('views.ecommerce.includes.product-item'), ['class' => 'tp-product-offer-item swiper-slide mb-0', 'withCountdown' => true, 'endDate' => $flashSale->end_date->endOfDay(), 'flashSale' => $flashSale])
                                @endforeach
                            </div>
                            <div class="tp-deals-slider-dot tp-swiper-dot text-center mt-40"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
