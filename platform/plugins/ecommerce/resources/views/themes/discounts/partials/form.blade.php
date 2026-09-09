@if (empty($isMobile))
    {{-- Desktop: Coupon List Section --}}
    @if ($discounts->isNotEmpty())
        <div class="checkout__coupon-section">
            <div class="checkout__coupon-heading">
                <img width="32" height="32" src="{{ asset('vendor/core/plugins/ecommerce/images/coupon-code.gif') }}" alt="coupon code icon">
                {{ trans('plugins/ecommerce::discount.coupon_codes_count', ['count' => $discounts->count()]) }}
            </div>

            <div class="checkout__coupon-list">
                @foreach ($discounts as $discount)
                    <div
                        @class(['checkout__coupon-item', 'active' => session()->has('applied_coupon_code') && session()->get('applied_coupon_code') === $discount->code])
                    >
                        <div class="checkout__coupon-item-icon"></div>
                        <div class="checkout__coupon-item-content">
                            {!! apply_filters('checkout_discount_item_before', null, $discount) !!}

                            <div class="checkout__coupon-item-title">
                                @if ($discount->type_option !== 'shipping')
                                    <h4>{{ $discount->type_option == 'percentage' ? $discount->value . '%' : format_price($discount->value) }}</h4>
                                @endif

                                @if($discount->quantity > 0)
                                    <span class="checkout__coupon-item-count">
                                        ({{ trans('plugins/ecommerce::discount.left_quantity', ['left' => $discount->left_quantity]) }})
                                    </span>
                                @endif
                            </div>
                            <div class="checkout__coupon-item-description">
                                {!! BaseHelper::clean($discount->description ?: get_discount_description($discount)) !!}
                            </div>
                            <div class="checkout__coupon-item-code">
                                <span>{{ $discount->code }}</span>
                                @if (!session()->has('applied_coupon_code') || session()->get('applied_coupon_code') !== $discount->code)
                                    <button type="button" data-bb-toggle="apply-coupon-code" data-discount-code="{{ $discount->code }}">
                                        {{ trans('plugins/ecommerce::discount.apply') }}
                                    </button>
                                @else
                                    <button type="button" class="remove-coupon-code" data-url="{{ route('public.coupon.remove') }}">
                                        {{ trans('plugins/ecommerce::discount.remove') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Desktop: Manual Coupon Entry Section --}}
    <div
        class="checkout-discount-section"
        @if (session()->has('applied_coupon_code')) style="display: none;" @endif
    >
        <a class="btn-open-coupon-form" href="#">
            {{ trans('plugins/ecommerce::discount.you_have_coupon_code') }}
        </a>
    </div>
    <div
        class="coupon-wrapper mt-2"
        @if (!session()->has('applied_coupon_code')) style="display: none;" @endif
    >
        @if (!session()->has('applied_coupon_code'))
            @include(EcommerceHelper::viewPath('discounts.partials.apply-coupon'))
        @else
            @include(EcommerceHelper::viewPath('discounts.partials.remove-coupon'))
        @endif
    </div>
@endif

