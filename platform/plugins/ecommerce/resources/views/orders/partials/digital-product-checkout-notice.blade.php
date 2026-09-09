@if(EcommerceHelper::isEnabledSupportDigitalProducts() && EcommerceHelper::countDigitalProducts($products) > 0)
    <div class="digital-product-notice">
        <h5 class="digital-product-notice-title">{{ trans('plugins/ecommerce::order.digital_product_checkout.title') }}</h5>

        <div class="digital-product-notice-card">
            <div class="d-flex align-items-start gap-3">
                <x-core::icon name="ti ti-cloud-download" class="fs-3 digital-product-notice-icon" />
                <div>
                    <p class="mb-2">
                        {{ trans('plugins/ecommerce::order.digital_product_checkout.message') }}
                    </p>
                    @php
                        $customerEmail = $sessionCheckoutData['email'] ?? auth('customer')->user()?->email;
                    @endphp
                    @if($customerEmail)
                        <p class="mb-0 text-muted">
                            <strong>{{ __('Email') }}:</strong> {{ $customerEmail }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
