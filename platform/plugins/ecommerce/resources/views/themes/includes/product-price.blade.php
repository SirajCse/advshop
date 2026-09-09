@php
    if (! isset($product)) {
        return;
    }

    $price ??= $product->price()->getPrice();
    $isDisplayPriceOriginal ??= true;
    $priceWrapperClassName ??= null;
    $priceClassName ??= null;
    $priceOriginalClassName ??= null;
    $priceOriginalWrapperClassName ??= null;
    $shouldShowPrice =
        (! EcommerceHelper::hideProductPrice() || EcommerceHelper::isCartEnabled())
        && (! EcommerceHelper::hideProductPriceWhenZero() || $price > 0);
@endphp

@if ($shouldShowPrice)
    <div class="{{ $priceWrapperClassName === null ? 'bb-product-price mb-3' : $priceWrapperClassName }}">
        <span
            class="{{ $priceClassName === null ? 'bb-product-price-text fw-bold' : $priceClassName }}"
            data-bb-value="product-price"
        >{{ $priceFormatted ?? $product->price()->displayAsText() }}</span>

        @if ($isDisplayPriceOriginal)
            @include(EcommerceHelper::viewPath('includes.product-prices.original'), [
                'priceWrapperClassName' => $priceOriginalWrapperClassName,
                'priceClassName' => $priceOriginalClassName,
            ])
        @endif
    </div>
@endif
