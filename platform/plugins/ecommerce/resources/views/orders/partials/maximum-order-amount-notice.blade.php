<div
    class="alert alert-warning"
    style="margin-top: 15px;"
>
    {{ trans('plugins/ecommerce::setting.payment_method_maximum_amount_error', ['payment_method' => $paymentLabel, 'amount' => format_price($maximumOrderAmount), 'more' => format_price(Cart::instance('cart')->rawSubTotal() - $maximumOrderAmount)]) }}
</div>
