@php
    use Botble\Ecommerce\Models\SpecificationTable;

    $version = EcommerceHelper::getAssetVersion();

    Theme::asset()->add('front-ecommerce-css', 'vendor/core/plugins/ecommerce/css/front-ecommerce.css', version: $version);
    Theme::asset()->add('front-compare-css', 'vendor/core/plugins/ecommerce/css/front-compare.css', version: $version);
    Theme::asset()->container('footer')->add('front-compare-js', 'vendor/core/plugins/ecommerce/js/front-compare.js', ['jquery'], version: $version);

    $hasProducts = $products->isNotEmpty();
    $hasSpecGroups = ($specGroups ?? collect())->isNotEmpty();
    $hasAttributeSets = $attributeSets->isNotEmpty();
    $maxProducts = $maxProducts ?? 4;
    $emptySlots = max(0, $maxProducts - $products->count());
    $shareUrl = $shareUrl ?? route('public.compare');
@endphp

<section class="compare-area pt-50 pb-50">
    <div class="container">
        @if ($hasProducts)
            <header class="compare-header mb-4">
                <h1 class="compare-page-title">
                    {{ trans('plugins/ecommerce::products.compare.heading') }}
                    @if ($products->count() >= 2)
                        <span class="compare-page-title-products">
                            {{ $products->pluck('name')->implode(' ' . trans('plugins/ecommerce::products.compare.vs_separator') . ' ') }}
                        </span>
                    @endif
                </h1>
            </header>

            <div class="compare-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div class="compare-toolbar-info text-muted small">
                    {{ trans('plugins/ecommerce::products.compare.slot_count', ['count' => $products->count(), 'max' => $maxProducts]) }}
                </div>
                <div class="compare-toolbar-actions d-flex flex-wrap gap-2 align-items-center">
                    @if ($hasSpecGroups || $hasAttributeSets)
                        <label class="compare-diff-toggle d-inline-flex align-items-center gap-2 mb-0">
                            <input type="checkbox" class="form-check-input mt-0" data-bb-toggle="compare-diff-only">
                            <span>{{ trans('plugins/ecommerce::products.compare.highlight_differences') }}</span>
                        </label>
                    @endif
                    @if ($products->count() >= 2)
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bb-toggle="compare-copy-link" data-url="{{ url($shareUrl) }}">
                            <x-core::icon name="ti ti-link" />
                            {{ trans('plugins/ecommerce::products.compare.copy_share_link') }}
                        </button>
                    @endif
                </div>
            </div>

            <div class="compare-table table-responsive">
                <table
                    class="table compare-grid text-center"
                    data-max-products="{{ $maxProducts }}"
                    data-copy-success-text="{{ trans('plugins/ecommerce::products.compare.link_copied') }}"
                >
                    <colgroup>
                        <col class="compare-col-label">
                        @foreach ($products as $product)
                            <col class="compare-col-product">
                        @endforeach
                        @for ($i = 0; $i < $emptySlots; $i++)
                            <col class="compare-col-empty">
                        @endfor
                    </colgroup>

                    <thead class="compare-sticky-head">
                    <tr>
                        <th class="compare-row-label" aria-label="{{ trans('plugins/ecommerce::products.product') }}"></th>
                        @foreach ($products as $product)
                            <td class="compare-product-cell" data-product-id="{{ $product->id }}">
                                <button
                                    type="button"
                                    class="compare-product-remove"
                                    data-bb-toggle="remove-from-compare"
                                    data-url="{{ route('public.compare.remove', $product->id) }}"
                                    aria-label="{{ trans('plugins/ecommerce::ecommerce.remove') }}"
                                    title="{{ trans('plugins/ecommerce::ecommerce.remove') }}"
                                >
                                    <x-core::icon name="ti ti-x" />
                                </button>
                                <div class="compare-thumb">
                                    <a href="{{ $product->url }}" tabindex="-1">
                                        {{ RvMedia::image($product->image, $product->name, 'thumb') }}
                                    </a>
                                </div>
                                <h4 class="compare-product-title">
                                    <a href="{{ $product->url }}">{{ $product->name }}</a>
                                </h4>
                                <div class="compare-product-price">
                                    @include(EcommerceHelper::viewPath('includes.product-price'), [
                                        'priceWrapperClassName' => 'compare-price',
                                        'priceClassName' => '',
                                        'priceOriginalWrapperClassName' => '',
                                        'priceOriginalClassName' => 'old-price',
                                    ])
                                </div>
                                @php
                                    $savingsPercent = $product->price > 0 && $product->front_sale_price < $product->price
                                        ? (int) round((($product->price - $product->front_sale_price) / $product->price) * 100)
                                        : 0;
                                @endphp
                                @if ($savingsPercent >= 1)
                                    <div class="compare-savings-badge" aria-label="{{ trans('plugins/ecommerce::products.compare.savings_aria', ['percent' => $savingsPercent]) }}">
                                        <x-core::icon name="ti ti-discount-2" />
                                        {{ trans('plugins/ecommerce::products.compare.savings_off', ['percent' => $savingsPercent]) }}
                                    </div>
                                @endif
                                <div class="compare-product-stock">
                                    <span @class(['compare-stock-badge', 'compare-stock-out' => $product->isOutOfStock(), 'compare-stock-in' => ! $product->isOutOfStock()])>
                                        <span class="compare-stock-dot" aria-hidden="true"></span>
                                        @if ($product->isOutOfStock())
                                            {{ trans('plugins/ecommerce::ecommerce.out_of_stock') }}
                                        @else
                                            {{ trans('plugins/ecommerce::ecommerce.in_stock') }}
                                        @endif
                                    </span>
                                </div>
                                <div class="compare-product-actions">
                                    <button
                                        type="button"
                                        class="btn btn-primary w-100"
                                        data-bb-toggle="add-to-cart"
                                        data-url="{{ route('public.cart.add-to-cart') }}"
                                        data-id="{{ $product->original_product->id }}"
                                        {!! EcommerceHelper::jsAttributes('add-to-cart', $product) !!}
                                    >
                                        <x-core::icon name="ti ti-shopping-cart-plus" />
                                        {{ trans('plugins/ecommerce::ecommerce.add_to_cart_1') }}
                                    </button>
                                </div>
                            </td>
                        @endforeach
                        @for ($i = 0; $i < $emptySlots; $i++)
                            <td class="compare-empty-slot">
                                @include(EcommerceHelper::viewPath('includes.compare-add-slot'))
                            </td>
                        @endfor
                    </tr>
                    </thead>

                    <tbody>
                    @if ($hasSpecGroups)
                        @foreach ($specGroups as $group)
                            <tr class="compare-group-row">
                                <th colspan="{{ 1 + $maxProducts }}" class="compare-group-title text-start">
                                    {{ $group['group']->name }}
                                </th>
                            </tr>
                            @foreach ($group['attributes'] as $attribute)
                                <tr class="compare-spec-row" data-spec-row>
                                    <th class="compare-row-label text-start">{{ $attribute->name }}</th>
                                    @foreach ($products as $product)
                                        @php
                                            $data = SpecificationTable::getAttributeDisplayData($product, $attribute);
                                            $value = trim((string) ($data['displayValue'] ?? ''));
                                        @endphp
                                        <td class="compare-spec-value" data-spec-value="{{ $value }}">
                                            @if ($value !== '')
                                                {!! BaseHelper::clean($value) !!}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    @for ($i = 0; $i < $emptySlots; $i++)
                                        <td class="compare-empty-cell text-muted">—</td>
                                    @endfor
                                </tr>
                            @endforeach
                        @endforeach
                    @endif

                    @if ($hasAttributeSets)
                        <tr class="compare-group-row">
                            <th colspan="{{ 1 + $maxProducts }}" class="compare-group-title text-start">
                                {{ trans('plugins/ecommerce::products.compare.additional_attributes') }}
                            </th>
                        </tr>
                        @foreach ($attributeSets as $attributeSet)
                            <tr class="compare-spec-row" data-spec-row>
                                <th class="compare-row-label text-start">{{ $attributeSet->title }}</th>
                                @foreach ($products as $product)
                                    @php
                                        $rendered = trim((string) render_product_attributes_view_only($product, $attributeSet));
                                    @endphp
                                    <td class="compare-spec-value" data-spec-value="{{ strip_tags($rendered) }}">
                                        @if ($rendered !== '')
                                            {!! $rendered !!}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                @for ($i = 0; $i < $emptySlots; $i++)
                                    <td class="compare-empty-cell text-muted">—</td>
                                @endfor
                            </tr>
                        @endforeach
                    @endif

                    <tr class="compare-spec-row" data-spec-row>
                        <th class="compare-row-label text-start">{{ trans('plugins/ecommerce::products.sku') }}</th>
                        @foreach ($products as $product)
                            <td class="compare-spec-value">{{ $product->sku ? '#' . $product->sku : '—' }}</td>
                        @endforeach
                        @for ($i = 0; $i < $emptySlots; $i++)
                            <td class="compare-empty-cell text-muted">—</td>
                        @endfor
                    </tr>

                    @if (EcommerceHelper::isReviewEnabled())
                        <tr class="compare-spec-row" data-spec-row>
                            <th class="compare-row-label text-start">{{ trans('plugins/ecommerce::review.rating') }}</th>
                            @foreach ($products as $product)
                                <td class="compare-spec-value">
                                    @if (! EcommerceHelper::hideRatingWhenNoReviews() || $product->reviews_count > 0)
                                        <div class="compare-rating d-flex justify-content-center">
                                            @include(EcommerceHelper::viewPath('includes.rating-star'), ['avg' => $product->reviews_avg, 'size' => 80])
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endforeach
                            @for ($i = 0; $i < $emptySlots; $i++)
                                <td class="compare-empty-cell text-muted">—</td>
                            @endfor
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        @else
            @include(EcommerceHelper::viewPath('includes.empty-state'), [
                'icon' => 'ti ti-arrows-left-right',
                'title' => trans('plugins/ecommerce::ecommerce.your_compare_list_is_empty'),
                'description' => trans('plugins/ecommerce::products.compare.empty_description'),
                'label' => trans('plugins/ecommerce::products.compare.browse_products'),
            ])
        @endif

        @if ($hasProducts && $emptySlots > 0)
            @include(EcommerceHelper::viewPath('includes.compare-picker-modal'))
        @endif
    </div>
</section>
