@php
    $title = $shortcode->title;
    $subtitle = $shortcode->subtitle;
    $buttonLabel = $shortcode->button_label;
    $buttonUrl = $shortcode->button_url;
@endphp

<section class="tp-category-area pt-50"
     @if ($shortcode->background_color)
         style="background-color: {{ $shortcode->background_color }} !important;"
    @endif
>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-8">
                {!! Theme::partial('section-title', compact('shortcode')) !!}
            </div>

            @if($buttonLabel && $buttonUrl)
                <div class="col-lg-6 col-md-4">
                    <div class="tp-category-more-3 text-md-end mb-55">
                        <a href="{{ $buttonUrl }}" class="tp-btn">
                            {{ $buttonLabel }}
                            <svg width="12" height="10" viewBox="0 0 12 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.9994 4.99981L1.04004 4.99981" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M6.98291 1L10.9998 4.99967L6.98291 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endif
        </div>
        <div class="row">
            @foreach($categories as $category)
                <div class="col-lg-3 col-sm-6">
                    <div class="tp-category-item-3 p-relative grey-bg text-center z-index-1 fix mb-30">
                        <a
                            href="{{ $category->url }}"
                            title="{{ $category->name }}"
                            aria-label="{{ $category->name }}"
                            class="tp-category-thumb-3 include-bg d-block"
                            @if($category->image)
                                data-background="{{ RvMedia::getImageUrl($category->image) }}"
                            @endif
                        ></a>
                        <div class="tp-category-content-3 transition-3">
                            <h3 class="tp-category-title-3">
                                <a href="{{ $category->url }}" title="{{ $category->name }}">{{ $category->name }}</a>
                            </h3>
                            @if ($shortcode->show_products_count)
                                <span class="tp-categroy-ammount-3">
                                    @if ($category->count_all_products === 1)
                                        {{ __('1 product') }}
                                    @else
                                        {{ __(':count products', ['count' => number_format($category->count_all_products)]) }}
                                    @endif
                                </span>
                            @endif
                            @if($shortcode->button_view_more_label)
                                <div class="tp-category-btn-3">
                                    <a href="{{ $category->url }}" class="tp-link-btn tp-link-btn-2" title="{{ $category->name }}">
                                        {{ $shortcode->button_view_more_label }}
                                        <svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 1L6.02116 5.99958L1 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
