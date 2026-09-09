@if (\Botble\Ecommerce\Facades\EcommerceHelper::isEnabledRelatedProducts())
<x-core::card class="mb-3">
    <x-core::card.header>
        <x-core::card.title>
            {{ trans('plugins/ecommerce::products.related_products') }}
        </x-core::card.title>
    </x-core::card.header>

    <x-core::card.body>
        @php
            $products = $product ? $product->products : collect();
        @endphp
        <x-plugins-ecommerce::box-search-advanced
            type="product"
            :search-target="$dataUrl"
            :shown="$products->isNotEmpty()"
        >
            <input
                name="related_products"
                type="hidden"
                value="@if ($products->isNotEmpty()) {{ implode(',', $product->products()->allRelatedIds()->all()) }} @endif"
            />

            <x-slot:items>
                @include('plugins/ecommerce::products.partials.selected-products-list', [
                    'products' => $products,
                    'includeVariation' => false,
                ])
            </x-slot:items>
        </x-plugins-ecommerce::box-search-advanced>
    </x-core::card.body>
</x-core::card>
@endif

@if (\Botble\Ecommerce\Facades\EcommerceHelper::isEnabledUpSaleProducts())
<x-core::card class="mb-3">
    <x-core::card.header>
        <x-core::card.title>
            {{ trans('plugins/ecommerce::products.up_selling_products') }}
        </x-core::card.title>
    </x-core::card.header>

    <x-core::card.body>
        @php
            $upSaleProducts = $product ? $product->upSales : collect();
            $upSaleProductsList = $product ? $product->upSaleProducts->where('is_variation', 0) : collect();
        @endphp

        <x-plugins-ecommerce::box-search-advanced
            type="product"
            :search-target="$dataUrl"
            :shown="$upSaleProductsList->isNotEmpty()"
            template="selected-up-sell-product-list-template"
        >
            <x-slot:items>
                @include('plugins/ecommerce::products.partials.selected-up-sell-products', [
                    'products' => $upSaleProductsList,
                    'includeVariation' => false,
                ])
            </x-slot:items>
        </x-plugins-ecommerce::box-search-advanced>

        <p class="small text-muted mt-2 mb-2">
            <strong>{{ trans('plugins/ecommerce::products.up_sell_help.price') }}</strong>:
            {{ trans('plugins/ecommerce::products.up_sell_help.price_description') }}
        </p>
        <p class="small text-muted mb-0">
            <strong>{{ trans('plugins/ecommerce::products.up_sell_help.type') }}</strong>:
            {{ trans('plugins/ecommerce::products.up_sell_help.type_description') }}
        </p>
    </x-core::card.body>
</x-core::card>
@endif

@if (\Botble\Ecommerce\Facades\EcommerceHelper::isEnabledCrossSaleProducts())
<x-core::card class="mb-3">
    <x-core::card.header>
        <x-core::card.title>
            {{ trans('plugins/ecommerce::products.cross_selling_products') }}
        </x-core::card.title>
    </x-core::card.header>

    <x-core::card.body>
        @php
            $crossSaleProducts = $product ? $product->crossSales : collect();
            $products = $product ? $product->crossSaleProducts->where('is_variation', 0) : collect();
        @endphp

        <x-plugins-ecommerce::box-search-advanced
            type="product"
            :search-target="$dataUrl"
            :shown="$products->isNotEmpty()"
            template="selected-cross-sell-product-list-template"
        >
            <x-slot:items>
                @include('plugins/ecommerce::products.partials.selected-cross-sell-products', [
                    'products' => $products,
                    'includeVariation' => false,
                ])
            </x-slot:items>
        </x-plugins-ecommerce::box-search-advanced>

        <p class="small text-muted mt-2 mb-2">
            <strong>{{ trans('plugins/ecommerce::products.cross_sell_help.price') }}</strong>:
            {{ trans('plugins/ecommerce::products.cross_sell_help.price_description') }}
        </p>
        <p class="small text-muted mb-0">
            <strong>{{ trans('plugins/ecommerce::products.cross_sell_help.type') }}</strong>:
            {{ trans('plugins/ecommerce::products.cross_sell_help.type_description') }}
        </p>
    </x-core::card.body>
