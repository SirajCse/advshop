@include('plugins/payment::partials.header')

<div class="checkout-wrapper">
    <div>
        <x-core::form
            :url="$action"
            class="payment-checkout-form"
            method="post"
        >
            <input name="name" type="hidden" value="{{ $name }}">
            <input name="amount" type="hidden" value="{{ $amount }}">
            <input name="currency" type="hidden" value="{{ $currency }}">
            @if (isset($returnUrl))
                <input name="return_url" type="hidden" value="{{ $returnUrl }}">
            @endif
            @if (isset($callbackUrl))
                <input name="callback_url" type="hidden" value="{{ $callbackUrl }}">
            @endif

            {!! apply_filters(PAYMENT_FILTER_PAYMENT_PARAMETERS, null) !!}

            @include('plugins/payment::partials.payment-methods')

            {!! apply_filters(PAYMENT_FILTER_AFTER_PAYMENT_METHOD, null) !!}

            <x-core::button
                class="payment-checkout-btn"
                color="primary w-100"
                data-processing-text="{{ trans('plugins/payment::payment.processing_please_wait') }}"
                data-error-header="{{ trans('plugins/payment::payment.error') }}"
                icon="ti ti-credit-card"
            >
                {{ trans('plugins/payment::payment.checkout') }}
            </x-core::button>
        </x-core::form>
    </div>
</div>

@include('plugins/payment::partials.footer')
