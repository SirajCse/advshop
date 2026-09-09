@php
    $checkoutOrderAmount = $orderAmount ?? Cart::instance('cart')->rawTotal();
    $isOrderFree = $checkoutOrderAmount == 0;
    $checkoutButtonText = $isOrderFree
        ? trans('plugins/ecommerce::ecommerce.complete_order')
        : trans('plugins/ecommerce::ecommerce.checkout');
@endphp

@if (EcommerceHelper::isValidToProcessCheckout())
    <button
        class="btn payment-checkout-btn payment-checkout-btn-step checkout-btn-responsive"
        data-processing-text="{{ __('Processing. Please wait...') }}"
        data-error-header="{{ __('Error') }}"
        type="submit"
        style="
            min-height: 48px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
        "
    >
        {{ $checkoutButtonText }}
    </button>
@else
    <span
        class="btn payment-checkout-btn-step checkout-btn-responsive disabled"
        style="
            min-height: 48px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
        "
    >
        {{ $checkoutButtonText }}
    </span>
@endif

<style>
@media (min-width: 768px) {
    .checkout-btn-responsive {
        width: auto !important;
        float: right;
        min-width: 150px;
    }

    [dir="rtl"] .checkout-btn-responsive {
        float: left;
    }
}
</style>
