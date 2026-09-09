@if ($displayBasePrice && $basePrice != null)
    <div class="small d-flex gap-2">
        <span>{{ trans('plugins/ecommerce::product-option.price') }}:</span>
        <strong>{{ format_price($basePrice) }}</strong>
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
                    if ($value['affect_price']) {
                        if ($value['affect_type'] == 1) {
                            $price += ($basePrice * $value['affect_price']) / 100;
                        } else {
                            $price += $value['affect_price'];
                        }
                    }
                @endphp
                <strong>{{ $value['option_value'] }}</strong>
                @if ($key + 1 < $totalOptionValue)
                    ,
                @endif
            @endforeach
        </span>
        @if ($price > 0)
            <strong class="text-nowrap ps-2">+ {{ format_price($price) }}</strong>
        @endif
    </div>
@endforeach
