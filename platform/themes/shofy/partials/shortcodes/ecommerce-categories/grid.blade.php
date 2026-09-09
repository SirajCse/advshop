<section {!! $shortcode->htmlAttributes() !!} class="tp-category-area">
    <div class="container">
        {!! Theme::partial('section-title', compact('shortcode')) !!}

        <div class="row">
            @foreach($categories as $category)
                <div class="col-lg-4 col-sm-6">
                    <div
                        class="tp-category-main-box mb-25 p-relative fix"
                        @if ($shortcode->background_color)
                            style="background-color: {{ $shortcode->background_color }} !important;"
                        @endif
                    >
                        <a href="{{ $category->url }}" title="{{ $category->name }}" class="tp-category-main-thumb transition-3">
                            {{ RvMedia::image($category->{$imageField}, $category->name) }}
                        </a>
                        <div class="tp-category-main-content">
                            <h3 class="tp-category-main-title">
                                <a href="{{ $category->url }}" title="{{ $category->name }}">{{ $category->name }}</a>
                            </h3>
                            @if ($shortcode->show_products_count)
                                <span class="tp-category-main-item">
                                    @if ($category->count_all_products === 1)
                                        {{ __('1 product') }}
                                    @else
                                        {{ __(':count products', ['count' => number_format($category->count_all_products)]) }}
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