{{-- Mobile: Coupon Bottom Sheet --}}
@if (!empty($isMobile) && ($discounts->isNotEmpty() || true))
    <div class="offcanvas offcanvas-bottom mobile-coupon-sheet" id="mobile-coupon-sheet" tabindex="-1" aria-labelledby="mobile-coupon-sheet-label" data-coupon-apply-url="{{ route('public.coupon.apply') }}">
        <div class="mobile-coupon-sheet__drag-handle">
            <span></span>
        </div>
        <div class="offcanvas-header border-0 pb-0">
            <h5 class="offcanvas-title" id="mobile-coupon-sheet-label">
                <img width="24" height="24" src="{{ asset('vendor/core/plugins/ecommerce/images/coupon-code.gif') }}" alt="coupon code icon" class="me-2">
                {{ trans('plugins/ecommerce::discount.select_coupon') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ trans('plugins/ecommerce::discount.close') }}"></button>
        </div>
        <div class="offcanvas-body pt-2">
            {{-- Manual Coupon Entry --}}
            <div class="mobile-coupon-entry mb-3">
                @if (!session()->has('applied_coupon_code'))
                    <div class="mobile-coupon-entry__card">
                        <div class="mobile-coupon-entry__icon">
                            <x-core::icon name="ti ti-ticket" />
                        </div>
                        <div class="mobile-coupon-entry__input">
                            <input
                                type="text"
                                class="form-control mobile-coupon-input"
                                name="coupon_code"
                                placeholder="{{ trans('plugins/ecommerce::discount.enter_coupon_code') }}"
                                autocomplete="off"
                            >
                        </div>
                        <button
                            type="button"
                            class="btn btn-primary mobile-apply-coupon-btn"
                            data-url="{{ route('public.coupon.apply') }}"
                        >
                            {{ trans('plugins/ecommerce::discount.apply') }}
                        </button>
                    </div>
                @else
                    <div class="mobile-coupon-entry__applied">
                        <div class="mobile-coupon-entry__icon mobile-coupon-entry__icon--success">
                            <x-core::icon name="ti ti-check" />
                        </div>
                        <div class="mobile-coupon-entry__code">
                            <span class="badge">{{ session()->get('applied_coupon_code') }}</span>
                        </div>
                        <button
                            type="button"
                            class="btn btn-outline-danger btn-sm remove-coupon-code"
                            data-url="{{ route('public.coupon.remove') }}"
                        >
                            {{ trans('plugins/ecommerce::discount.remove') }}
                        </button>
                    </div>
                @endif
            </div>

            @if ($discounts->isNotEmpty())
                <div class="mobile-coupon-divider mb-3">
                    <span>{{ trans('plugins/ecommerce::discount.or_select_coupon') }}</span>
                </div>

                <div class="mobile-coupon-list">
                    @foreach ($discounts as $discount)
                        <div
                            @class([
                                'mobile-coupon-item',
                                'border',
                                'rounded',
                                'mb-3',
                                'p-3',
                                'position-relative',
                                'active' => session()->has('applied_coupon_code') && session()->get('applied_coupon_code') === $discount->code
                            ])
                            data-discount-code="{{ $discount->code }}"
                        >
                            @if (session()->has('applied_coupon_code') && session()->get('applied_coupon_code') === $discount->code)
                                <div class="position-absolute top-0 end-0 mt-2 me-2">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                        <x-core::icon name="ti ti-check" style="width: 14px; height: 14px;" />
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex align-items-start gap-3">
                                <div class="mobile-coupon-icon bg-primary bg-opacity-10 rounded p-2 flex-shrink-0">
                                    <x-core::icon name="ti ti-discount-2" class="text-primary" />
                                </div>
                                <div class="flex-grow-1">
                                    <div class="mobile-coupon-value mb-1">
                                        @if ($discount->type_option !== 'shipping')
                                            <h6 class="mb-0 fw-bold">
                                                {{ $discount->type_option == 'percentage' ? $discount->value . '%' : format_price($discount->value) }}
                                            </h6>
                                        @else
                                            <h6 class="mb-0 fw-bold">{{ trans('plugins/ecommerce::discount.free_shipping') }}</h6>
                                        @endif

                                        @if($discount->quantity > 0)
                                            <small>
                                                ({{ trans('plugins/ecommerce::discount.left_quantity', ['left' => $discount->left_quantity]) }})
                                            </small>
                                        @endif
                                    </div>

                                    <div class="mobile-coupon-description mb-2">
                                        <small>
                                            {!! BaseHelper::clean($discount->description ?: get_discount_description($discount)) !!}
                                        </small>
                                    </div>

                                    <div class="mobile-coupon-code d-flex align-items-center justify-content-between">
                                        <span class="badge">{{ $discount->code }}</span>
                                        @if (!session()->has('applied_coupon_code') || session()->get('applied_coupon_code') !== $discount->code)
                                            <button type="button" class="btn" data-bb-toggle="apply-coupon-code" data-discount-code="{{ $discount->code }}">
                                                {{ trans('plugins/ecommerce::discount.apply') }}
                                            </button>
                                        @else
                                            <button type="button" class="btn remove-coupon-code" data-url="{{ route('public.coupon.remove') }}">
                                                {{ trans('plugins/ecommerce::discount.remove') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="offcanvas-footer border-0 pt-0">
            <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="offcanvas">
                {{ trans('plugins/ecommerce::discount.close') }}
            </button>
        </div>
    </div>
@endif

<div class="clearfix"></div>
