@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce::invoice.heading') . ' - ' . $invoice->code)

@section('content')
    <div class="bb-customer-content-wrapper">
        <div class="bb-invoice-detail-wrapper">
            <!-- Invoice Information Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="bb-order-info">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="bb-order-info-section">
                                    <h5 class="bb-section-title mb-3">{{ trans('plugins/ecommerce::invoice.heading') }}</h5>
                                    <div class="bb-order-info-list">
                                        <div class="bb-order-info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::invoice.detail.code') }}:</span>
                                            <span class="value fw-bold">{{ $invoice->code }}</span>
                                        </div>
                                        @if ($invoice->created_at)
                                            <div class="bb-order-info-item">
                                                <span class="label">{{ trans('plugins/ecommerce::invoice.detail.issue_at') }}:</span>
                                                <span class="value">{{ $invoice->created_at->translatedFormat('d M Y H:i:s') }}</span>
                                            </div>
                                        @endif
                                        <div class="bb-order-info-item">
                                            <span class="label">{{ trans('plugins/ecommerce::invoice.payment_status') }}:</span>
                                            <span class="value">{!! BaseHelper::clean($invoice->status->toHtml()) !!}</span>
                                        </div>
                                        @if (is_plugin_active('payment') && $invoice->payment->id && $invoice->payment->payment_channel->displayName())
                                            <div class="bb-order-info-item">
                                                <span class="label">{{ trans('plugins/ecommerce::invoice.payment_method') }}:</span>
                                                <span class="value">{{ $invoice->payment->payment_channel->displayName() }}</span>
                                            </div>
                                        @endif
                                        @if ($invoice->reference && $invoice->reference->code)
                                            <div class="bb-order-info-item">
                                                <span class="label">{{ trans('plugins/ecommerce::invoice.order') }}:</span>
                                                <span class="value">
                                                    <a href="{{ route('customer.orders.view', $invoice->reference->id) }}">{{ $invoice->reference->code }}</a>
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bb-order-address-section">
                                    <h5 class="bb-section-title mb-3">{{ trans('plugins/ecommerce::invoice.detail.invoice_to') }}</h5>
                                    <div class="bb-order-info-list">
                                        @if ($invoice->customer_name)
                                            <div class="bb-order-info-item">
                                                <span class="label">{{ trans('plugins/ecommerce::ecommerce.full_name') }}:</span>
                                                <span class="value">{{ $invoice->customer_name }}</span>
                                            </div>
                                        @endif
                                        @if ($invoice->customer_email)
                                            <div class="bb-order-info-item">
                                                <span class="label">{{ trans('plugins/ecommerce::ecommerce.email') }}:</span>
                                                <span class="value">{{ $invoice->customer_email }}</span>
                                            </div>
                                        @endif
                                        @if ($invoice->customer_phone)
                                            <div class="bb-order-info-item">
                                                <span class="label">{{ trans('plugins/ecommerce::ecommerce.phone') }}:</span>
                                                <span class="value">{{ $invoice->customer_phone }}</span>
                                            </div>
                                        @endif
                                        @if ($invoice->customer_address)
                                            <div class="bb-order-info-item">
                                                <span class="label">{{ trans('plugins/ecommerce::ecommerce.address') }}:</span>
                                                <span class="value">{{ $invoice->customer_address }}</span>
                                            </div>
                                        @endif
                                        @if ($invoice->customer_tax_id)
                                            <div class="bb-order-info-item">
                                                <span class="label">{{ trans('plugins/ecommerce::invoice.detail.tax_id') }}:</span>
                                                <span class="value">{{ $invoice->customer_tax_id }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Items Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="bb-section-title mb-3">{{ trans('plugins/ecommerce::invoice.detail.description') }}</h5>
                    <div class="bb-order-products">
                        <div class="bb-order-product-cards mb-3">
                            @foreach ($invoice->items as $item)
                                <div class="bb-order-product-card">
                                    <div class="bb-order-product-card-content">
                                        <div class="bb-order-product-card-details flex-grow-1">
                                            <div class="bb-order-product-card-header">
                                                <div class="bb-order-product-card-name">
                                                    {{ $item->name }}
                                                </div>
                                            </div>
                                            @if ($item->description)
                                                <div class="bb-order-product-card-meta">
                                                    <small class="text-muted">{{ $item->description }}</small>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="bb-order-product-card-info">
                                            <div class="bb-order-product-card-price">
                                                <div class="bb-order-product-card-price-item">
                                                    <span class="label">{{ trans('plugins/ecommerce::invoice.detail.quantity') }}:</span>
                                                    <span class="value">{{ $item->qty }}</span>
                                                </div>
                                                <div class="bb-order-product-card-price-item total">
                                                    <span class="label">{{ trans('plugins/ecommerce::invoice.detail.amount') }}:</span>
                                                    <span class="value">{{ format_price($item->amount) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="bb-order-totals">
                            <div class="bb-order-total-item">
                                <span class="label">{{ trans('plugins/ecommerce::invoice.detail.sub_total') }}:</span>
                                <span class="value">{{ format_price($invoice->sub_total) }}</span>
                            </div>

                            @if ((float) $invoice->tax_amount > 0)
                                <div class="bb-order-total-item">
                                    <span class="label">{{ trans('plugins/ecommerce::invoice.detail.tax') }}:</span>
                                    <span class="value">{{ format_price($invoice->tax_amount) }}</span>
                                </div>
                            @endif

                            @if ((float) $invoice->shipping_amount > 0)
                                <div class="bb-order-total-item">
                                    <span class="label">{{ trans('plugins/ecommerce::invoice.detail.shipping_fee') }}:</span>
                                    <span class="value">{{ format_price($invoice->shipping_amount) }}</span>
                                </div>
                            @endif

                            @if ((float) ($invoice->shipping_tax_amount ?? 0) > 0)
                                <div class="bb-order-total-item">
                                    <span class="label">{{ trans('plugins/ecommerce::order.shipping_tax') }}:</span>
                                    <span class="value">{{ format_price($invoice->shipping_tax_amount) }}</span>
                                </div>
                            @endif

                            @if ((float) ($invoice->payment_fee ?? 0) > 0)
                                <div class="bb-order-total-item">
                                    <span class="label">{{ trans('plugins/payment::payment.payment_fee') }}:</span>
                                    <span class="value">{{ format_price($invoice->payment_fee) }}</span>
                                </div>
                            @endif

                            @if ((float) $invoice->discount_amount > 0)
                                <div class="bb-order-total-item">
                                    <span class="label">{{ trans('plugins/ecommerce::invoice.detail.discount') }}:</span>
                                    <span class="value">-{{ format_price($invoice->discount_amount) }}</span>
                                </div>
                            @endif

                            <div class="bb-order-total-item grand-total">
                                <span class="label">{{ trans('plugins/ecommerce::invoice.detail.grand_total') }}:</span>
                                <span class="value">{{ format_price($invoice->amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Information Section -->
            @if ($invoice->company_name || $invoice->company_logo)
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="bb-section-title mb-3">{{ trans('plugins/ecommerce::invoice.detail.invoice_for') }}</h5>
                        <div class="d-flex align-items-center gap-3">
                            @if ($invoice->company_logo)
                                <img
                                    src="{{ RvMedia::getImageUrl($invoice->company_logo) }}"
                                    alt="{{ $invoice->company_name }}"
                                    style="max-height: 60px;"
                                    class="rounded"
                                >
                            @endif
                            @if ($invoice->company_name)
                                <div>
                                    <strong>{{ $invoice->company_name }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="bb-order-actions d-flex flex-wrap gap-2">
                <a
                    class="btn btn-primary"
                    href="{{ route('customer.invoices.generate_invoice', ['id' => $invoice->id, 'type' => 'print']) }}"
                    target="_blank"
                >
                    <x-core::icon name="ti ti-printer" />
                    {{ trans('plugins/ecommerce::invoice.print') }}
                </a>
                <a
                    class="btn btn-success"
                    href="{{ route('customer.invoices.generate_invoice', ['id' => $invoice->id, 'type' => 'download']) }}"
                    target="_blank"
                >
                    <x-core::icon name="ti ti-download" />
                    {{ trans('plugins/ecommerce::invoice.download') }}
                </a>
                <a
                    class="btn btn-secondary"
                    href="{{ route('customer.invoices.index') }}"
                >
                    <x-core::icon name="ti ti-arrow-left" />
                    {{ trans('plugins/ecommerce::invoice.back_to_invoices') }}
                </a>
            </div>
        </div>
    </div>
@endsection
