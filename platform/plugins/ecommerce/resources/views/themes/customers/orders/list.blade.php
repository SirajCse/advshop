@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce::customer-dashboard.orders'))

@section('content')
    {!! apply_filters('ecommerce_customer_orders_before_content', '') !!}

    <div class="bb-customer-content-wrapper">
        @if($orders->isNotEmpty())
            <div class="customer-list-order">
                <div class="bb-customer-card-list order-cards">
                @foreach ($orders as $order)
                    <div class="bb-customer-card order-card">
                        <div class="bb-customer-card-header">
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div class="flex-grow-1">
                                    <h3 class="bb-customer-card-title mb-2">
                                        {{ trans('plugins/ecommerce::customer-dashboard.order_code', ['code' => $order->code]) }}
                                    </h3>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="bb-customer-card-status">
                                            {!! BaseHelper::clean($order->status->toHtml()) !!}
                                        </div>
                                        <span class="text-muted" style="font-size: 0.75rem;">•</span>
                                        <span class="text-muted" style="font-size: 0.75rem;">
                                            {{ $order->created_at->translatedFormat('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bb-customer-card-body">
                            <div class="bb-customer-card-info">
                                <div class="row g-3">
                                    <div class="col-6 col-sm-4">
                                        <div class="info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::customer-dashboard.total_amount') }}</span>
                                            <span class="value">{{ $order->amount_format }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <div class="info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::customer-dashboard.items') }}</span>
                                            <span class="value">{{ $order->products_count }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::customer-dashboard.payment') }}</span>
                                            <span class="value">
                                                @if(is_plugin_active('payment') && $order->payment->id && $order->payment->payment_channel->displayName())
                                                    {{ $order->payment->payment_channel->displayName() }}
                                                @else
                                                    {{ trans('plugins/ecommerce::customer-dashboard.n_a') }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bb-customer-card-footer">
                            <a
                                class="btn btn-primary btn-sm"
                                href="{{ route('customer.orders.view', $order->id) }}"
                            >
                                <x-core::icon name="ti ti-eye" />
                                <span>{{ trans('plugins/ecommerce::customer-dashboard.view_details') }}</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

                @if($orders->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {!! $orders->links() !!}
                    </div>
                @endif
            </div>
        @else
            @include(EcommerceHelper::viewPath('customers.partials.empty-state'), [
                'title' => trans('plugins/ecommerce::customer-dashboard.no_orders_yet'),
                'subtitle' => trans('plugins/ecommerce::customer-dashboard.not_placed_orders_yet'),
                'actionUrl' => route('public.products'),
                'actionLabel' => trans('plugins/ecommerce::customer-dashboard.start_shopping_now'),
            ])
        @endif
    </div>

    {!! apply_filters('ecommerce_customer_orders_after_content', '') !!}
@stop
