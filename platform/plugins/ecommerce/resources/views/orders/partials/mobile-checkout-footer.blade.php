@php
    $checkoutOrderAmount = $orderAmount ?? Cart::instance('cart')->rawTotal();
    $isOrderFree = $checkoutOrderAmount == 0;
    $checkoutButtonText = $isOrderFree
        ? trans('plugins/ecommerce::ecommerce.complete_order')
        : trans('plugins/ecommerce::ecommerce.checkout');
    $showTerms = Theme::termAndPrivacyPolicyUrl() && get_ecommerce_setting('show_terms_and_policy_checkbox', true);
    $showAcceptanceMessage = get_ecommerce_setting('checkout_acceptance_message_enabled', false);
    $appliedCode = session()->get('applied_coupon_code');
    $appliedDiscount = isset($discounts) && $appliedCode ? $discounts->firstWhere('code', $appliedCode) : null;
    $hasCoupons = isset($discounts) && $discounts->isNotEmpty();
@endphp

<div class="mobile-checkout-footer">
    @if ($showAcceptanceMessage)
        <div class="mobile-checkout-footer__acceptance">
            {{ trans('plugins/ecommerce::ecommerce.checkout_acceptance_message') }}
        </div>
    @endif

    @if ($showTerms)
        <div class="mobile-checkout-footer__terms">
            <div class="form-check">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="agree_terms_and_policy"
                    id="agree_terms_and_policy"
                    value="1"
                    data-error-message="{{ trans('plugins/ecommerce::ecommerce.agree_terms_and_policy_error') }}"
                    {{ get_ecommerce_setting('terms_and_policy_checkbox_checked_by_default', false) ? 'checked' : '' }}
                >
                <label class="form-check-label" for="agree_terms_and_policy">
                    {!! BaseHelper::clean(__('I agree to the :link', ['link' => Html::link(Theme::termAndPrivacyPolicyUrl(), __('Terms and Privacy Policy'), attributes: ['target' => '_blank'])])) !!}
                </label>
            </div>
        </div>
    @endif

    <div class="mobile-checkout-footer__actions">
        <a href="{{ route('public.cart') }}" class="btn mobile-checkout-footer__btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
        </a>

        <button
            type="button"
            class="btn mobile-checkout-footer__btn-secondary mobile-checkout-footer__coupon-btn {{ $appliedDiscount ? 'has-coupon' : '' }}"
            data-bs-toggle="offcanvas"
            data-bs-target="#mobile-coupon-sheet"
        >
            @if ($appliedDiscount)
                <x-core::icon name="ti ti-discount-2" />
                <x-core::icon name="ti ti-check" class="coupon-applied-icon" />
            @elseif ($hasCoupons)
                <img width="20" height="20" src="{{ asset('vendor/core/plugins/ecommerce/images/coupon-code.gif') }}" alt="">
                <span class="coupon-count">{{ $discounts->count() }}</span>
            @else
                <x-core::icon name="ti ti-ticket" />
            @endif
        </button>

        @if (EcommerceHelper::isValidToProcessCheckout())
            <button
                class="btn payment-checkout-btn payment-checkout-btn-step mobile-checkout-btn"
                data-processing-text="{{ __('Processing. Please wait...') }}"
                data-error-header="{{ __('Error') }}"
                type="submit"
            >
                {{ $checkoutButtonText }}
            </button>
        @else
            <span class="btn payment-checkout-btn-step mobile-checkout-btn disabled">
                {{ $checkoutButtonText }}
            </span>
        @endif
    </div>
</div>
