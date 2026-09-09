@if ($order)
    <div class="bb-order-detail-wrapper">
        <!-- Order Information Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="bb-order-info">
                    <div class="row">
                        <div @class(['col-12' => ! $order->address->name, 'col-md-6' => $order->address->name])>
                            <div class="bb-order-info-section">
                                <h5 class="bb-section-title mb-3">{{ trans('plugins/ecommerce::order.order_information') }}</h5>
                                <div class="bb-order-info-list">
                                    <div class="bb-order-info-item">
                                        <span class="label">{{ trans('plugins/ecommerce::order.order_number_1') }}:</span>
                                        <span class="value fw-bold">{{ $order->code }}</span>
                                    </div>
                                    <div class="bb-order-info-item">
                                        <span class="label">{{ trans('plugins/ecommerce::ecommerce.time') }}:</span>
                                        <span class="value">{{ $order->created_at->translatedFormat('d M Y H:i:s') }}</span>
                                    </div>
                                    <div class="bb-order-info-item">
                                        <span class="label">{{ trans('plugins/ecommerce::order.order_status') }}:</span>
                                        <span class="value">{!! BaseHelper::clean($order->status->toHtml()) !!}</span>
                                    </div>
                                    @if($order->cancellation_reason)
                                        <div class="bb-order-info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::order.cancellation_reason') }}:</span>
                                            <span class="value text-warning">{{ $order->cancellation_reason_message }}</span>
                                        </div>
                                    @endif
                                    @if (is_plugin_active('payment') && $order->payment->id)
                                        <div class="bb-order-info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::order.payment_method') }}:</span>
                                            <span class="value">{{ $order->payment->payment_channel->displayName() }}</span>
                                        </div>
                                        <div class="bb-order-info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::order.payment_status_1') }}:</span>
                                            <span class="value">{!! BaseHelper::clean($order->payment->status->toHtml()) !!}</span>
                                        </div>
                                    @endif
                                    @if ($order->description)
                                        <div class="bb-order-info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::ecommerce.note') }}:</span>
                                            <span class="value text-warning fst-italic">{{ $order->description }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if ($order->address->name)
                            <div class="col-md-6">
                                <div class="bb-order-address-section">
                                    <h5 class="bb-section-title mb-3">{{ trans('plugins/ecommerce::order.shipping_address') }}</h5>
                                    <div class="bb-order-info-list">
                                        <div class="bb-order-info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::ecommerce.full_name') }}:</span>
                                            <span class="value">{{ $order->address->name }}</span>
                                        </div>
                                        <div class="bb-order-info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::ecommerce.phone') }}:</span>
                                            <span class="value">{{ $order->address->phone }}</span>
                                        </div>
                                        <div class="bb-order-info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::ecommerce.address') }}:</span>
                                            <span class="value">{{ $order->address->full_address }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="bb-section-title mb-3">{{ trans('plugins/ecommerce::products.products') }}</h5>
                <div class="bb-order-products">
                    <div class="bb-order-product-cards mb-3">
                        @foreach ($order->products as $orderProduct)
                            @php
                                $product = get_products([
                                    'condition' => [
                                        'ec_products.id' => $orderProduct->product_id,
                                    ],
                                    'take' => 1,
                                    'select' => ['ec_products.id', 'ec_products.images', 'ec_products.name', 'ec_products.price', 'ec_products.sale_price', 'ec_products.sale_type', 'ec_products.start_date', 'ec_products.end_date', 'ec_products.sku', 'ec_products.is_variation', 'ec_products.status', 'ec_products.order', 'ec_products.created_at'],
                                ]);
                            @endphp
                            <div class="bb-order-product-card">
                                <div class="bb-order-product-card-content">
                                    <div class="bb-order-product-card-image">
                                        <img src="{{ RvMedia::getImageUrl($orderProduct->product_image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                            alt="{{ $orderProduct->product_name }}">
                                    </div>
                                    <div class="bb-order-product-card-details">
                                        <div class="bb-order-product-card-header">
                                            <div class="bb-order-product-card-name">
                                                @if ($product && $product->original_product?->url)
                                                    <a href="{{ $product->original_product->url }}">{!! BaseHelper::clean($orderProduct->product_name) !!}</a>
                                                @else
                                                    {!! BaseHelper::clean($orderProduct->product_name) !!}
                                                @endif
                                            </div>

                                            @if ($sku = Arr::get($orderProduct->options, 'sku'))
                                                <div class="bb-order-product-card-sku">
                                                    <span class="text-muted">{{ $sku }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="bb-order-product-card-meta">
                                            @if ($attributes = Arr::get($orderProduct->options, 'attributes'))
                                                <div class="bb-order-product-card-attributes">
                                                    <small>{{ $attributes }}</small>
                                                </div>
                                            @elseif ($product && $product->is_variation)
                                                <div class="bb-order-product-card-attributes">
                                                    <small>
                                                        @if ($attributes = get_product_attributes($product->getKey()))
                                                            @foreach ($attributes as $attribute)
                                                                {{ $attribute->attribute_set_title }}: {{ $attribute->title }}
                                                                @if (!$loop->last)
                                                                    ,
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </small>
                                                </div>
                                            @endif

                                            @include(
                                                EcommerceHelper::viewPath('includes.cart-item-options-extras'),
                                                ['options' => $orderProduct->options]
                                            )

                                            @if (!empty($orderProduct->product_options) && is_array($orderProduct->product_options))
                                                {!! render_product_options_html($orderProduct->product_options, $product?->front_sale_price ?? $orderProduct->price) !!}
                                            @endif

                                            @if ($orderProduct->license_code)
                                                @php
                                                    $licenseCodes = $orderProduct->license_codes_array;
                                                    $hasMultipleCodes = count($licenseCodes) > 1;
                                                @endphp
                                                <div class="bb-order-product-card-license-code mt-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <x-core::icon name="ti ti-key" class="text-primary" />
                                                        <span class="fw-semibold">
                                                            {{ $hasMultipleCodes
                                                                ? trans('plugins/ecommerce::products.license_codes.codes') . ' (' . count($licenseCodes) . ')'
                                                                : trans('plugins/ecommerce::products.license_codes.code') }}:
                                                        </span>
                                                    </div>
                                                    <div class="mt-1">
                                                        @if ($hasMultipleCodes)
                                                            <div class="d-flex flex-column gap-2">
                                                                @foreach ($licenseCodes as $index => $code)
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="text-muted me-2">{{ $index + 1 }}.</span>
                                                                        <code class="bg-light p-2 rounded d-inline-block">{{ $code }}</code>
                                                                        <button type="button"
                                                                                class="btn btn-sm btn-outline-secondary ms-2"
                                                                                onclick="navigator.clipboard.writeText('{{ $code }}'); this.innerHTML='<i class=\'ti ti-check\'></i> Copied!'; setTimeout(() => this.innerHTML='<i class=\'ti ti-copy\'></i> Copy', 2000)">
                                                                            <x-core::icon name="ti ti-copy" />
                                                                            {{ trans('plugins/ecommerce::ecommerce.copy') }}
                                                                        </button>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <code class="bg-light p-2 rounded d-inline-block">{{ $licenseCodes[0] ?? $orderProduct->license_code }}</code>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-secondary ms-2"
                                                                    onclick="navigator.clipboard.writeText('{{ $licenseCodes[0] ?? $orderProduct->license_code }}'); this.innerHTML='<i class=\'ti ti-check\'></i> Copied!'; setTimeout(() => this.innerHTML='<i class=\'ti ti-copy\'></i> Copy', 2000)">
                                                                <x-core::icon name="ti ti-copy" />
                                                                {{ trans('plugins/ecommerce::ecommerce.copy') }}
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            @if (is_plugin_active('marketplace') && ($product = $orderProduct->product) && $product->original_product->store?->id)
                                                <div class="bb-order-product-card-vendor">
                                                    <small>{{ trans('plugins/ecommerce::ecommerce.sold_by') }}: <a
                                                            href="{{ $product->original_product->store->url }}"
                                                            class="text-primary">{{ $product->original_product->store->name }}</a>
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="bb-order-product-card-info">
                                        <div class="bb-order-product-card-price">
                                            <div class="bb-order-product-card-price-item">
                                                <span class="label">{{ trans('plugins/ecommerce::products.price') }}:</span>
                                                <span class="value">{{ $orderProduct->amount_format }}</span>
                                            </div>
                                            <div class="bb-order-product-card-price-item">
                                                <span class="label">{{ trans('plugins/ecommerce::products.quantity') }}:</span>
                                                <span class="value">{{ $orderProduct->qty }}</span>
                                            </div>
                                            <div class="bb-order-product-card-price-item total">
                                                <span class="label">{{ trans('plugins/ecommerce::ecommerce.total') }}:</span>
                                                <span class="value">{{ $orderProduct->total_format }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="bb-order-totals">
                        @if (EcommerceHelper::isTaxEnabled() && (float)$order->tax_amount)
                            @php
                                $taxInfo = '';
                                if (EcommerceHelper::isDisplayCheckoutTaxInformation()) {
                                    $taxParts = [];
                                    foreach ($order->products as $op) {
                                        if ($op->tax_amount > 0 && !empty($op->options['taxClasses'])) {
                                            foreach ($op->options['taxClasses'] as $tn => $tr) {
                                                $taxParts[$tn . ' ' . $tr . '%'] = true;
                                            }
                                        }
                                    }
                                    if ($taxParts) {
                                        $taxInfo = ' <small>(' . implode(', ', array_keys($taxParts)) . ')</small>';
                                    }
                                }
                            @endphp
                            <div class="bb-order-total-item">
                                <span class="label">{{ trans('plugins/ecommerce::ecommerce.tax') }}:</span>
                                <span class="value">{!! BaseHelper::clean(format_price($order->tax_amount) . $taxInfo) !!}</span>
                            </div>
                        @endif

                        @if ((float)$order->discount_amount)
                            <div class="bb-order-total-item">
                                <span class="label">{{ trans('plugins/ecommerce::ecommerce.discount') }}:</span>
                                <span class="value">
                                    {{ format_price($order->discount_amount) }}
                                    @if ($order->discount_amount)
                                        @if ($order->coupon_code)
                                            <span class="small">
                                                ({!! BaseHelper::html(trans('plugins/ecommerce::ecommerce.coupon_code_with_code', ['code' => Html::tag('strong', $order->coupon_code)->toHtml()])) !!})
                                            </span>
                                        @elseif ($order->discount_description)
                                            <span class="small">({{ $order->discount_description }})</span>
                                        @endif
                                    @endif
                                </span>
                            </div>
                        @endif

                        @if ((float)$order->shipping_amount && EcommerceHelper::countDigitalProducts($order->products) != $order->products->count())
                            <div class="bb-order-total-item">
                                <span class="label">{{ trans('plugins/ecommerce::order.shipping_fee') }}:</span>
                                <span class="value">{{ format_price($order->shipping_amount) }}</span>
                            </div>
                        @endif

                        @if ((float) ($order->shipping_tax_amount ?? 0))
                            <div class="bb-order-total-item">
                                <span class="label">{{ trans('plugins/ecommerce::order.shipping_tax') }}:</span>
                                <span class="value">{{ format_price($order->shipping_tax_amount) }}</span>
                            </div>
                        @endif

                        @if ((float) $order->payment_fee)
                            <div class="bb-order-total-item">
                                <span class="label">{{ trans('plugins/payment::payment.payment_fee') }}:</span>
                                <span class="value">{{ format_price($order->payment_fee) }}</span>
                            </div>
                        @endif

                        <div class="bb-order-total-item grand-total">
                            <span class="label">{{ trans('plugins/ecommerce::ecommerce.total_amount') }}:</span>
                            <span class="value">{{ format_price($order->amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping Information Section -->
        @if (! EcommerceHelper::isDisabledPhysicalProduct() && $order->shipment->id)
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="bb-section-title mb-3">{{ trans('plugins/ecommerce::order.shipping_information') }}</h5>
                    <div class="bb-order-shipping">
                        <div class="bb-order-info-list">
                            <div class="bb-order-info-item">
                                <span class="label">{{ trans('plugins/ecommerce::order.shipping_status') }}:</span>
                                <span class="value">{!! BaseHelper::clean($order->shipment->status->toHtml()) !!}</span>
                            </div>

                            @if ($order->shipment->shipping_company_name)
                                <div class="bb-order-info-item">
                                    <span class="label">{{ trans('plugins/ecommerce::order.shipping_company_name') }}:</span>
                                    <span class="value">{{ $order->shipment->shipping_company_name }}</span>
                                </div>
                            @endif

                            @if ($order->shipment->tracking_id)
                                <div class="bb-order-info-item">
                                    <span class="label">{{ trans('plugins/ecommerce::order.tracking_id') }}:</span>
                                    <span class="value">{{ $order->shipment->tracking_id }}</span>
                                </div>
                            @endif

                            @if ($order->shipment->tracking_link)
                                <div class="bb-order-info-item">
                                    <span class="label">{{ trans('plugins/ecommerce::order.tracking_link') }}:</span>
                                    <span class="value">
                                        <a href="{{ $order->shipment->tracking_link }}" target="_blank">{{ $order->shipment->tracking_link }}</a>
                                    </span>
                                </div>
                            @endif

                            @if ($order->shipment->note)
                                <div class="bb-order-info-item">
                                    <span class="label">{{ trans('plugins/ecommerce::order.delivery_notes') }}:</span>
                                    <span class="value">{{ $order->shipment->note }}</span>
                                </div>
                            @endif

                            @if ($order->shipment->estimate_date_shipped)
                                <div class="bb-order-info-item">
                                    <span class="label">{{ trans('plugins/ecommerce::ecommerce.estimate_date_shipped') }}:</span>
                                    <span class="value">{{ $order->shipment->estimate_date_shipped }}</span>
                                </div>
                            @endif

                            @if ($order->shipment->date_shipped)
                                <div class="bb-order-info-item">
                                    <span class="label">{{ trans('plugins/ecommerce::ecommerce.date_shipped') }}:</span>
                                    <span class="value">{{ $order->shipment->date_shipped }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@elseif (request()->input('order_id') || request()->input('email'))
    <div role="alert" class="alert alert-danger mt-3">
        <div class="d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-triangle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M12 9v4"></path>
                <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"></path>
                <path d="M12 16h.01"></path>
            </svg>

            {{ trans('plugins/ecommerce::order.the_order_could_not_be_found_please_try_again_or_c') }}
        </div>
    </div>
@endif