</x-core::card>
@endif

<x-core::custom-template id="selected_product_list_template">
    <div class="list-group-item">
        <div class="row align-items-center">
            <div class="col-auto">
                <span
                    class="avatar"
                    style="background-image: url('__image__')"
                ></span>
            </div>
            <div class="col text-truncate">
                <a href="__url__" class="text-body d-block text-truncate" target="_blank">__name__</a>
                <div class="text-secondary text-truncate">
                    __attributes__
                </div>
            </div>
            <div class="col-auto">
                <a
                    href="javascript:void(0)"
                    data-bb-toggle="product-delete-item"
                    data-bb-target="__id__"
                    class="text-decoration-none list-group-item-actions btn-trigger-remove-selected-product"
                    title="{{ trans('plugins/ecommerce::products.delete') }}"
                >
                    <x-core::icon name="ti ti-x" class="text-secondary" />
                </a>
            </div>
        </div>
    </div>
</x-core::custom-template>

<x-core::custom-template id="selected-cross-sell-product-list-template">
    <div class="list-group-item">
        <input
            type="hidden"
            name="cross_sale_products[__id__][id]"
            value="__id__"
        />
        <div class="row align-items-center">
            <div class="col-auto">
                <span
                    class="avatar"
                    style="background-image: url('__image__')"
                ></span>
            </div>
            <div class="col text-truncate">
                <a href="__url__" class="text-body d-block text-truncate" target="_blank">__name__</a>
                <div class="text-secondary text-truncate">
                    __attributes__
                </div>
            </div>
            <div class="col">
                <x-core::form.text-input
                    :label="trans('plugins/ecommerce::products.price')"
                    name="cross_sale_products[__id__][price]"
                />
            </div>
            <div class="col">
                <x-core::form.select
                    :label="trans('plugins/ecommerce::products.cross_sell_price_type.title')"
                    :options="\Botble\Ecommerce\Enums\CrossSellPriceType::labels()"
                    name="cross_sale_products[__id__][price_type]"
                />
            </div>
            <div class="col-auto">
                <a
                    href="javascript:void(0)"
                    data-bb-toggle="product-delete-item"
                    data-bb-target="__id__"
                    class="text-decoration-none list-group-item-actions btn-trigger-remove-selected-product"
                    title="{{ trans('plugins/ecommerce::products.delete') }}"
                >
                    <x-core::icon name="ti ti-x" class="text-secondary" />
                </a>
            </div>
        </div>
    </div>
</x-core::custom-template>

<x-core::custom-template id="selected-up-sell-product-list-template">
    <div class="list-group-item">
        <input
            type="hidden"
            name="up_sale_products[__id__][id]"
            value="__id__"
        />
        <div class="row align-items-center">
            <div class="col-auto">
                <span
                    class="avatar"
                    style="background-image: url('__image__')"
                ></span>
            </div>
            <div class="col text-truncate">
                <a href="__url__" class="text-body d-block text-truncate" target="_blank">__name__</a>
                <div class="text-secondary text-truncate">
                    __attributes__
                </div>
            </div>
            <div class="col">
                <x-core::form.text-input
                    :label="trans('plugins/ecommerce::products.price')"
                    name="up_sale_products[__id__][price]"
                />
            </div>
            <div class="col">
                <x-core::form.select
                    :label="trans('plugins/ecommerce::products.up_sell_price_type.title')"
                    :options="\Botble\Ecommerce\Enums\UpSellPriceType::labels()"
                    name="up_sale_products[__id__][price_type]"
                />
            </div>
            <div class="col-auto">
                <a
                    href="javascript:void(0)"
                    data-bb-toggle="product-delete-item"
                    data-bb-target="__id__"
                    class="text-decoration-none list-group-item-actions btn-trigger-remove-selected-product"
                    title="{{ trans('plugins/ecommerce::products.delete') }}"
                >
                    <x-core::icon name="ti ti-x" class="text-secondary" />
                </a>
            </div>
        </div>
    </div>
</x-core::custom-template>
