@if ($reference)
    @php
        $wrapperClass = apply_filters('ecommerce_cart_bundle_badge_wrapper_class', 'cart-bundle-badge mt-2');
        $badgeClass = apply_filters('ecommerce_cart_bundle_badge_class', 'badge');
    @endphp
    <div class="{{ $wrapperClass }}">
        <span
            class="{{ $badgeClass }}"
            style="background: #2fb344; color: #ffffff; display: inline-flex; align-items: center; max-width: 100%; font-size: 12px; font-weight: 500; padding: 4px 8px; border-radius: 4px; line-height: 1.4;"
            title="{{ trans('plugins/ecommerce::products.up_sale.bundle_discount_with', ['product' => $referenceProduct?->name ?? $reference]) }}"
        >
            <x-core::icon name="ti ti-discount-2" style="width: 14px; height: 14px; flex-shrink: 0;" class="me-1" />
            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ trans('plugins/ecommerce::products.up_sale.bundle_with', ['product' => Str::limit($referenceProduct?->name ?? $reference, 25)]) }}
            </span>
        </span>
    </div>
@endif
