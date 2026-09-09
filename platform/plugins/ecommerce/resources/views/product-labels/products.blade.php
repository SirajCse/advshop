<x-plugins-ecommerce::box-search-advanced
    type="product"
    :placeholder="trans('plugins/ecommerce::products.search_products')"
    :shown="$products->isNotEmpty()"
    template="selected_product_label_template"
>
    <input
        name="label_products"
        type="hidden"
        value="@if ($productLabel->id) {{ implode(',', array_filter($productLabel->products()->allRelatedIds()->toArray())) }} @endif"
    />

    <x-slot:items>
        @foreach ($products as $product)
            <div class="list-group-item" data-product-id="{{ $product->id }}">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span
                            class="avatar"
                            style="background-image: url('{{ RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage()) }}')"
                        ></span>
                    </div>
                    <div class="col text-truncate">
                        <a href="{{ route('products.edit', $product->id) }}" class="text-body d-block text-truncate" target="_blank">
                            {{ $product->name }}
                        </a>
                        <div class="text-secondary text-truncate">
                            {{ format_price($product->display_sale_price ?: $product->display_price) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <a
                            href="javascript:void(0)"
                            class="text-decoration-none list-group-item-actions"
                            data-bb-toggle="product-delete-item"
                            data-bb-target="{{ $product->id }}"
                            title="{{ trans('plugins/ecommerce::products.delete') }}"
                        >
                            <x-core::icon name="ti ti-x" class="text-secondary" />
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </x-slot:items>
</x-plugins-ecommerce::box-search-advanced>

@push('footer')
<x-core::custom-template id="selected_product_label_template">
    <div class="list-group-item" data-product-id="__id__">
        <div class="row align-items-center">
            <div class="col-auto">
                <span
                    class="avatar"
                    style="background-image: url('__image__')"
                ></span>
            </div>
            <div class="col text-truncate">
                <a href="__url__" class="text-body d-block text-truncate" target="_blank">
                    __name__
                </a>
                <div class="text-secondary text-truncate">
                    __attributes__
                </div>
            </div>
            <div class="col-auto">
                <a
                    href="javascript:void(0)"
                    class="text-decoration-none list-group-item-actions"
                    data-bb-toggle="product-delete-item"
                    data-bb-target="__id__"
                    title="{{ trans('plugins/ecommerce::products.delete') }}"
                >
                    <x-core::icon name="ti ti-x" class="text-secondary" />
                </a>
            </div>
        </div>
    </div>
</x-core::custom-template>
@endpush
