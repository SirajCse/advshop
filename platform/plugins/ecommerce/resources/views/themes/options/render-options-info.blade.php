@if(! empty($productOptions['optionCartValue']))
    <div class="product-cart-options mt-1">
        @if ($displayBasePrice)
            <div class="small d-flex gap-2">
                <span>{{ trans('plugins/ecommerce::product-option.price') }}:</span>
                <strong>{{ $product->original_product->price()->displayAsText() }}</strong>
            </div>
        @endif

        @foreach ($productOptions['optionCartValue'] as $key => $optionValue)
            @php
                $price = 0;
                $totalOptionValue = count($optionValue);
            @endphp
            @continue(!$totalOptionValue)
            <div class="small d-flex gap-2">
                <span>
                    {{ $productOptions['optionInfo'][$key] }}:
                    @foreach ($optionValue as $value)
                        @php
                            if ($value['affect_type'] == 1) {
                                $price += ($product->original_product->price()->getPrice() * $value['affect_price']) / 100;
                            } else {
                                $price += $value['affect_price'];
                            }
                        @endphp
                        <strong>{{ $value['option_value'] }}</strong>@if (! $loop->last),&nbsp;@endif
                    @endforeach
                </span>
                @if ($price > 0)
                    <strong class="text-nowrap ps-2">+ {{ format_price($price) }}</strong>
                @endif
            </div>
        @endforeach
    </div>
@endif
