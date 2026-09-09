@php
    SeoHelper::setTitle(__('Page not found') . ' - ' . Theme::getSiteTitle());
    Theme::fireEventGlobalAssets();

    $showSearch = theme_option('404_page_search_enabled', true);
    $showCategories = theme_option('404_page_categories_enabled', true);
    $showProducts = theme_option('404_page_products_enabled', true);
    $showPosts = theme_option('404_page_posts_enabled', true);

    $featuredProducts = $showProducts && is_plugin_active('ecommerce') ? get_featured_products(['take' => 4, 'with' => ['slugable']]) : collect();
    $recentPosts = $showPosts && is_plugin_active('blog') ? get_recent_posts(3) : collect();
    $categories = $showCategories && is_plugin_active('ecommerce') ? ProductCategoryHelper::getProductCategoriesWithUrl()->where('parent_id', 0)->take(8) : collect();
@endphp

@extends(Theme::getThemeNamespace('layouts.base'))

@section('content')
    <section class="tp-error-area pt-80 pb-80">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-7 col-lg-8 col-md-10">
                    <div class="tp-error-hero text-center">
                        <div class="tp-error-thumb" style="margin-bottom: 10px;">
                            <img src="{{ theme_option('404_page_image') ? RvMedia::getImageUrl(theme_option('404_page_image')) : Theme::asset()->url('images/404.png') }}" alt="{{ Theme::getSiteTitle() }}" style="max-height: 200px; width: auto;">
                        </div>

                        <h3 style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">{{ theme_option('404_page_title') ?: __('Oops! Page not found') }}</h3>
                        <p style="color: #55585b; font-size: 15px; margin-bottom: 25px;">{{ theme_option('404_page_description') ?: __("The page you were looking for doesn't exist or has been moved.") }}</p>

                        @if ($showSearch && is_plugin_active('ecommerce'))
                            <div style="max-width: 480px; margin: 0 auto 20px;">
                                <x-plugins-ecommerce::fronts.ajax-search>
                                    <div class="tp-search-input tp-error-search-box">
                                        <x-plugins-ecommerce::fronts.ajax-search.input />
                                        <button type="submit" aria-label="{{ __('Search') }}"><x-core::icon name="ti ti-search" /></button>
                                    </div>
                                </x-plugins-ecommerce::fronts.ajax-search>
                            </div>
                        @endif

                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="{{ BaseHelper::getHomepageUrl() }}" class="tp-error-btn">{{ __('Back to Home') }}</a>
                            @if (is_plugin_active('ecommerce'))
                                <a href="{{ route('public.products') }}" class="tp-error-btn tp-error-btn-outline">{{ __('Browse Products') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($categories->isNotEmpty())
                <div class="row mt-60">
                    <div class="col-12 text-center mb-30">
                        <h4 style="font-size: 20px; font-weight: 600;">{{ __('Browse Categories') }}</h4>
                    </div>
                    <div class="col-12">
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            @foreach ($categories as $category)
                                <a href="{{ $category->url }}" class="tp-error-category-link">{{ $category->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if ($featuredProducts->isNotEmpty())
                <div class="row mt-60">
                    <div class="col-12 text-center mb-30">
                        <h4 style="font-size: 20px; font-weight: 600;">{{ __('Popular Products') }}</h4>
                    </div>
                    @foreach ($featuredProducts as $product)
                        <div class="col-xl-3 col-lg-3 col-sm-6">
                            @include(Theme::getThemeNamespace('views.ecommerce.includes.product-item'))
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($recentPosts->isNotEmpty())
                <div class="row mt-60">
                    <div class="col-12 text-center mb-30">
                        <h4 style="font-size: 20px; font-weight: 600;">{{ __('Recent Articles') }}</h4>
                    </div>
                    @foreach ($recentPosts as $post)
                        <div class="col-xl-4 col-lg-4 col-md-6">
                            <div class="tp-error-post mb-30">
                                <a href="{{ $post->url }}" style="display: block; overflow: hidden; border-radius: 8px; margin-bottom: 15px;">
                                    {{ RvMedia::image($post->image, $post->name, 'medium', attributes: ['style' => 'width: 100%; height: 200px; object-fit: cover; transition: transform 0.3s;', 'onmouseover' => "this.style.transform='scale(1.05)'", 'onmouseout' => "this.style.transform='scale(1)'"]) }}
                                </a>
                                <h5 style="font-size: 16px; font-weight: 600; line-height: 1.4;" class="line-clamp-2">
                                    <a href="{{ $post->url }}" style="color: var(--tp-common-black); transition: color 0.3s;" onmouseover="this.style.color='var(--primary-color)'" onmouseout="this.style.color='var(--tp-common-black)'">{{ $post->name }}</a>
                                </h5>
                                <span style="font-size: 13px; color: #999;">{{ $post->created_at->translatedFormat('M d, Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <style>
        .tp-error-area .tp-error-btn {
            font-size: 14px;
            padding: 9px 24px;
            border-radius: 4px;
            transition: all 0.3s;
        }
        .tp-error-btn-outline {
            background-color: transparent !important;
            border: 1.5px solid var(--tp-common-black) !important;
            color: var(--tp-common-black) !important;
        }
        .tp-error-btn-outline:hover {
            background-color: var(--tp-common-black) !important;
            color: #fff !important;
        }
        .tp-error-search-box {
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border-radius: 8px;
        }
        .tp-error-category-link {
            display: inline-block;
            padding: 8px 20px;
            border: 1px solid #e0e2e3;
            border-radius: 30px;
            color: var(--tp-common-black);
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .tp-error-category-link:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }
    </style>
@endsection
