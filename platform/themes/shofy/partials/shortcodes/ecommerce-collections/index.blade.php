<section {!! $shortcode->htmlAttributes() !!} class="tp-product-category pt-60 pb-15">
    <div class="container">
        <div class="tp-product-categories-slider swiper-container"
             data-items="{{ (int) $shortcode->items_per_view ?: 5 }}"
             {!! Theme\Shofy\Helpers\SliderAutoplayHelper::getDataAttributes($shortcode) !!}>
            <div class="swiper-wrapper">
                @foreach ($collections as $collection)
                    <div class="swiper-slide">
                        <div class="tp-product-category-item text-center mb-40">
                            <div class="tp-product-category-thumb fix">
                                <a href="{{ route('public.products', ['collections' => [$collection->getKey()]]) }}">
                                    {{ RvMedia::image($collection->image, $collection->name) }}
                                </a>
                            </div>
                            <div class="tp-product-category-content">
                                <h3 class="tp-product-category-title text-truncate">
                                    <a href="{{ route('public.products', ['collections' => [$collection->getKey()]]) }}" title="{{ $collection->name }}">{{ $collection->name }}</a>
                                </h3>
                                <p>
                                    @if ($collection->products_count === 1)
                                        {{ __('1 product') }}
                                    @else
                                        {{ __(':count products', ['count' => number_format($collection->products_count)]) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
