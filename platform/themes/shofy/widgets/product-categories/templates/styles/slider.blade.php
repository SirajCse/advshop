<div class="tp-product-categories-slider swiper-container">
    <div class="swiper-wrapper">
        @foreach ($categories as $category)
            <div class="swiper-slide">
                <div class="tp-product-category-item text-center mb-40">
                    <div class="tp-product-category-thumb fix">
                        <a href="{{ $category->url }}" title="{{ $category->name }}">
                            {{ RvMedia::image($category->image, $category->name) }}
                        </a>
                    </div>
                    <div class="tp-product-category-content">
                        <h3 class="tp-product-category-title">
                            <a href="{{ $category->url }}" title="{{ $category->name }}">{{ $category->name }}</a>
                        </h3>
                        <p>
                            @if ($category->count_all_products === 1)
                                {{ __('1 product') }}
                            @else
                                {{ __(':count products', ['count' => number_format($category->count_all_products)]) }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
