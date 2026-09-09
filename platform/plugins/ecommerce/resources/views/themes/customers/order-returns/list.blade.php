@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce::customer-dashboard.order_return_requests'))

@section('content')
    <div class="bb-customer-content-wrapper">
        @if($requests->isNotEmpty())
            <div class="customer-list-order">
                <!-- Return Requests Grid -->
                <div class="bb-customer-card-list order-return-cards">
                @foreach ($requests as $item)
                    <div class="bb-customer-card">
                        <!-- Return Request Header -->
                        <div class="bb-customer-card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="bb-customer-card-title h6 mb-1">
                                        {{ trans('plugins/ecommerce::customer-dashboard.return_request_code', ['code' => $item->code]) }}
                                    </h3>
                                    <p class="text-muted small mb-0">
                                        {{ $item->created_at->translatedFormat('M d, Y \a\t g:i A') }}
                                    </p>
                                    <div class="bb-customer-card-status">
                                        {!! BaseHelper::clean($item->return_status->toHtml()) !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Return Request Content -->
                        <div class="bb-customer-card-body">
                            <div class="bb-customer-card-info">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <span class="label">
                                                <x-core::icon name="ti ti-shopping-cart" class="me-1" />
                                                {{ trans('plugins/ecommerce::customer-dashboard.original_order') }}
                                            </span>
                                            <span class="value">
                                                <a
                                                    href="{{ route('customer.orders.view', $item->order_id) }}"
                                                    class="text-decoration-none fw-medium"
                                                    title="{{ trans('plugins/ecommerce::customer-dashboard.click_to_view_order_details') }}"
                                                >
                                                    {{ $item->order->code }}
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <span class="label">
                                                <x-core::icon name="ti ti-package" class="me-1" />
                                                {{ trans('plugins/ecommerce::customer-dashboard.items_count') }}
                                            </span>
                                            <span class="value">
                                                {{ $item->items_count === 1 ? trans('plugins/ecommerce::customer-dashboard.count_item', ['count' => $item->items_count]) : trans('plugins/ecommerce::customer-dashboard.count_items', ['count' => $item->items_count]) }}
                                            </span>
                                        </div>
                                    </div>

                                    {!! apply_filters('ecommerce_order_return_list_item_info', '', $item) !!}
                                </div>
                            </div>
                        </div>

                        <!-- Return Request Actions -->
                        <div class="bb-customer-card-footer">
                            <div class="d-flex justify-content-end">
                                <a
                                    class="btn btn-primary btn-sm"
                                    href="{{ route('customer.order_returns.detail', $item->id) }}"
                                >
                                    <x-core::icon name="ti ti-eye" class="me-1" />
                                    {{ trans('plugins/ecommerce::customer-dashboard.view_details') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>

                <!-- Pagination -->
                @if($requests->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {!! $requests->links() !!}
                    </div>
                @endif
            </div>
        @else
            @include(EcommerceHelper::viewPath('customers.partials.empty-state'), [
                'title' => trans('plugins/ecommerce::customer-dashboard.no_order_return_requests_yet'),
                'subtitle' => trans('plugins/ecommerce::customer-dashboard.no_order_return_requests_description'),
            ])
        @endif
    </div>
@stop
