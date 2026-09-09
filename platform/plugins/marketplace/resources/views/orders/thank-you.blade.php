@extends('plugins/ecommerce::orders.master')

@section('title', trans('plugins/ecommerce::order.order_successfully_at_site', ['site_title' => Theme::getSiteTitle()]))

@push('header')
    @include('plugins/ecommerce::orders.partials.google-ads-conversion', ['orders' => $orders])
@endpush

@php
    $firstOrder = $orders->first();
    $customerName = $firstOrder->address?->name ?: $firstOrder->user?->name ?: trans('plugins/marketplace::order.thank_you.customer');
@endphp

@section('content')
    <div class="checkout-success-page">
        <div class="checkout-success-header">
            @include('plugins/ecommerce::orders.partials.logo')

            <div class="success-confirmation">
                <div class="success-checkmark">
                    <svg viewBox="0 0 52 52" class="checkmark-svg">
                        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>

                <div class="success-message">
                    <p class="success-order-number">
                        @if ($orders->count() > 1)
                            {{ trans('plugins/marketplace::order.thank_you.count_orders', ['count' => $orders->count()]) }}
                        @else
                            {{ trans('plugins/marketplace::order.thank_you.order') }} {{ $firstOrder->code }}
                        @endif
                    </p>
                    <h1 class="success-title">{{ trans('plugins/marketplace::order.thank_you.thank_you_name', ['name' => $customerName]) }}</h1>
                </div>
            </div>
        </div>

        <div class="checkout-success-body">
            <div class="checkout-success-main">
                <div class="confirmation-card">
                    <div class="confirmation-card-header">
                        <x-core::icon name="ti ti-circle-check" class="confirmation-icon" />
                        <div class="confirmation-text">
                            <h2>{{ trans('plugins/marketplace::order.thank_you.order_confirmed') }}</h2>
                            <p>{{ trans('plugins/marketplace::order.thank_you.order_accepted') }}</p>
                        </div>
                    </div>
                </div>

                @include('plugins/ecommerce::orders.thank-you.customer-info', [
                    'order' => $orders,
                    'isShowShipping' => false,
                ])

                @if(EcommerceHelper::isEnabledSupportDigitalProducts())
                    @foreach($orders as $order)
                        @include('plugins/ecommerce::orders.partials.digital-product-downloads-frontend', ['order' => $order])
                    @endforeach
                @endif

                <div class="success-actions">
                    <a class="btn-continue-shopping" href="{{ BaseHelper::getHomepageUrl() }}">
                        {{ trans('plugins/ecommerce::order.continue_shopping') }}
                    </a>
                    @if (auth('customer')->check())
                        <a class="btn-view-orders" href="{{ route('customer.orders') }}">
                            {{ trans('plugins/marketplace::order.thank_you.view_order_history') }}
                        </a>
                    @endif
                </div>

                @if (Route::has('public.contact'))
                    <div class="help-section">
                        <p>{{ trans('plugins/marketplace::order.thank_you.need_help') }} <a href="{{ route('public.contact') }}">{{ trans('plugins/marketplace::order.thank_you.contact_us') }}</a></p>
                    </div>
                @endif
            </div>

            <div class="checkout-success-sidebar">
                @php
                    $isUnifiedShipping = ! MarketplaceHelper::isChargeShippingPerVendor();
                    $totalShippingAmount = $orders->sum('shipping_amount');
                    $hasShipping = $orders->filter(fn ($order) => $order->shipment && $order->shipment->id)->count() > 0;
                @endphp

                @foreach ($orders as $order)
                    <div class="order-summary-card @if (!$loop->last) mb-3 @endif">
                        @include('plugins/ecommerce::orders.thank-you.order-info', ['isShowTotalInfo' => false])

                        <div class="order-totals-section">
                            @if ($order->sub_total != $order->amount)
                                @include('plugins/ecommerce::orders.thank-you.total-row', [
                                    'label' => trans('plugins/ecommerce::order.sub_amount'),
                                    'value' => format_price($order->sub_total),
                                ])
                            @endif

                            @if ($order->shipping_method->getValue())
                                @include('plugins/ecommerce::orders.thank-you.total-row', [
                                    'label' => trans('plugins/ecommerce::order.shipping_fee'),
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
                                    'value' => format_price($order->discount_amount) .
                                        ($order->coupon_code ? ' <small>(' . trans('plugins/ecommerce::order.using_coupon_code') . ': <strong>' . $order->coupon_code . '</strong>)</small>' : ''),
                                ])
                            @endif

                            @if ((float) $order->payment_fee)
                                @include('plugins/ecommerce::orders.thank-you.total-row', [
                                    'label' => trans('plugins/payment::payment.payment_fee'),
                                    'value' => format_price($order->payment_fee),
                                ])
                            @endif

                            <div class="order-total-row">
                                <span class="order-total-label">{{ trans('plugins/ecommerce::order.total_amount') }}:</span>
                                <span class="order-total-value">{{ format_price($order->amount) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if (count($orders) > 1)
                    <div class="order-summary-card order-grand-total-card mt-3">
                        <div class="order-summary-header">
                            <h3 class="order-summary-title">{{ trans('plugins/marketplace::order.thank_you.order_total') }}</h3>
                        </div>

                        <div class="order-totals-section">
                            @include('plugins/ecommerce::orders.thank-you.total-row', [
                                'label' => trans('plugins/ecommerce::order.sub_amount'),
                                'value' => format_price($orders->sum('sub_total')),
                            ])

                            @if ($hasShipping && $totalShippingAmount > 0)
                                @include('plugins/ecommerce::orders.thank-you.total-row', [
                                    'label' => trans('plugins/ecommerce::order.shipping_fee'),
                                    'value' => format_price($totalShippingAmount),
                                ])
                            @endif

                            @if ($orders->sum('discount_amount'))
                                @include('plugins/ecommerce::orders.thank-you.total-row', [
                                    'label' => trans('plugins/ecommerce::order.discount'),
                                    'value' => format_price($orders->sum('discount_amount')),
                                ])
                            @endif

                            @if (EcommerceHelper::isTaxEnabled() && $orders->sum('tax_amount'))
                                @include('plugins/ecommerce::orders.thank-you.total-row', [
                                    'label' => trans('plugins/ecommerce::order.tax'),
                                    'value' => format_price($orders->sum('tax_amount')),
                                ])
                            @endif

                            @if ($orders->sum('shipping_tax_amount'))
                                @include('plugins/ecommerce::orders.thank-you.total-row', [
                                    'label' => trans('plugins/ecommerce::order.shipping_tax'),
                                    'value' => format_price($orders->sum('shipping_tax_amount')),
                                ])
                            @endif

                            @if ($orders->sum('payment_fee'))
                                @include('plugins/ecommerce::orders.thank-you.total-row', [
                                    'label' => trans('plugins/payment::payment.payment_fee'),
                                    'value' => format_price($orders->sum('payment_fee')),
                                ])
                            @endif

                            <div class="order-total-row order-grand-total">
                                <span class="order-total-label">{{ trans('plugins/ecommerce::order.total_amount') }}:</span>
                                <span class="order-total-value">{{ format_price($orders->sum('amount')) }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop
