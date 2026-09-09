@php
    $dataForFilter = EcommerceHelper::dataForFilter($category ?? null);
    $dataForFilter = array_pad($dataForFilter, 9, null);
    [$categories, $brands, $tags, $rand, $categoriesRequest, $urlCurrent, $categoryId, $maxFilterPrice, $labels] = $dataForFilter;
    $labels = $labels ?: collect();

    $requestBrands = EcommerceHelper::parseFilterParams(request(), 'brands');
    $requestTags = EcommerceHelper::parseFilterParams(request(), 'tags');
    $requestLabels = EcommerceHelper::parseFilterParams(request(), 'labels');
    $requestCategories = EcommerceHelper::parseFilterParams(request(), 'categories');

    $brands = $brands->whereIn('id', $requestBrands);
    $tags = $tags->whereIn('id', $requestTags);
    $labels = $labels->whereIn('id', $requestLabels);
    $categories = $categories->whereIn('id', $requestCategories);

    $attributeSets = app(\Botble\Ecommerce\Supports\RenderProductAttributeSetsOnSearchPageSupport::class)->getAttributeSets();
@endphp

@if($brands->isNotEmpty() || $tags->isNotEmpty() || $labels->isNotEmpty() || request()->input('attributes', []))
    <div class="bb-product-filter-result">
        @foreach($brands as $brand)
            <a href="{{ request()->fullUrlWithQuery([...request()->except('brands'), 'brands' => implode(',', array_diff($requestBrands, [$brand->id]))]) }}" class="bb-product-filter-clear">
                <x-core::icon name="ti ti-x" />
                {{ $brand->name }}
            </a>
        @endforeach

        @foreach($tags as $tag)
            <a href="{{ request()->fullUrlWithQuery([...request()->except('tags'), 'tags' => implode(',', array_diff($requestTags, [$tag->id]))]) }}" class="bb-product-filter-clear">
                <x-core::icon name="ti ti-x" />
                {{ $tag->name }}
            </a>
        @endforeach

        @foreach($labels as $label)
            <a href="{{ request()->fullUrlWithQuery([...request()->except('labels'), 'labels' => implode(',', array_diff($requestLabels, [$label->id]))]) }}" class="bb-product-filter-clear">
                <x-core::icon name="ti ti-x" />
                {{ $label->name }}
            </a>
        @endforeach

        @foreach($attributeSets as $attributeSet)
            @foreach((array) request()->input('attributes', []) as $slug => $values)
                @continue($slug !== $attributeSet->slug || ! is_array($values) || empty($values))
                @foreach($values as $value)
                    @php
                        $attribute = $attributeSet->attributes->where('id', $value)->first();
                    @endphp

                    @if($attribute)
                        <a href="{{ request()->fullUrlWithQuery([...request()->except('attributes.' . $slug), "attributes[{$slug}]" => array_diff(request()->input("attributes.{$slug}", []), [$value])]) }}" class="bb-product-filter-clear">
                            <x-core::icon name="ti ti-x" />
                            <span>{{ $attributeSet->title }}:</span> {{ $attribute->title }}
                        </a>
                    @endif
                @endforeach
            @endforeach
        @endforeach

        <a href="{{ request()->url() }}" class="bb-product-filter-clear-all">
            <x-core::icon name="ti ti-x" />
            {{ __('Clear all') }}
        </a>
    </div>
@endif
