<section {!! $shortcode->htmlAttributes() !!} class="tp-product-category pt-60 pb-15"
     @if ($shortcode->background_color)
         style="background-color: {{ $shortcode->background_color }} !important;"
     @endif
>
    <div class="container">
        {!! Theme::partial('section-title', compact('shortcode')) !!}
        <div class="tp-product-categories-slider swiper-container"
             data-items="{{ (int) $shortcode->items_per_view ?: 5 }}"
             {!! Theme\Shofy\Helpers\SliderAutoplayHelper::getDataAttributes($shortcode) !!}>
            <div class="swiper-wrapper">
                @foreach ($categories as $category)
                    <div class="swiper-slide">
                        <div class="tp-product-category-item text-center mb-40">
                            <div class="tp-product-category-thumb fix">
                                <a href="{{ $category->url }}" title="{{ $category->name }}">
                                    {{ RvMedia::image($category->{$imageField}, $category->name) }}
                                </a>
                            </div>
                            <div class="tp-product-category-content">
                                <h3 class="tp-product-category-title">
                                    <a href="{{ $category->url }}" title="{{ $category->name }}">{{ $category->name }}</a>
                                </h3>
                                @if ($shortcode->show_products_count)
                                    <p>
                                        @if ($category->count_all_products === 1)
                                            {{ __('1 product') }}
                                        @else
                                            {{ __(':count products', ['count' => number_format($category->count_all_products)]) }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
