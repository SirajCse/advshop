
@if ($order->sub_total != $order->amount)
    @include('plugins/ecommerce::orders.thank-you.total-row', [
        'label' => trans('plugins/ecommerce::order.sub_amount'),
        'value' => $order->sub_total == 0 ? trans('plugins/ecommerce::ecommerce.free') : format_price($order->sub_total),
    ])
@endif

@if ($order->shipping_method->getValue())
    @include('plugins/ecommerce::orders.thank-you.total-row', [
        'label' =>
            trans('plugins/ecommerce::order.shipping_fee') .
            ($order->is_free_shipping
                ? ' <small>(' . trans('plugins/ecommerce::order.using_coupon_code') . ': <strong>' . $order->coupon_code . '</strong>)</small>'
                : ''),
        'value' => $order->shipping_method_name . ((float) $order->shipping_amount ? ' - ' . format_price($order->shipping_amount) : ' - ' . trans('plugins/ecommerce::ecommerce.free')),
    ])
@endif

@if (EcommerceHelper::isTaxEnabled() && (float) $order->tax_amount)
    @if (EcommerceHelper::isDisplayCheckoutTaxInformation())
        @php
            $taxGroups = [];
            foreach ($order->products as $orderProduct) {
                if ($orderProduct->tax_amount > 0 && !empty($orderProduct->options['taxClasses'])) {
                    foreach ($orderProduct->options['taxClasses'] as $taxName => $taxRate) {
                        $taxKey = $taxName . ' ' . $taxRate . '%';
                        if (!isset($taxGroups[$taxKey])) {
                            $taxGroups[$taxKey] = 0;
                        }
                        $taxGroups[$taxKey] += $orderProduct->tax_amount;
                    }
                }
            }
        @endphp

        @if (!empty($taxGroups))
            @foreach ($taxGroups as $taxName => $taxAmount)
                @include('plugins/ecommerce::orders.thank-you.total-row', [
                    'label' => trans('plugins/ecommerce::order.tax'),
                    'value' => format_price($taxAmount) . ' <small>(' . $taxName . ')</small>',
                ])
            @endforeach
        @else
            @include('plugins/ecommerce::orders.thank-you.total-row', [
                'label' => trans('plugins/ecommerce::order.tax'),
                'value' => format_price($order->tax_amount),
            ])
        @endif
    @else
        @include('plugins/ecommerce::orders.thank-you.total-row', [
            'label' => trans('plugins/ecommerce::order.tax'),
            'value' => format_price($order->tax_amount),
        ])
    @endif
@endif

@if ((float) ($order->shipping_tax_amount ?? 0))
    @include('plugins/ecommerce::orders.thank-you.total-row', [
        'label' => trans('plugins/ecommerce::order.shipping_tax'),
        'value' => format_price($order->shipping_tax_amount),
    ])
@endif

@if ((float) $order->discount_amount)
    @include('plugins/ecommerce::orders.thank-you.total-row', [
        'label' => trans('plugins/ecommerce::order.discount'),
        'value' =>
            format_price($order->discount_amount) .
            ($order->coupon_code
                ? ' <small>(' . trans('plugins/ecommerce::order.using_coupon_code') . ': <strong>' . $order->coupon_code . '</strong>)</small>'
                : ''),
    ])
@endif

@if ((float) $order->payment_fee)
    @include('plugins/ecommerce::orders.thank-you.total-row', [
        'label' => trans('plugins/payment::payment.payment_fee'),
        'value' => format_price($order->payment_fee),
    ])
@endif

{!! apply_filters('ecommerce_thank_you_total_info', null, $order) !!}

@php($isOrderTotalFree = $order->amount == 0)
<div class="row">
    <div class="col-6">
        <p>{{ trans('plugins/ecommerce::order.total_amount') }}:</p>
    </div>
    <div class="col-6 float-end">
        <p class="total-text raw-total-text"> {{ $isOrderTotalFree ? trans('plugins/ecommerce::ecommerce.free') : format_price($order->amount) }} </p>
    </div>
</div>
