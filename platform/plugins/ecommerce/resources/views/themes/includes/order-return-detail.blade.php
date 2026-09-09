@if ($orderReturn)
    <div class="customer-order-detail">
        <!-- Return Request Information Card -->
        <div class="bb-customer-card order-return-detail-card mb-4">
            <div class="bb-customer-card-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="bb-customer-card-title h5 mb-1">
                            {{ trans('plugins/ecommerce::ecommerce.return_request_details') }}
                        </h3>
                        <p class="text-muted small mb-0">
                            {{ trans('plugins/ecommerce::order.request_code', ['code' => $orderReturn->code]) }}
                        </p>
                    </div>
                    <div class="bb-customer-card-status">
                        {!! BaseHelper::clean($orderReturn->return_status->toHtml()) !!}
                    </div>
                </div>
            </div>

            <div class="bb-customer-card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="bb-customer-card-info">
                            <div class="info-item mb-3">
                                <span class="label">
                                    <x-core::icon name="ti ti-hash" class="me-1" />
                                    {{ trans('plugins/ecommerce::ecommerce.request_number') }}
                                </span>
                                <span class="value fw-semibold">{{ $orderReturn->code }}</span>
                            </div>

                            <div class="info-item mb-3">
                                <span class="label">
                                    <x-core::icon name="ti ti-shopping-cart" class="me-1" />
                                    {{ trans('plugins/ecommerce::order.original_order') }}
                                </span>
                                <span class="value">
                                    <a href="{{ route('customer.orders.view', $orderReturn->order_id) }}"
                                       class="text-decoration-none fw-semibold">
                                        {{ $orderReturn->order->code }}
                                    </a>
                                </span>
                            </div>

                            @if($orderReturn->latestHistory && $orderReturn->latestHistory->reason)
                                <div class="info-item mb-3">
                                    <span class="label">
                                        <x-core::icon name="ti ti-message-circle" class="me-1" />
                                        {{ trans('plugins/ecommerce::ecommerce.moderators_note') }}
                                    </span>
                                    <span class="value">{{ $orderReturn->latestHistory->reason }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="bb-customer-card-info">
                            <div class="info-item mb-3">
                                <span class="label">
                                    <x-core::icon name="ti ti-calendar" class="me-1" />
                                    {{ trans('plugins/ecommerce::ecommerce.request_date') }}
                                </span>
                                <span class="value">{{ $orderReturn->created_at->translatedFormat('M d, Y \a\t g:i A') }}</span>
                            </div>

                            @if($orderReturn->latestHistory)
                                <div class="info-item mb-3">
                                    <span class="label">
                                        <x-core::icon name="ti ti-clock" class="me-1" />
                                        {{ trans('plugins/ecommerce::ecommerce.last_update') }}
                                    </span>
                                    <span class="value">{{ $orderReturn->latestHistory->created_at->translatedFormat('M d, Y \a\t g:i A') }}</span>
                                </div>
                            @endif

                            @if (!EcommerceHelper::allowPartialReturn())
                                <div class="info-item mb-3">
                                    <span class="label">
                                        <x-core::icon name="ti ti-info-circle" class="me-1" />
                                        {{ trans('plugins/ecommerce::order.return_reason') }}
                                    </span>
                                    <span class="value">{{ $orderReturn->reason->label() }}</span>
                                </div>
                            @endif

                            {!! apply_filters('ecommerce_order_return_detail_info', '', $orderReturn) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Return Items Card -->
        <div class="bb-customer-card order-return-items-card">
            <div class="bb-customer-card-header">
                <h4 class="bb-customer-card-title h5 mb-0">
                    <x-core::icon name="ti ti-package" class="me-2" />
                    {{ trans('plugins/ecommerce::ecommerce.return_items') }}
                </h4>
            </div>

            <div class="bb-customer-card-body">
                <div class="bb-customer-card-list return-items-list mb-0">
                    @foreach ($orderReturn->items as $item)
                        @php
                            $orderProduct = $item->orderProduct;
                        @endphp
                        <div class="bb-customer-card return-item-card">
                            <div class="bb-customer-card-body">
                                <div class="d-flex align-items-start gap-3">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0">
                                        <div class="return-item-image">
                                            <img
                                                src="{{ RvMedia::getImageUrl($item->product_image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                alt="{{ $item->product_name }}"
                                                class="img-fluid rounded"
                                            >
                                        </div>
                                    </div>

                                    <!-- Product Details -->
                                    <div class="flex-grow-1">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="return-item-details">
                                                    <h6 class="return-item-name mb-2">{{ $item->product_name }}</h6>

                                                    @if ($orderProduct)
                                                        @if ($sku = Arr::get($orderProduct->options, 'sku'))
                                                            <div class="return-item-sku mb-1">
                                                                <small class="text-muted">
                                                                    <x-core::icon name="ti ti-barcode" class="me-1" />
                                                                    {{ trans('plugins/ecommerce::products.sku') }}: {{ $sku }}
                                                                </small>
                                                            </div>
                                                        @endif
                                                        @if ($attributes = Arr::get($orderProduct->options, 'attributes'))
                                                            <div class="return-item-attributes mb-2">
                                                                <small class="text-muted">{{ $attributes }}</small>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="return-item-info">
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <div class="info-item text-center">
                                                                <span class="label">
                                                                    <x-core::icon name="ti ti-package" class="me-1" />
                                                                    {{ trans('plugins/ecommerce::products.quantity') }}
                                                                </span>
                                                                <span class="value fw-semibold text-primary">{{ number_format($item->qty) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="info-item text-center">
                                                                <span class="label">
                                                                    <x-core::icon name="ti ti-currency-dollar" class="me-1" />
                                                                    {{ trans('plugins/ecommerce::order.refund_amount') }}
                                                                </span>
                                                                <span class="value fw-semibold text-success">{{ format_price($item->refund_amount) }}</span>
                                                            </div>
                                                        </div>
                                                        @if (EcommerceHelper::allowPartialReturn())
                                                            <div class="col-12">
                                                                <div class="info-item text-center">
                                                                    <span class="label">
                                                                        <x-core::icon name="ti ti-info-circle" class="me-1" />
                                                                        {{ trans('plugins/ecommerce::order.return_reason') }}
                                                                    </span>
                                                                    <span class="value">
                                                                        <span class="badge bg-warning">{{ $item->reason->label() }}</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($orderReturn->images && count($orderReturn->images))
            <!-- Return Images Card -->
            <div class="bb-customer-card order-return-images-card mt-4">
                <div class="bb-customer-card-header">
                    <h5 class="bb-customer-card-title h6 mb-0">
                        <x-core::icon name="ti ti-photo" class="me-1" />
                        {{ trans('plugins/ecommerce::order.return_images') }}
                        <span class="badge bg-secondary ms-1" style="font-size: 11px;">{{ count($orderReturn->images) }}</span>
                    </h5>
                </div>

                <div class="bb-customer-card-body pt-3">
                    <div class="return-images-gallery">
                        @foreach ($orderReturn->images as $index => $image)
                            <a
                                href="{{ RvMedia::getImageUrl($image) }}"
                                target="_blank"
                                class="return-image-item"
                                title="{{ trans('plugins/ecommerce::order.return_image') }} {{ $index + 1 }}"
                            >
                                <img
                                    src="{{ RvMedia::getImageUrl($image, 'thumb') }}"
                                    alt="{{ trans('plugins/ecommerce::order.return_image') }} {{ $index + 1 }}"
                                >
                                <span class="return-image-overlay">
                                    <x-core::icon name="ti ti-zoom-in" />
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <style>
                .return-images-gallery {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 12px;
                }
                .return-image-item {
                    position: relative;
                    width: 100px;
                    height: 100px;
                    border-radius: 8px;
                    overflow: hidden;
                    border: 2px solid #e5e7eb;
                    transition: all 0.2s ease;
                    display: block;
                }
                .return-image-item:hover {
                    border-color: var(--primary-color, #0d6efd);
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                }
                .return-image-item img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
                .return-image-overlay {
                    position: absolute;
                    inset: 0;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    opacity: 0;
                    transition: opacity 0.2s ease;
                }
                .return-image-overlay svg {
                    width: 24px;
                    height: 24px;
                    color: #fff;
                }
                .return-image-item:hover .return-image-overlay {
                    opacity: 1;
                }
                @media (max-width: 576px) {
                    .return-image-item {
                        width: 80px;
                        height: 80px;
                    }
                }
            </style>
        @endif
    </div>
@else
    <div class="bb-empty">
        <div class="text-center py-5">
            <div class="bb-empty-img mb-4">
                <div class="bg-light rounded-circle p-4 d-inline-flex">
                    <x-core::icon name="ti ti-alert-circle" class="text-danger" style="width: 48px; height: 48px;" />
                </div>
            </div>
            <div class="bb-empty-content">
                <h3 class="bb-empty-title h5 mb-2 text-danger">{{ trans('plugins/ecommerce::order.order_return_request_not_found') }}</h3>
                <p class="bb-empty-subtitle text-muted mb-4">
                    {{ trans('plugins/ecommerce::ecommerce.the_return_request_you_are_looking_for_does_not_ex') }}
                </p>
                <div class="bb-empty-action">
                    <a href="{{ route('customer.order_returns.index') }}" class="btn btn-primary">
                        <x-core::icon name="ti ti-arrow-left" class="me-1" />
                        {{ trans('plugins/ecommerce::ecommerce.back_to_return_requests') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
