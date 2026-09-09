@extends('plugins/ecommerce::orders.master')

@section('title', trans('plugins/ecommerce::order.order_successfully_id', ['id' => $order->code]))

@push('header')
    @include('plugins/ecommerce::orders.partials.google-ads-conversion', ['orders' => [$order]])
@endpush

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
                    <p class="success-order-number">{{ trans('plugins/ecommerce::order.order') }} {{ $order->code }}</p>
                    <h1 class="success-title">{{ trans('plugins/ecommerce::order.thank_you_name', ['name' => $order->address?->name ?: $order->user?->name ?: trans('plugins/ecommerce::order.customer')]) }}</h1>
                </div>
            </div>
        </div>

        <div class="checkout-success-body">
            <div class="checkout-success-main">
                <div class="confirmation-card">
                    <div class="confirmation-card-header">
                        <x-core::icon name="ti ti-circle-check" class="confirmation-icon" />
                        <div class="confirmation-text">
                            <h2>{{ trans('plugins/ecommerce::order.your_order_is_confirmed') }}</h2>
                            <p>{{ trans('plugins/ecommerce::order.order_confirmed_message') }}</p>
                        </div>
                    </div>
                </div>

                @include('plugins/ecommerce::orders.thank-you.customer-info', compact('order'))

                @if(EcommerceHelper::isEnabledSupportDigitalProducts())
                    @include('plugins/ecommerce::orders.partials.digital-product-downloads-frontend', ['order' => $order])
                @endif

                <div class="success-actions">
                    <a class="btn-continue-shopping" href="{{ BaseHelper::getHomepageUrl() }}">
                        {{ trans('plugins/ecommerce::order.continue_shopping') }}
                    </a>
                    @if (auth('customer')->check())
                        <a class="btn-view-orders" href="{{ route('customer.orders') }}">
                            {{ trans('plugins/ecommerce::order.view_order_history') }}
                        </a>
                    @endif
                </div>

                @if (Route::has('public.contact'))
                    <div class="help-section">
                        <p>{{ trans('plugins/ecommerce::order.need_help') }} <a href="{{ route('public.contact') }}">{{ trans('plugins/ecommerce::order.contact_us') }}</a></p>
                    </div>
                @endif
            </div>

            <div class="checkout-success-sidebar">
                <div class="order-summary-card">
                    @include('plugins/ecommerce::orders.thank-you.order-info')
                    @include('plugins/ecommerce::orders.thank-you.total-info', ['order' => $order])
                </div>
            </div>
        </div>
    </div>
@stop
