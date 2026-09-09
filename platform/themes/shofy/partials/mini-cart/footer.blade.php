@if (Cart::instance('cart')->isNotEmpty() && Cart::instance('cart')->products()->count())
    <div class="cartmini__checkout">
        {!! apply_filters('ecommerce_cart_sidebar_before_checkout', null, Cart::instance('cart')->products()) !!}

        <div class="d-flex flex-column gap-2 cartmini__checkout-title mb-30">
            <div>
                <span class="cartmini__checkout-label">{{ __('Subtotal:') }}</span>
                <span>{{ format_price(Cart::instance('cart')->rawSubTotal()) }}</span>
            </div>
            {!! apply_filters('ecommerce_cart_sidebar_after_subtotal', null, Cart::instance('cart')->products()) !!}
            @if (EcommerceHelper::isTaxEnabled() && Cart::instance('cart')->rawTax() > 0)
                <div>
                    <span class="cartmini__checkout-label">{{ __('Tax:') }}</span>
                    <span>{{ format_price(Cart::instance('cart')->rawTax()) }}</span>
                </div>
            @endif
            @if (EcommerceHelper::isTaxEnabled())
                <div>
                    <span class="cartmini__checkout-label">{{ __('Total:') }}</span>
                    <span>{{ format_price(Cart::instance('cart')->rawTotal()) }}</span>
                </div>
            @endif
        </div>
        <div class="cartmini__checkout-btn">
            @if (session('tracked_start_checkout'))
                <a href="{{ route('public.checkout.information', session('tracked_start_checkout')) }}" class="mb-10 tp-btn w-100">
                    {{ __('Checkout') }}
                </a>
            @endif

            <a href="{{ route('public.cart') }}" class="tp-btn tp-btn-border w-100">
                {{ __('View Cart') }}
            </a>
        </div>

        {!! apply_filters('ecommerce_cart_sidebar_after_checkout', null, Cart::instance('cart')->products()) !!}
    </div>
@endif
