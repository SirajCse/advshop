{{--
    Empty product slot on the compare page. Clicking opens the standalone
    product-picker modal (no Bootstrap dependency — see compare-picker-modal).
--}}
<button
    type="button"
    class="compare-add-slot"
    data-bb-toggle="compare-open-picker"
    aria-label="{{ trans('plugins/ecommerce::products.compare.add_another') }}"
>
    <span class="compare-add-slot-icon" aria-hidden="true">
        <x-core::icon name="ti ti-plus" />
    </span>
    <span class="compare-add-slot-label">
        {{ trans('plugins/ecommerce::products.compare.add_another') }}
    </span>
</button>
