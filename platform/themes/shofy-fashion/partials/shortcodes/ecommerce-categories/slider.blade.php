<section class="tp-category-area pb-50 pt-50"
         @class(['tp-category-area', 'pb-50 pt-50' => ! $shortcode->background_color, 'mb-50 mt-50 pt-20' => $shortcode->background_color])
     @if ($shortcode->background_color)
         style="background-color: {{ $shortcode->background_color }} !important;"
    @endif
>
    <div class="container">
        {!! Theme::partial('section-title', ['shortcode' => $shortcode, 'class' => 'text-center mb-40']) !!}

        <div class="row">
            <div class="col-xl-12">
                <div class="tp-category-slider-2">
                    <div class="tp-category-slider-active-2 swiper-container mb-50">
                        <div class="swiper-wrapper">
                            @foreach ($categories as $category)
                                <div class="tp-category-item-2 p-relative z-index-1 text-center swiper-slide">
                                    <div class="tp-category-thumb-2">
                                        <a href="{{ $category->url }}" title="{{ $category->name }}">
                                            {{ RvMedia::image($category->image, $category->name, 'medium', useDefaultImage: true) }}
                                        </a>
                                    </div>
                                    <div class="tp-category-content-2">
                                        @if ($shortcode->show_products_count)
                                            <span>
                                                @if ($category->count_all_products === 1)
                                                    {{ __('1 product') }}
                                                @else
                                                    {{ __(':count products', ['count' => number_format($category->count_all_products)]) }}
                                                @endif
                                            </span>
                                        @endif
                                        <h3 class="tp-category-title-2">
                                            <a href="{{ $category->url }}" title="{{ $category->name }}">{{ $category->name }}</a>
                                        </h3>
                                        <div class="tp-category-btn-2">
                                            <a href="{{ $category->url }}" class="tp-btn tp-btn-border">
                                                {{ __('Shop Now') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="swiper-scrollbar tp-swiper-scrollbar tp-swiper-scrollbar-drag"></div>
                </div>
            </div>
        </div>
    </div>
</section>
