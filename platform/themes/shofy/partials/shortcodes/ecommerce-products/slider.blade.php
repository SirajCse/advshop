@php
    $slidesToShow = $shortcode->slides_to_show ?: 4;

    if ($shortcode->with_sidebar) {
        $slidesToShow = $slidesToShow - 1;
    }

    $sliderUniqueId = 'tp-arrival-slider-' . uniqid();
@endphp

<section {!! $shortcode->htmlAttributes() !!} class="tp-product-arrival-area pt-30 pb-30"
     @if ($shortcode->background_color)
         style="background-color: {{ $shortcode->background_color }} !important;"
    @endif
>
    <div class="container">
        <div class="tp-product-arrival-wrapper">
            <div class="row align-items-center mb-40">
                <div class="col-xl-5 col-sm-6">
                    {!! Theme::partial('section-title', compact('shortcode')) !!}
                </div>
                <div class="col-xl-7 col-sm-6">
                    <div class="tp-product-arrival-more-wrapper d-flex justify-content-end">
                        <div class="tp-product-arrival-arrow tp-swiper-arrow text-end tp-product-arrival-border">
                            <button type="button" class="tp-arrival-slider-button-prev tp-arrival-prev-{{ $sliderUniqueId }}" aria-label="{{ __('Previous') }}">
                                <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7 13L1 7L7 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <button type="button" class="tp-arrival-slider-button-next tp-arrival-next-{{ $sliderUniqueId }}" aria-label="{{ __('Next') }}">
                                <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 13L7 7L1 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        @if($shortcode->with_sidebar)
            <div class="row">
                <div class="col-xl-4 col-lg-5">
                    @include(Theme::getThemeNamespace('partials.shortcodes.ecommerce-products.partials.sidebar'))
                </div>
                <div class="col-xl-8 col-lg-7">
                    @endif

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="tp-product-arrival-slider fix">
                                <div class="tp-product-arrival-active swiper-container"
                                     id="{{ $sliderUniqueId }}"
                                     data-items-per-view="{{ $slidesToShow }}"
                                     data-slider-id="{{ $sliderUniqueId }}"
                                     {!! Theme\Shofy\Helpers\SliderAutoplayHelper::getDataAttributes($shortcode) !!}>
                                    <div class="swiper-wrapper">
                                        @foreach($products as $product)
                                            @include(Theme::getThemeNamespace('views.ecommerce.includes.product-item'), ['class' => 'swiper-slide'])
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($shortcode->with_sidebar)
                </div>
            </div>
        @endif
        </div>
    </div>
</section>
