@php
    $order = null;
    if (isset($orders) && $orders instanceof \Illuminate\Support\Collection) {
        $order = $orders->where('is_finished', true)->first();
        if (!$order) {
            $order = $orders->first();
        }
    }
@endphp

@if ($order && $order->isPaymentProofEnabled())
    @php
        $isCustomer = auth('customer')->check();
        $guestProofEnabled = EcommerceHelper::isGuestPaymentProofEnabled();

        $uploadRoute = $isCustomer
            ? route('customer.orders.upload-proof', $order)
            : ($guestProofEnabled ? route('public.orders.upload-proof-guest', $order->token) : null);

        $downloadRoute = $isCustomer
            ? route('customer.orders.download-proof', $order)
            : ($guestProofEnabled ? route('public.orders.download-proof-guest', $order->token) : null);
    @endphp

    @if ($order->canBeCanceled() && ($isCustomer || $guestProofEnabled))
        <div class="payment-proof-upload">
            <x-core::alert type="info" :icon="false" class="mb-0">
                <div class="w-100">
                    <div class="payment-proof-upload__header">
                        <div class="payment-proof-upload__icon">
                            <x-core::icon name="ti ti-receipt" />
                        </div>
                        <div class="payment-proof-upload__content">
                            <h6 class="payment-proof-upload__title">{{ __('Payment Proof') }}</h6>
                            <x-core::form method="post" :files="true" class="customer-order-upload-receipt" :url="$uploadRoute">
                                @if (! $order->proof_file)
                                    <p class="payment-proof-upload__message">{{ __('For expedited processing, kindly upload a copy of your payment proof:') }}</p>
                                @else
                                    <p class="payment-proof-upload__message">{{ __('You have uploaded a copy of your payment proof.') }}</p>
                                    <div class="payment-proof-upload__file-preview">
                                        <span class="payment-proof-upload__file-preview-label">{{ __('View Receipt:') }}</span>
                                        <a href="{{ $downloadRoute }}" target="_blank" class="payment-proof-upload__file-preview-link">
                                            <x-core::icon name="ti ti-file" />
                                            {{ basename($order->proof_file) }}
                                        </a>
                                    </div>
                                    <p class="payment-proof-upload__replace-text">{{ __('Or you can upload a new one, the old one will be replaced.') }}</p>
                                @endif
                                <div class="payment-proof-upload__upload-form">
                                    <div class="bb-file-upload-wrapper">
                                        <input type="file" name="file" id="payment-proof-file-guest" class="bb-file-input" accept=".jpg,.jpeg,.png,.pdf">
                                        <label for="payment-proof-file-guest" class="bb-file-label">
                                            <div class="bb-file-icon">
                                                <x-core::icon name="ti ti-cloud-upload" />
                                            </div>
                                            <div class="bb-file-text">
                                                <span class="bb-file-placeholder">{{ trans('plugins/ecommerce::customer-dashboard.choose_file') }}</span>
                                                <span class="bb-file-name"></span>
                                            </div>
                                            <span class="bb-file-button">{{ trans('plugins/ecommerce::customer-dashboard.browse') }}</span>
                                        </label>
                                        <button type="submit" class="btn payment-checkout-btn bb-upload-submit">
                                            <x-core::icon name="ti ti-upload" />
                                            {{ trans('plugins/ecommerce::customer-dashboard.upload') }}
                                        </button>
                                    </div>
                                </div>
                                <small class="payment-proof-upload__help-text">{{ __('You can upload the following file types: jpg, jpeg, png, pdf and max file size is 2MB.') }}</small>
                            </x-core::form>
                        </div>
                    </div>
                </div>
            </x-core::alert>
        </div>
    @elseif ($order->proof_file && ($isCustomer || $guestProofEnabled))
        <div class="payment-proof-upload">
            <x-core::alert type="info" :icon="false" class="mb-0">
                <div class="w-100">
                    <div class="payment-proof-upload__header">
                        <div class="payment-proof-upload__icon">
                            <x-core::icon name="ti ti-receipt" />
                        </div>
                        <div class="payment-proof-upload__content">
                            <h6 class="payment-proof-upload__title">{{ __('Payment Proof') }}</h6>
                            <p class="payment-proof-upload__message">{{ __('You have uploaded a copy of your payment proof.') }}</p>
                            <div class="payment-proof-upload__file-preview">
                                <span class="payment-proof-upload__file-preview-label">{{ __('View Receipt:') }}</span>
                                <a href="{{ $downloadRoute }}" target="_blank" class="payment-proof-upload__file-preview-link">
                                    <x-core::icon name="ti ti-file" />
                                    {{ basename($order->proof_file) }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </x-core::alert>
        </div>
    @endif
@endif
