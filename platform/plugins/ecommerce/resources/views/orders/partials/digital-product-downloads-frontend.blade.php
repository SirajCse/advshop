@php
    $digitalProducts = $order->products->where('product_type', 'digital');
@endphp

@if($digitalProducts->isNotEmpty())
    <div class="digital-product-notice">
        <h5 class="digital-product-notice-title">{{ trans('plugins/ecommerce::order.digital_product_downloads.title') }}</h5>

        <div class="digital-product-notice-card">
            <div class="d-flex align-items-start gap-3">
                <x-core::icon name="ti ti-cloud-download" class="fs-3 digital-product-notice-icon" />
                <div class="flex-grow-1">
                    @foreach($digitalProducts as $orderProduct)
                        <div @class(['digital-product-download-item', 'border-bottom pb-3 mb-3' => !$loop->last])>
                            <div class="d-flex align-items-start gap-3">
                                <img
                                    src="{{ RvMedia::getImageUrl($orderProduct->product_image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                    alt="{{ $orderProduct->product_name }}"
                                    width="60"
                                    class="rounded border"
                                >
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold">{{ $orderProduct->product_name }}</h6>

                                    @if ($sku = Arr::get($orderProduct->options, 'sku') ?: $orderProduct->sku)
                                        <div class="text-muted small mb-1">{{ trans('plugins/ecommerce::products.sku') }}: {{ $sku }}</div>
                                    @endif

                                    <div class="text-muted small mb-2">
                                        {{ trans('plugins/ecommerce::products.quantity') }}: {{ $orderProduct->qty }}
                                    </div>

                                    @if ($orderProduct->license_code)
                                        @php
                                            $licenseCodes = $orderProduct->license_codes_array;
                                            $hasMultipleCodes = count($licenseCodes) > 1;
                                        @endphp
                                        <div class="license-codes-box mb-3">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <x-core::icon name="ti ti-key" class="text-warning" />
                                                <span class="fw-semibold small">
                                                    {{ $hasMultipleCodes
                                                        ? trans('plugins/ecommerce::products.license_codes.codes') . ' (' . count($licenseCodes) . ')'
                                                        : trans('plugins/ecommerce::products.license_codes.code') }}:
                                                </span>
                                            </div>
                                            @if ($hasMultipleCodes)
                                                <div class="d-flex flex-column gap-1">
                                                    @foreach ($licenseCodes as $index => $code)
                                                        <div class="d-flex align-items-center small">
                                                            <span class="text-muted me-2">{{ $index + 1 }}.</span>
                                                            <code class="license-code">{{ $code }}</code>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <code class="license-code">{{ $licenseCodes[0] ?? $orderProduct->license_code }}</code>
                                            @endif
                                        </div>
                                    @endif

                                    @if($order->is_finished && ($orderProduct->product_file_internal_count || $orderProduct->product_file_external_count))
                                        <div class="d-flex flex-wrap gap-2">
                                            @if ($orderProduct->product_file_internal_count && $orderProduct->download_hash_url)
                                                <a
                                                    class="btn btn-sm download-btn"
                                                    href="{{ $orderProduct->download_hash_url }}"
                                                    target="_blank"
                                                >
                                                    <x-core::icon name="ti ti-download" />
                                                    {{ trans('plugins/ecommerce::products.download') }}@if($orderProduct->product_file_internal_count > 1) ({{ $orderProduct->product_file_internal_count }})@endif
                                                </a>
                                            @endif
                                            @if ($orderProduct->product_file_external_count && $orderProduct->download_external_url)
                                                <a
                                                    class="btn btn-sm download-btn-outline"
                                                    href="{{ $orderProduct->download_external_url }}"
                                                    target="_blank"
                                                >
                                                    <x-core::icon name="ti ti-external-link" />
                                                    {{ trans('plugins/ecommerce::products.external_link_downloads') }}@if($orderProduct->product_file_external_count > 1) ({{ $orderProduct->product_file_external_count }})@endif
                                                </a>
                                            @endif
                                        </div>
                                    @elseif (!$order->is_finished)
                                        <div class="text-muted small">
                                            <x-core::icon name="ti ti-info-circle" />
                                            {{ trans('plugins/ecommerce::products.download_available_when_completed') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
