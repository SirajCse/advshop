<div class="tp-product-countdown" data-countdown data-date="{{ $endDate }}">
    <div class="tp-product-countdown-inner">
        <ul>
            <li><span data-days>0</span> {{ __('Days') }}</li>
            <li><span data-hours>0</span> {{ __('Hrs') }}</li>
            <li><span data-minutes>0</span> {{ __('Mins') }}</li>
            <li><span data-seconds>0</span> {{ __('Secs') }}</li>
        </ul>
    </div>
</div>

@if (isset($flashSale) && isset($product) && $product->pivot && Botble\Ecommerce\Facades\FlashSale::isShowSaleCountLeft())
    <div class="tp-product-flash-sale-info mt-2">
        @if ($product->pivot->quantity > $product->pivot->sold)
            <div class="tp-product-progress-bar" data-value="{{ $product->pivot->quantity > 0 ? ($product->pivot->sold / $product->pivot->quantity) * 100 : 0 }}">
                <div class="tp-progress">
                    <div class="tp-progress-value" style="width: {{ $product->pivot->quantity > 0 ? ($product->pivot->sold / $product->pivot->quantity) * 100 : 0 }}%"></div>
                </div>
                <p class="small">{{ __('Sold') }}: {{ (int)$product->pivot->sold }}/{{ (int)$product->pivot->quantity }}</p>
            </div>
        @else
            <p class="text-danger small">{{ __('Sold out') }}</p>
        @endif
    </div>
@endif
