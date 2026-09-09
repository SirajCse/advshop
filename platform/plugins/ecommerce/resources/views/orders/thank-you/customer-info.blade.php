@php
    $orders = $order;

    if ($orders instanceof \Illuminate\Support\Collection) {
        $order = $orders->where('is_finished', true)->first();

        if (! $order) {
            $order = $orders->first();
        }
    }

    $userInfo = null;
    if ($order->address && $order->address->id) {
        $userInfo = $order->address;
    } elseif ($order->user && $order->user->id) {
        $userInfo = $order->user;
    }

    $hasShippingInfo = !empty($isShowShipping) && $order->shipping_method->getValue();
    $hasPaymentInfo = is_plugin_active('payment') && $order->payment && $order->payment->id;
@endphp

<div class="customer-info-card">
    <h3 class="card-section-title">{{ trans('plugins/ecommerce::order.customer_information') }}</h3>

    <div class="info-grid">
        @if ($userInfo)
            <div class="info-block">
                <h4 class="info-block-title">{{ trans('plugins/ecommerce::order.contact_information') }}</h4>
                <div class="info-block-content">
                    @if ($userInfo->email)
                        <p class="info-item">
                            <x-core::icon name="ti ti-mail" class="info-icon" />
                            <span>{{ $userInfo->email }}</span>
                        </p>
                    @endif
                    @if ($userInfo->phone)
                        <p class="info-item">
                            <x-core::icon name="ti ti-phone" class="info-icon" />
                            <span>{{ $userInfo->phone }}</span>
                        </p>
                    @endif
                </div>
            </div>

            @if ($order->full_address || ($userInfo->name && !in_array('address', EcommerceHelper::getHiddenFieldsAtCheckout())))
                <div class="info-block">
                    <h4 class="info-block-title">{{ $hasShippingInfo ? trans('plugins/ecommerce::order.checkout.shipping_address') : trans('plugins/ecommerce::order.customer_details') }}</h4>
                    <div class="info-block-content">
                        @if ($userInfo->name)
                            <p class="info-item">{{ $userInfo->name }}</p>
                        @endif
                        @if ($order->full_address && !in_array('address', EcommerceHelper::getHiddenFieldsAtCheckout()))
                            <p class="info-item address-text">{{ $order->full_address }}</p>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        @if ($hasShippingInfo)
            <div class="info-block">
                <h4 class="info-block-title">{{ trans('plugins/ecommerce::shipping.shipping_method') }}</h4>
                <div class="info-block-content">
                    <p class="info-item">
                        <x-core::icon name="ti ti-truck" class="info-icon" />
                        <span>{{ $order->shipping_method_name }}</span>
                    </p>
                    @if ((float) $order->shipping_amount)
                        <p class="info-item-secondary">{{ format_price($order->shipping_amount) }}</p>
                    @else
                        <p class="info-item-secondary shipping-free">{{ trans('plugins/ecommerce::ecommerce.free') }}</p>
                    @endif
                </div>
            </div>
        @endif

        @if ($hasPaymentInfo)
            <div class="info-block">
                <h4 class="info-block-title">{{ trans('plugins/ecommerce::order.payment_method') }}</h4>
                <div class="info-block-content">
                    <p class="info-item">
                        <x-core::icon name="ti ti-credit-card" class="info-icon" />
                        <span>{{ $order->payment->payment_channel->displayName() }}</span>
                    </p>
                    <p class="info-item payment-status">
                        <span class="status-badge status-{{ $order->payment->status->getValue() }}">
                            {{ $order->payment->status->label() }}
                        </span>
                    </p>
                </div>
            </div>
        @endif
    </div>

    @if ($hasPaymentInfo)
        @if (
            setting('payment_bank_transfer_display_bank_info_at_the_checkout_success_page', false) &&
            ($bankInfo = OrderHelper::getOrderBankInfo($orders))
        )
            <div class="payment-info-full-width bank-info-block">
                {!! $bankInfo !!}
            </div>
        @else
            <div class="payment-info-full-width">
                @include('plugins/ecommerce::orders.partials.payment-proof-upload')
            </div>
        @endif
    @endif

    {!! apply_filters('ecommerce_thank_you_customer_info', null, $order) !!}
</div>

@if ($tax = $order->taxInformation)
    <div class="customer-info-card tax-info-card">
        <h3 class="card-section-title">{{ trans('plugins/ecommerce::order.tax_information') }}</h3>
        <div class="info-grid">
            <div class="info-block info-block-full">
                <div class="tax-info-grid">
                    <div class="tax-info-item">
                        <span class="tax-label">{{ trans('plugins/ecommerce::order.tax_info.company_name') }}</span>
                        <span class="tax-value">{{ $tax->company_name }}</span>
                    </div>
                    <div class="tax-info-item">
                        <span class="tax-label">{{ trans('plugins/ecommerce::order.tax_info.company_tax_code') }}</span>
                        <span class="tax-value">{{ $tax->company_tax_code }}</span>
                    </div>
                    <div class="tax-info-item">
                        <span class="tax-label">{{ trans('plugins/ecommerce::order.tax_info.company_email') }}</span>
                        <span class="tax-value">{{ $tax->company_email }}</span>
                    </div>
                    <div class="tax-info-item">
                        <span class="tax-label">{{ trans('plugins/ecommerce::order.tax_info.company_address') }}</span>
                        <span class="tax-value">{{ $tax->company_address }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
