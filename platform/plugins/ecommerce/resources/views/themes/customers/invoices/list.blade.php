@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce::invoice.name'))

@section('content')
    <div class="bb-customer-content-wrapper">
        @if($invoices->isNotEmpty())
            <div class="customer-list-invoice">
                <div class="bb-customer-card-list invoice-cards">
                @foreach ($invoices as $invoice)
                    <div class="bb-customer-card invoice-card">
                        <div class="bb-customer-card-header">
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div class="flex-grow-1">
                                    <h3 class="bb-customer-card-title mb-2">
                                        {{ trans('plugins/ecommerce::customer-dashboard.invoice_code', ['code' => $invoice->code]) }}
                                    </h3>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="bb-customer-card-status">
                                            {!! BaseHelper::clean($invoice->status->toHtml()) !!}
                                        </div>
                                        <span class="text-muted" style="font-size: 0.75rem;">•</span>
                                        <span class="text-muted" style="font-size: 0.75rem;">
                                            {{ $invoice->created_at->translatedFormat('M d, Y') }}
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
                                            <span class="value">{{ format_price($invoice->amount) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <div class="info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::customer-dashboard.items') }}</span>
                                            <span class="value">{{ $invoice->items_count }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::customer-dashboard.payment') }}</span>
                                            <span class="value">
                                                @if(is_plugin_active('payment') && $invoice->payment->id && $invoice->payment->payment_channel->displayName())
                                                    {{ $invoice->payment->payment_channel->displayName() }}
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
                                href="{{ route('customer.invoices.show', $invoice->id) }}"
                            >
                                <x-core::icon name="ti ti-eye" />
                                <span>{{ trans('plugins/ecommerce::customer-dashboard.view_details') }}</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

                @if($invoices->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {!! $invoices->links() !!}
                    </div>
                @endif
            </div>
        @else
            @include(EcommerceHelper::viewPath('customers.partials.empty-state'), [
                'title' => trans('plugins/ecommerce::customer-dashboard.no_invoices_yet'),
                'subtitle' => trans('plugins/ecommerce::customer-dashboard.no_invoices_description'),
                'actionUrl' => route('public.products'),
                'actionLabel' => trans('plugins/ecommerce::customer-dashboard.start_shopping_now'),
            ])
        @endif
    </div>
@stop
