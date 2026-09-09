@php
    $displayAttributes = $attributes->where('attribute_set_id', $set->id);
@endphp

@if ($displayAttributes && $displayAttributes->isNotEmpty())
    <div
        class="bb-product-attribute-swatch dropdown-swatches-wrapper attribute-swatches-wrapper"
        data-type="dropdown"
        data-slug="{{ $set->slug }}"
    >
        <div class="bb-product-attribute-swatch-title">{{ $set->title }}:</div>
        <div class="bb-product-attribute-swatch-list attribute-swatch">
            <select class="form-select product-filter-item">
                <option value="">{{ trans('plugins/ecommerce::products.select_attribute', ['name' => strtolower($set->title)]) }}</option>
                @foreach ($displayAttributes as $attribute)
                    @php
                        $isDisabled = isset($variationInfo) && $variationInfo->where('id', $attribute->id)->isEmpty();
                    @endphp
                    <option
                        data-id="{{ $attribute->id }}"
                        data-slug="{{ $attribute->slug }}"
                        @if (! empty($referenceProduct)) data-reference-product="{{ $referenceProduct->slug }}" @endif
                        value="{{ $attribute->id }}"
                        @selected($selected->where('id', $attribute->id)->isNotEmpty())
                        @disabled($isDisabled)
                    >
                        {{ $attribute->title }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
@endif
