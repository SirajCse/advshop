@php
    $key = 'upsale-' . $product->id . '-' . mt_rand();
@endphp

<div
    class="ec-upsell-attributes product-attributes product-attribute-swatches"
    id="product-attributes-{{ $product->id }}"
    data-target="{{ route('public.web.get-variation-by-attributes', $product->getKey()) }}"
    data-update-url="false"
>
    @php
        $variationInfo = $productVariationsInfo;
        $variationNextIds = [];
    @endphp

    @foreach ($attributeSets as $set)
        @if (! $loop->first)
            @php
                $variationInfo = $productVariationsInfo->where('attribute_set_id', $set->id)->whereIn('variation_id', $variationNextIds);
            @endphp
        @endif

        @php
            $displayAttributes = $attributes->where('attribute_set_id', $set->id);
            $isVisual = $set->display_layout === 'visual';
        @endphp

        @if ($displayAttributes && $displayAttributes->isNotEmpty())
            <div
                @class([
                    'ec-upsell-attribute-group',
                    'visual-swatches-wrapper' => $isVisual,
                    'text-swatches-wrapper' => !$isVisual,
                ])
                data-type="{{ $set->display_layout }}"
                data-slug="{{ $set->slug }}"
            >
                <span class="ec-upsell-attribute-label">{{ $set->title }}:</span>
                <ul @class([
                    'ec-upsell-attribute-options',
                    'visual-swatch' => $isVisual,
                    'text-swatch' => !$isVisual,
                ]) data-slug="{{ $set->slug }}">
                    @foreach ($displayAttributes as $attribute)
                        @php
                            $isDisabled = $variationInfo->where('id', $attribute->id)->isEmpty();
                            $style = $attribute->getAttributeStyle($set, $productVariations);
                        @endphp
                        <li
                            @class([
                                'ec-upsell-attribute-option',
                                'attribute-swatch-item',
                                'disabled' => $isDisabled,
                            ])
                            data-slug="{{ $attribute->slug }}"
                            data-id="{{ $attribute->id }}"
                            @if($isDisabled) title="{{ __('Not available') }}" @endif
                        >
                            <label>
                                <input
                                    type="radio"
                                    name="attribute_{{ $set->slug }}_{{ $key }}"
                                    data-slug="{{ $attribute->slug }}"
                                    value="{{ $attribute->id }}"
                                    @checked($selected->where('id', $attribute->id)->isNotEmpty())
                                    class="product-filter-item"
                                    @if($isDisabled) disabled @endif
                                >
                                @if($isVisual && $style)
                                    <span
                                        class="ec-upsell-attribute-visual"
                                        style="{{ $style }}"
                                        title="{{ $attribute->title }}"
                                    ></span>
                                @else
                                    <span class="ec-upsell-attribute-text">{{ $attribute->title }}</span>
                                @endif
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            [$variationNextIds] = handle_next_attributes_in_product($attributes->where('attribute_set_id', $set->id), $productVariationsInfo, $set->id, $selected->pluck('id')->toArray(), $loop->index, $variationNextIds);
        @endphp
    @endforeach
</div>
