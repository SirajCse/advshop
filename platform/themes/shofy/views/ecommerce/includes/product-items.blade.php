@php
    $paginationType = theme_option('ecommerce_products_pagination_type', 'default');
    $isInfiniteScroll = $paginationType === 'infinite' && ! isset($shortcode);
@endphp

@if(! isset($shortcode))
    @include(Theme::getThemeNamespace('views.ecommerce.includes.filters.results'))
@endif

@if ($products && $products->isNotEmpty())
    @php
        if ($products instanceof \Illuminate\Database\Eloquent\Collection) {
            $products->loadMissing(['brand']);
        }
    @endphp

    @if ($layout ?? get_product_layout() === 'grid')
        @php
            $itemsPerRow ??= get_products_per_row_by_layout();
            $itemsPerRowOnMobile = theme_option('ecommerce_products_per_row_mobile', 2);
        @endphp

        <div @class(['row mb-30', 'row-cols-xxl-' . $itemsPerRow, 'row-cols-md-' . ($itemsPerRow - 1), 'row-cols-sm-' . $itemsPerRowOnMobile, 'row-cols-' . $itemsPerRowOnMobile, 'bb-infinite-products-grid' => $isInfiniteScroll])>
            @foreach ($products as $product)
                <div class="col">
                    @include(Theme::getThemeNamespace('views.ecommerce.includes.product-item'), ['layout' => 'grid'])
                </div>
            @endforeach
        </div>
    @else
        <div @class(['row mb-30', 'bb-infinite-products-list' => $isInfiniteScroll])>
            <div class="col-xl-12">
                @foreach ($products as $product)
                    @include(Theme::getThemeNamespace('views.ecommerce.includes.product-item'), ['layout' => 'list'])
                @endforeach
            </div>
        </div>
    @endif
@else
    <div class="alert alert-warning rounded-0">
        <div class="d-flex align-items-center gap-2">
            <x-core::icon name="ti ti-info-circle" />
            {{ __('No products were found matching your selection.') }}
        </div>
    </div>

    {!! apply_filters('ecommerce_no_products_found_content', null) !!}
@endif

@if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->hasPages())
    @if ($isInfiniteScroll)
        <div
            class="bb-infinite-scroll-trigger"
            data-next-page-url="{{ $products->nextPageUrl() }}"
            data-current-page="{{ $products->currentPage() }}"
            data-last-page="{{ $products->lastPage() }}"
        >
            @if ($products->hasMorePages())
                <button type="button" class="bb-load-more-btn">
                    {{ __('Load More') }}
                </button>
            @endif
        </div>
    @else
        {{ $products->withQueryString()->links(Theme::getThemeNamespace('partials.pagination')) }}
    @endif
@endif
