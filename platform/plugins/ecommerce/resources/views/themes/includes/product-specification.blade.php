@php
    use Botble\Ecommerce\Models\ProductSpecificationAttributeTranslation;

    $currentLangCode = ProductSpecificationAttributeTranslation::getCurrentLanguageCode();

    $visibleAttributes = $product->getVisibleSpecificationAttributes();
    $specDisplayStyle = theme_option('product_specification_display_style', 'rows_columns');
@endphp

@if ($specDisplayStyle === 'table')
    <div class="product-specification-table product-specification-table--table">
        <table class="table">
            <tbody>
                @foreach($visibleAttributes as $attribute)
                    <tr>
                        <td class="spec-label">{{ $attribute->name }}</td>
                        <td class="spec-value">
                            @if ($attribute->type == 'checkbox')
                                @if ($attribute->pivot->value)
                                    <span class="spec-badge spec-badge-success">
                                        <x-core::icon name="ti ti-check" />
                                        {{ __('Yes') }}
                                    </span>
                                @else
                                    <span class="spec-badge spec-badge-danger">
                                        <x-core::icon name="ti ti-x" />
                                        {{ __('No') }}
                                    </span>
                                @endif
                            @else
                                {{ ProductSpecificationAttributeTranslation::getDisplayValue($product, $attribute, $currentLangCode) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="product-specification-table product-specification-table--rows-columns">
        <div class="row">
            @foreach($visibleAttributes as $index => $attribute)
                <div class="col-md-6">
                    <div class="spec-item">
                        <span class="spec-label">{{ $attribute->name }}</span>
                        <span class="spec-value">
                            @if ($attribute->type == 'checkbox')
                                @if ($attribute->pivot->value)
                                    <span class="spec-badge spec-badge-success">
                                        <x-core::icon name="ti ti-check" />
                                        {{ __('Yes') }}
                                    </span>
                                @else
                                    <span class="spec-badge spec-badge-danger">
                                        <x-core::icon name="ti ti-x" />
                                        {{ __('No') }}
                                    </span>
                                @endif
                            @else
                                {{ ProductSpecificationAttributeTranslation::getDisplayValue($product, $attribute, $currentLangCode) }}
                            @endif
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
