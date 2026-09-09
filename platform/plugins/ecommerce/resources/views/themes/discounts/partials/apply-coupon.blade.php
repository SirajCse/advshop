<div class="btn-group w-100">
    <input
        class="form-control coupon-code"
        name="coupon_code"
        type="text"
        value="{{ old('coupon_code') }}"
        placeholder="{{ trans('plugins/ecommerce::discount.enter_coupon_code_placeholder') }}"
    >
    <button
        class="apply-coupon-code d-flex align-items-center gap-2"
        data-url="{{ route('public.coupon.apply') }}"
        type="button"
    >
        {{ trans('plugins/ecommerce::discount.apply') }}
    </button>
</div>

<div class="coupon-error-msg mt-1">
    <span class="text-danger"></span>
</div>
