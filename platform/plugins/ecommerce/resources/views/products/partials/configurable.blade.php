@php
    $currencies = \Botble\Ecommerce\Models\Currency::query()->oldest('order')->get();
    $defaultCurrency = get_application_currency();
    $productCurrencyCode = old('currency_code', $product?->currency_code ?? $defaultCurrency->title);
    $selectedCurrency = $currencies->firstWhere('title', $productCurrencyCode) ?? $defaultCurrency;

    if (is_in_admin()) {
        $showCurrencySelector = EcommerceHelper::isEnabledProductCurrencySelection();
    } else {
        $showCurrencySelector = EcommerceHelper::isEnabledProductCurrencySelection()
            && \Botble\Marketplace\Facades\MarketplaceHelper::allowVendorManageProductCurrency();
    }
@endphp

@if ($showCurrencySelector)
    <div class="d-flex align-items-center gap-2 mt-3 mb-3 mx-3">
        <label for="currency_code" class="form-label mb-0 text-nowrap">{{ trans('plugins/ecommerce::products.form.currency') }}:</label>
        <select name="currency_code" id="currency_code" class="form-select form-select-sm product-currency-selector" style="width: auto; min-width: 120px;">
            @foreach($currencies as $currency)
                <option value="{{ $currency->title }}" data-symbol="{{ $currency->symbol }}" @selected($productCurrencyCode === $currency->title)>
                    {{ $currency->title }} ({{ $currency->symbol }})
                </option>
            @endforeach
        </select>
        <small class="text-muted d-none d-md-inline">{{ trans('plugins/ecommerce::products.form.currency_for_variations_hint') }}</small>
    </div>
@endif

<div id="product-variations-wrapper" data-clear-filters="{{ trans('plugins/ecommerce::products.clear_filters') }}">
    {!! $productVariationTable->renderTable() !!}
</div>

<x-core::modal
    id="select-attribute-sets-modal"
    :title="trans('plugins/ecommerce::products.select_attribute')"
>
    @include('plugins/ecommerce::products.partials.attribute-sets', compact('productAttributeSets'))

    <x-slot:footer>
        <x-core::button
            type="button"
            data-bs-dismiss="modal"
        >
            {{ trans('core/base::base.close') }}
        </x-core::button>

        <x-core::button
            type="button"
            color="primary"
            id="store-related-attributes-button"
            class="ms-auto"
        >
            {{ trans('plugins/ecommerce::products.save_changes') }}
        </x-core::button>
    </x-slot:footer>
</x-core::modal>

@push('footer')
    <x-core::modal
        id="add-new-product-variation-modal"
        :title="trans('plugins/ecommerce::products.add_new_variation')"
        size="xl"
    >
        <x-core::loading />
        <x-slot:footer>
            <x-core::button
                type="button"
                data-bs-dismiss="modal"
            >
                {{ trans('core/base::base.close') }}
            </x-core::button>

            <x-core::button
                type="button"
                color="primary"
                id="store-product-variation-button"
                class="ms-auto"
            >
                {{ trans('plugins/ecommerce::products.save_changes') }}
            </x-core::button>
        </x-slot:footer>
    </x-core::modal>

    <x-core::modal
        id="edit-product-variation-modal"
        :title="trans('plugins/ecommerce::products.edit_variation')"
        size="xl"
    >
        <x-core::loading />
        <x-slot:footer>
            <x-core::button
                type="button"
                data-bs-dismiss="modal"
            >
                {{ trans('core/base::base.close') }}
            </x-core::button>

            <x-core::button
                type="button"
                color="primary"
                id="update-product-variation-button"
                class="ms-auto"
            >
                {{ trans('plugins/ecommerce::products.save_changes') }}
            </x-core::button>
        </x-slot:footer>
    </x-core::modal>

    <x-core::modal
        id="generate-versions-modal"
        :title="trans('plugins/ecommerce::products.generate_variations')"
        :description="trans('plugins/ecommerce::products.generate_variations_confirmation')"
    >
        <x-core::form.label>
            {{ trans('plugins/ecommerce::products.select_attributes') }}
        </x-core::form.label>
        <div class="row row-cols-2">
            @foreach($productAttributeSets as $productAttributeSet)
                @continue(! $productAttributeSet->is_selected)
                <div class="col">
                    <h4 class="my-2">{{ $productAttributeSet->title }}</h4>

                    <fieldset class="form-fieldset">
                        <div data-bb-toggle="tree-checkboxes">
                            <ul class="list-unstyled">
                                <li>
                                    <x-core::form.checkbox
                                        :label="trans('plugins/ecommerce::products.all')"
                                        :checked="true"
                                    />

                                    <ul class="list-unstyled ms-2 mt-2">
                                        @foreach($productAttributeSet->attributes as $attribute)
                                            <li>
                                                <x-core::form.checkbox
                                                    :label="$attribute->title"
                                                    name="attributes[]"
                                                    value="{{ $attribute->id }}"
                                                    :checked="true"
                                                />
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </fieldset>
                </div>
            @endforeach
        </div>
        <x-slot:footer>
            <x-core::button
                type="button"
                data-bs-dismiss="modal"
            >
                {{ trans('core/base::base.close') }}
            </x-core::button>

            <x-core::button
                type="button"
                color="primary"
                data-bb-toggle="generate-versions-button"
                class="ms-auto"
            >
                {{ trans('plugins/ecommerce::products.continue') }}
            </x-core::button>
        </x-slot:footer>
    </x-core::modal>

    <x-core::modal.action
        id="confirm-delete-version-modal"
        type="danger"
        :title="trans('plugins/ecommerce::products.delete_variation')"
        :description="trans('plugins/ecommerce::products.delete_variation_confirmation')"
        :submit-button-attrs="['id' => 'delete-version-button']"
        :submit-button-label="trans('plugins/ecommerce::products.continue')"
    />

    <x-core::modal.action
        id="delete-variations-modal"
        type="danger"
        :title="trans('plugins/ecommerce::products.delete_variations')"
        :description="trans('plugins/ecommerce::products.delete_variations_confirmation')"
        :submit-button-attrs="['id' => 'delete-selected-variations-button']"
        :submit-button-label="trans('plugins/ecommerce::products.continue')"
    />
@endpush
