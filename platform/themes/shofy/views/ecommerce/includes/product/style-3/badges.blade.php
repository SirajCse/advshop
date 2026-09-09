<div class="tp-product-badge-3">
    @if ($product->isOutOfStock())
        <span class="product-out-stock">{{ __('Out Of Stock') }}</span>
    @else
        @if ($product->productLabels->isNotEmpty())
            @foreach ($product->productLabels as $label)
                <span {!! $label->css_styles !!}>{{ $label->name }}</span>
            @endforeach
        @else
            {{-- Compare numeric values only — a product is on sale when its own
                sale_price is genuinely lower than its base price. --}}
            @php
                $rawSalePrice = $product->getRawSalePrice();
                $basePrice = $product->getRawPrice();
                $hasOwnSalePrice = $rawSalePrice !== null && $rawSalePrice > 0 && $rawSalePrice < $basePrice;
            @endphp
            @if ($hasOwnSalePrice)
                <span class="product-sale">{{ get_sale_percentage($product->price, $product->front_sale_price) }}</span>
            @endif
        @endif
    @endif
</div>
