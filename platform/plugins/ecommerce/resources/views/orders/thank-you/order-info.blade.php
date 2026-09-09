@php
    $orderProducts = apply_filters('ecommerce_thank_you_order_products', $order->products, $order);
@endphp

<div class="order-summary-header">
    <h3 class="order-summary-title">{{ trans('plugins/ecommerce::order.order_summary') }}</h3>
    <span class="order-number-badge">{{ $order->code }}</span>
</div>

<div class="order-products-list">
    @foreach ($orderProducts as $orderProduct)
        <div class="order-product-item">
            <div class="product-image-wrapper">
                <img
                    class="product-image"
                    src="{{ RvMedia::getImageUrl($orderProduct->product_image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                    alt="{{ $orderProduct->product_name }}"
                >
                <span class="product-quantity-badge">{{ $orderProduct->qty }}</span>
            </div>
            <div class="product-details">
                <p class="product-name">{!! BaseHelper::clean($orderProduct->product_name) !!}</p>
                @if ($sku = Arr::get($orderProduct->options, 'sku', $orderProduct->product?->sku))
                    <p class="product-sku">{{ trans('plugins/ecommerce::products.sku') }}: {{ $sku }}</p>
                @endif
                @if ($attributes = Arr::get($orderProduct->options, 'attributes', ''))
                    <p class="product-variant">{{ $attributes }}</p>
                @endif

                @if (!empty($orderProduct->product_options) && is_array($orderProduct->product_options))
                    <div class="product-options">
                        {!! render_product_options_html($orderProduct->product_options, $orderProduct->product?->front_sale_price ?? $orderProduct->price) !!}
                    </div>
                @endif

                @include(EcommerceHelper::viewPath('includes.cart-item-options-extras'), [
                    'options' => $orderProduct->options,
                ])

                @php
                    $bundleReference = Arr::get($orderProduct->options, 'extras.upsale_reference_product');
                    $bundleReferenceProduct = $bundleReference ? \Botble\Ecommerce\Models\Product::query()->where('slug', $bundleReference)->first() : null;
                @endphp
                @if ($bundleReference)
                    @include('plugins/ecommerce::themes.includes.cart-bundle-badge', [
                        'reference' => $bundleReference,
                        'referenceProduct' => $bundleReferenceProduct,
                    ])
                @endif

                {!! apply_filters('ecommerce_thank_you_order_product_item', null, $orderProduct, $order) !!}
            </div>
            <div class="product-price-col">
                @php($isOrderProductFree = $orderProduct->price == 0)
                <p class="product-price">{{ $isOrderProductFree ? trans('plugins/ecommerce::ecommerce.free') : format_price($orderProduct->price * $orderProduct->qty) }}</p>

                @if (EcommerceHelper::isTaxEnabled() && $orderProduct->tax_amount > 0 && count($order->products) > 1)
                    <p class="product-tax">
                        {{ trans('plugins/ecommerce::order.tax') }}: {{ format_price($orderProduct->tax_amount) }}
                        @if (EcommerceHelper::isDisplayCheckoutTaxInformation() && !empty($orderProduct->options['taxClasses']))
                            <span class="tax-detail">
                            (
                            @foreach ($orderProduct->options['taxClasses'] as $taxName => $taxRate)
                                {{ $taxName }} {{ $taxRate }}%@if (!$loop->last), @endif
                            @endforeach
                            )
                            </span>
                        @elseif (EcommerceHelper::isDisplayCheckoutTaxInformation() && !empty($orderProduct->options['taxRate']) && $orderProduct->options['taxRate'] > 0)
                            <span class="tax-detail">({{ $orderProduct->options['taxRate'] }}%)</span>
                        @endif
                    </p>
                @endif
            </div>
        </div>
    @endforeach
</div>

{!! apply_filters('ecommerce_thank_you_after_order_products', null, $order) !!}

@if ($order->description)
    <div class="order-note">
        <h4 class="order-note-title">
            <x-core::icon name="ti ti-note" class="note-icon" />
            {{ trans('plugins/ecommerce::order.order_note') }}
        </h4>
        <p class="order-note-text">{{ $order->description }}</p>
    </div>
@endif

@if (!empty($isShowTotalInfo))
    @include('plugins/ecommerce::orders.thank-you.total-info', compact('order'))
@endif
