<div class="number-items-available">
    @if ($product->stock_status == 'on_backorder')
        <p class="text-warning fw-medium fs-6">{{ __('Warning: This product is on backorder and may take longer to ship.') }}</p>
    @elseif ($product->isOutOfStock())
        <span class="text-danger">{{ __('Out of stock') }}</span>
    @else
        @if (! $productVariation)
            <span class="text-danger">{{ __('Not available') }}
        @else
            @if ($productVariation->stock_status == 'on_backorder')
                <p class="text-warning fw-medium fs-6">{{ __('Warning: This product is on backorder and may take longer to ship.') }}</p>
            @elseif ($productVariation->isOutOfStock())
                <span class="text-danger">{{ __('Out of stock') }}</span>
            @elseif (! $productVariation->with_storehouse_management || $productVariation->quantity < 1)
                <span class="text-success">{{ __('Available') }}</span>
            @elseif ($productVariation->quantity)
                <span class="text-success">
                    @if (EcommerceHelper::showNumberOfProductsInProductSingle())
                        @if ($productVariation->quantity !== 1)
                            {{ __(':number products available', ['number' => $productVariation->quantity]) }}
                        @else
                            {{ __(':number product available', ['number' => $productVariation->quantity]) }}
                        @endif
                    @else
                        {{ __('In stock') }}
                    @endif
                </span>
           @endif
       @endif
    @endif
</div>
