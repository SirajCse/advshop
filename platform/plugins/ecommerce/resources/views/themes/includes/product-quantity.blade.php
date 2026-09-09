<div>
    <div data-bb-toggle="product-quantity" class="bb-product-quantity input-group w-auto flex-nowrap">
        <button data-bb-toggle="product-quantity-toggle" data-value="minus" class="btn btn-outline-secondary minus" type="button" title="{{ trans('plugins/ecommerce::ecommerce.minus') }}">
            <x-core::icon name="ti ti-minus"/>
        </button>
        <input type="number" data-bb-toggle="input" name="qty" value="{{ $product->minimum_order_quantity ?: 1 }}" class="form-control" min="1" step="1" max="{{ $product->maximum_order_quantity ?: ($product->with_storehouse_management ? $product->quantity : 1000) }}" title="{{ trans('plugins/ecommerce::products.quantity') }}">
        <button data-bb-toggle="product-quantity-toggle" data-value="plus" class="btn btn-outline-secondary plus" type="button" title="{{ trans('plugins/ecommerce::ecommerce.plus') }}">
            <x-core::icon name="ti ti-plus"/>
        </button>
    </div>
</div>
