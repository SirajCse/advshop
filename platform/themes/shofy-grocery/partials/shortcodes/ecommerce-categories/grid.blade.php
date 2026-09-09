<section class="tp-category-area pt-110 pb-110"
     @if ($shortcode->background_color)
         style="background-color: {{ $shortcode->background_color }} !important;"
    @endif
>
    <div class="container">
        {!! Theme::partial('section-title', ['shortcode' => $shortcode, 'centered' => true]) !!}
        <div class="row">
            <div class="col-xl-12">
                <div class="tp-category-slider-5">
                    <div class="tp-category-slider-active-5 swiper-container mb-50">
                        <div class="swiper-wrapper">
                            @foreach($categories as $category)
                                <div class="tp-category-item-5 p-relative z-index-1 fix swiper-slide">
                                    <a href="{{ $category->url }}" title="{{ $category->name }}">
                                        <div
                                            class="tp-category-thumb-5 include-bg"
                                            @if($category->image)
                                                data-background="{{ RvMedia::getImageUrl($category->image) }}"
                                            @endif
                                        ></div>
                                        <div class="tp-category-content-5">
                                            <h3 class="tp-category-title-5">{{ $category->name }}</h3>
                                            @if ($shortcode->show_products_count)
                                                <span>
                                                    @if ($category->count_all_products === 1)
                                                        {{ __('1 product') }}
                                                    @else
                                                        {{ __(':count products', ['count' => number_format($category->count_all_products)]) }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="tp-category-5-swiper-scrollbar tp-swiper-scrollbar"></div>
                </div>
            </div>
        </div>
    </div>
</section>
