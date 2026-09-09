<div class="row cart-item">
    <div class="col-3">
        <div class="checkout-product-img-wrapper">
            <img
                class="item-thumb img-thumbnail img-rounded"
                src="{{ RvMedia::getImageUrl(Arr::get($cartItem->options, 'image'), 'thumb', default: RvMedia::getDefaultImage()) }}"
                alt="{{ $product->original_product->name }}"
            >
            <span class="checkout-quantity">{{ $cartItem->qty }}</span>
        </div>
    </div>
    <div class="col">

        {!! apply_filters('ecommerce_cart_before_item_content', null, $cartItem) !!}

        <p class="mb-0">
            {{ $product->original_product->name }}
            @if ($product->isOutOfStock())
                <span class="stock-status-label">({!! BaseHelper::clean($product->stock_status_html) !!})</span>
            @endif
        </p>
        @if($product->variation_attributes)
            <p class="mb-0">
                <small>{{ $product->variation_attributes }}</small>
            </p>
        @endif

        @if (get_ecommerce_setting('checkout_product_quantity_editable', true))
            <div
                class="ec-checkout-quantity"
                data-url="{{ route('public.cart.update') }}"
                data-row-id="{{ $cartItem->rowId }}"
            >
                <button type="button" class="ec-checkout-quantity-control ec-checkout-quantity-minus" data-bb-toggle="decrease-qty">
                    <x-core::icon name="ti ti-minus" />
                </button>
                <input
                    type="number"
                    name="items[{{ $key }}][values][qty]"
                    value="{{ $cartItem->qty }}"
                    min="1"
                    max="{{ $product->with_storehouse_management ? $product->quantity : 1000 }}"
                    data-bb-toggle="update-cart"
                    readonly
                />
                <button type="button" class="ec-checkout-quantity-control ec-checkout-quantity-plus" data-bb-toggle="increase-qty">
                    <x-core::icon name="ti ti-plus" />
                </button>
            </div>
        @endif

        @include(EcommerceHelper::viewPath('includes.cart-item-options-extras'), [
            'options' => $cartItem->options,
        ])

        @if (!empty($cartItem->options['options']))
            {!! render_product_options_html($cartItem->options['options'], $product->front_sale_price) !!}
        @endif

        @if (EcommerceHelper::isTaxEnabled() && EcommerceHelper::isDisplayItemTaxAtCheckout() && $cartItem->taxRate > 0 && $cartItem->taxTotal > 0 && ! $cartItem->options->get('price_includes_tax', false))
            <p class="mb-0 ec-checkout-item-tax">
                <small class="text-muted">
                    {{ __('Tax') }}: {{ format_price($cartItem->taxTotal) }}
                    @if (EcommerceHelper::isDisplayCheckoutTaxInformation() && $cartItem->options && $cartItem->options->taxClasses)
                        (
                        @foreach ($cartItem->options->taxClasses as $taxName => $taxRate)
                            {{ $taxName }} {{ $taxRate }}%@if (!$loop->last), @endif
                        @endforeach
                        )
                    @elseif (EcommerceHelper::isDisplayCheckoutTaxInformation() && $cartItem->taxRate > 0)
                        ({{ $cartItem->taxRate }}%)
                    @endif
                </small>
            </p>
        @endif

        {!! apply_filters('ecommerce_cart_after_item_content', null, $cartItem) !!}
    </div>
    @php
        $isCheckoutItemFree = $cartItem->price == 0;
    @endphp
    <div class="col-auto text-end">
        <p class="mb-1 ec-checkout-item-price">{{ $isCheckoutItemFree ? trans('plugins/ecommerce::ecommerce.free') : format_price($cartItem->price) }}</p>
        @if (! $isCheckoutItemFree && $cartItem->qty > 1)
            <p class="mb-0 ec-checkout-item-total">
                <small class="text-muted">{{ __('Total') }}: {{ format_price($cartItem->total) }}</small>
            </p>
        @endif
    </div>
</div>
