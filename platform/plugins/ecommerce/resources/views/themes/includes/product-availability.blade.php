<div class="number-items-available">
    @if ($product->stock_status == \Botble\Ecommerce\Enums\StockStatusEnum::ON_BACKORDER)
        <p class="text-warning fw-medium mb-0">{{ trans('plugins/ecommerce::ecommerce.backorder_warning') }}</p>
    @elseif ($product->isOutOfStock())
        <span class="text-danger">{{ trans('plugins/ecommerce::ecommerce.out_of_stock') }}</span>
    @else
        @if (! $productVariation)
            <span class="text-danger">{{ trans('plugins/ecommerce::ecommerce.not_available') }}
        @else
            @if ($productVariation->stock_status == \Botble\Ecommerce\Enums\StockStatusEnum::ON_BACKORDER)
                <p class="text-warning fw-medium mb-0">{{ trans('plugins/ecommerce::ecommerce.backorder_warning') }}</p>
            @elseif ($productVariation->isOutOfStock())
                <span class="text-danger">{{ trans('plugins/ecommerce::ecommerce.out_of_stock') }}</span>
            @elseif (! $productVariation->with_storehouse_management || $productVariation->quantity < 1)
                <span class="text-success">{{ trans('plugins/ecommerce::ecommerce.available') }}</span>
            @elseif ($productVariation->quantity)
                <span class="text-success">
                    @if (EcommerceHelper::showNumberOfProductsInProductSingle())
                        @if ($productVariation->quantity !== 1)
                            {{ trans('plugins/ecommerce::products.number_products_available_plural', ['number' => $productVariation->quantity]) }}
                        @else
                            {{ trans('plugins/ecommerce::products.number_product_available_singular', ['number' => $productVariation->quantity]) }}
                        @endif
                    @else
                        {{ trans('plugins/ecommerce::ecommerce.in_stock') }}
                    @endif
                </span>
           @endif
       @endif
    @endif
</div>
