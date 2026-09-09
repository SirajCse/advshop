@php
    $googleAdsConversionId = get_ecommerce_setting('google_ads_conversion_id');
    $gtmEnabled = get_ecommerce_setting('google_tag_manager_enabled', false);
    $shouldRenderPurchaseEvent = $googleAdsConversionId || $gtmEnabled;
@endphp

@if ($shouldRenderPurchaseEvent)
    <script>
        window.addEventListener('load', function() {
            window.dataLayer = window.dataLayer || [];

            @foreach ($orders as $order)
                @php
                    $orderProducts = $order->products;
                    $items = [];

                    $productIds = $orderProducts->pluck('product_id')->filter()->all();
                    if (!empty($productIds)) {
                        $productsWithRelations = \Botble\Ecommerce\Models\Product::query()
                            ->whereIn('id', $productIds)
                            ->with(['brand', 'categories', 'variationInfo.configurableProduct.brand', 'variationInfo.configurableProduct.categories'])
                            ->get()
                            ->keyBy('id');
                    } else {
                        $productsWithRelations = collect();
                    }

                    foreach ($orderProducts as $index => $orderProduct) {
                        $product = $productsWithRelations->get($orderProduct->product_id);
                        $originalProduct = $product?->original_product;

                        $item = [
                            'item_id' => $product?->sku ?: $orderProduct->product_id,
                            'item_name' => $orderProduct->product_name,
                            'price' => (float) $orderProduct->price,
                            'quantity' => (int) $orderProduct->qty,
                            'index' => $index,
                        ];

                        if ($originalProduct) {
                            if ($originalProduct->relationLoaded('brand') && $originalProduct->brand) {
                                $item['item_brand'] = $originalProduct->brand->name;
                            }

                            if ($originalProduct->relationLoaded('categories')) {
                                $categories = $originalProduct->categories;
                                if ($categories && $categories->isNotEmpty()) {
                                    foreach ($categories as $catIndex => $category) {
                                        $catKey = $catIndex === 0 ? 'item_category' : 'item_category' . ($catIndex + 1);
                                        $item[$catKey] = $category->name;
                                    }
                                }
                            }
                        }

                        $items[] = $item;
                    }
                @endphp

                window.dataLayer.push({ ecommerce: null });
                window.dataLayer.push({
                    event: 'purchase',
                    ecommerce: {
                        transaction_id: '{{ $order->code }}',
                        value: {{ number_format((float) $order->sub_total, 2, '.', '') }},
                        tax: {{ number_format((float) ($order->tax_amount ?? 0), 2, '.', '') }},
                        shipping: {{ number_format((float) ($order->shipping_amount ?? 0), 2, '.', '') }},
                        currency: '{{ get_application_currency()->title }}',
                        @if ($order->coupon_code)
                        coupon: '{{ $order->coupon_code }}',
                        @endif
                        items: @json($items)
                    }
                });

                @if ($googleAdsConversionId)
                    var conversionData{{ $loop->index }} = {
                        'send_to': '{{ $googleAdsConversionId }}',
                        'value': {{ number_format((float) $order->amount, 2, '.', '') }},
                        'currency': '{{ get_application_currency()->title }}',
                        'transaction_id': '{{ $order->code }}'
                    };

                    if (typeof gtag === 'function') {
                        gtag('event', 'conversion', conversionData{{ $loop->index }});
                    }
                @endif
            @endforeach
        });
    </script>
@endif
