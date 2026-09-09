<div data-bb-toggle="product-quantity" class="bb-product-quantity input-group w-auto flex-nowrap">
    <button data-bb-toggle="product-quantity-toggle" data-value="minus" class="btn btn-outline-secondary minus" type="button" title="{{ trans('plugins/ecommerce::ecommerce.minus') }}">
        <x-core::icon name="ti ti-minus"/>
    </button>
    <input
        title="{{ trans('plugins/ecommerce::products.quantity') }}"
        data-bb-toggle="input"
        class="form-control"
        type="number"
        name="items[{{ $key ?? $cartItem->rowId }}][values][qty]"
        value="{{ $cartItem->qty }}"
        min="1"
        max="{{ $product->with_storehouse_management ? $product->quantity : 1000 }}"
    />
    <button data-bb-toggle="product-quantity-toggle" data-value="plus" class="btn btn-outline-secondary plus" type="button" title="{{ trans('plugins/ecommerce::ecommerce.plus') }}">
        <x-core::icon name="ti ti-plus"/>
    </button>
</div>

