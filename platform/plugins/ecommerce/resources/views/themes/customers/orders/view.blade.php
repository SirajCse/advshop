@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce::customer-dashboard.order_information'))

@section('content')
    <div class="bb-customer-content-wrapper">
        <div class="customer-order-detail">
            @include(EcommerceHelper::viewPath('includes.order-tracking-detail'))

        @if ($order->isPaymentProofEnabled())
            @if ($order->canBeCanceled())
                <div class="bb-payment-proof-card">
                    <div class="bb-payment-proof-card-content">
                        <div class="bb-payment-proof-card-icon">
                            <x-core::icon name="ti ti-receipt" class="payment-icon" />
                        </div>
                        <div class="bb-payment-proof-card-details">
                            <h5 class="bb-payment-proof-card-title">{{ trans('plugins/ecommerce::customer-dashboard.payment_proof') }}</h5>
                            <x-core::form method="post" :files="true" class="customer-order-upload-receipt" :url="route('customer.orders.upload-proof', $order)">
                                <div class="bb-payment-proof-card-content-inner">
                                    <div class="bb-payment-proof-card-message">
                                        @if (! $order->proof_file)
                                            <p class="mb-3">{{ trans('plugins/ecommerce::customer-dashboard.payment_proof_upload_description') }}</p>
                                        @else
                                            <p class="mb-2">{{ trans('plugins/ecommerce::customer-dashboard.uploaded_payment_proof') }}</p>
                                            <div class="bb-payment-proof-card-file mb-3 bg-light">
                                                <span class="label">{{ trans('plugins/ecommerce::customer-dashboard.view_receipt') }}</span>
                                                <a href="{{ route('customer.orders.download-proof', $order) }}" target="_blank" class="value">
                                                    <x-core::icon name="ti ti-file" />
                                                    {{ $order->proof_file }}
                                                </a>
                                            </div>
                                            <p class="mb-3 fw-medium">{{ trans('plugins/ecommerce::customer-dashboard.upload_new_receipt_description') }}</p>
                                        @endif
                                    </div>
                                    <div class="bb-payment-proof-card-upload">
                                        <div class="bb-file-upload-wrapper">
                                            <input type="file" name="file" id="payment-proof-file" class="bb-file-input" accept=".jpg,.jpeg,.png,.pdf">
                                            <label for="payment-proof-file" class="bb-file-label">
                                                <div class="bb-file-icon">
                                                    <x-core::icon name="ti ti-cloud-upload" />
                                                </div>
                                                <div class="bb-file-text">
                                                    <span class="bb-file-placeholder">{{ trans('plugins/ecommerce::customer-dashboard.choose_file') }}</span>
                                                    <span class="bb-file-name"></span>
                                                </div>
                                                <span class="bb-file-button">{{ trans('plugins/ecommerce::customer-dashboard.browse') }}</span>
                                            </label>
                                            <button type="submit" class="btn btn-primary bb-upload-submit">
                                                <x-core::icon name="ti ti-upload" />
                                                {{ trans('plugins/ecommerce::customer-dashboard.upload') }}
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">{{ trans('plugins/ecommerce::customer-dashboard.upload_file_types_description') }}</small>
                                    </div>
                                </div>
                            </x-core::form>
                        </div>
                    </div>
                </div>
            @elseif ($order->proof_file)
                <div class="bb-payment-proof-card">
                    <div class="bb-payment-proof-card-content">
                        <div class="bb-payment-proof-card-icon">
                            <x-core::icon name="ti ti-receipt" class="payment-icon" />
                        </div>
                        <div class="bb-payment-proof-card-details">
                            <h5 class="bb-payment-proof-card-title">{{ trans('plugins/ecommerce::customer-dashboard.payment_proof') }}</h5>
                            <div class="bb-payment-proof-card-content-inner">
                                <div class="bb-payment-proof-card-message">
                                    <p class="mb-2">{{ trans('plugins/ecommerce::customer-dashboard.uploaded_payment_proof') }}</p>
                                    <div class="bb-payment-proof-card-file">
                                        <span class="label">{{ trans('plugins/ecommerce::customer-dashboard.view_receipt') }}</span>
                                        <a href="{{ route('customer.orders.download-proof', $order) }}" target="_blank" class="value">
                                            <x-core::icon name="ti ti-file" />
                                            {{ $order->proof_file }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        {!! apply_filters('ecommerce_customer_order_view_before_actions', null, $order) !!}

        <div class="bb-order-actions d-flex flex-wrap gap-2 mt-3">
            @if($order->shipment->can_confirm_delivery)
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmDeliveredModal" data-toggle="modal" data-target="#confirmDeliveredModal">
                    <x-core::icon name="ti ti-check" />
                    {{ trans('plugins/ecommerce::customer-dashboard.confirm_delivery') }}
                </button>
            @endif
            @if ($order->isInvoiceAvailable())
                <a class="btn btn-success" href="{{ route('customer.print-order', $order->id) }}?type=print" target="_blank">
                    <x-core::icon name="ti ti-printer" />
                    {{ trans('plugins/ecommerce::customer-dashboard.print_invoice') }}
                </a>
                <a class="btn btn-success" href="{{ route('customer.print-order', $order->id) }}">
                    <x-core::icon name="ti ti-download" />
                    {{ trans('plugins/ecommerce::customer-dashboard.download_invoice') }}
                </a>
            @endif
            @if ($order->canBeCanceled())
                <a class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal-cancel-order" data-toggle="modal" data-target="#modal-cancel-order">
                    <x-core::icon name="ti ti-x" />
                    {{ trans('plugins/ecommerce::customer-dashboard.cancel_order') }}
                </a>
            @endif
            @if ($order->canBeReturned())
                <a class="btn btn-danger" href="{{ route('customer.order_returns.request_view', $order->id) }}">
                    <x-core::icon name="ti ti-arrow-back-up" />
                    {{ trans('plugins/ecommerce::customer-dashboard.return_products') }}
                </a>
            @endif
        </div>
    </div>

    @if ($order->canBeCanceled())
        <div class="modal fade" id="modal-cancel-order" tabindex="-1" aria-labelledby="modalCancelOrderLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bb-modal-content">
                    <div class="modal-header align-items-start bb-modal-header">
                        <div>
                            <h4 class="modal-title fs-5 fw-bold" id="modalCancelOrderLabel">
                                <x-core::icon name="ti ti-alert-triangle" class="text-warning me-2" />
                                {{ trans('plugins/ecommerce::customer-dashboard.cancel_order_title') }}
                            </h4>
                            <p class="text-muted mb-0 mt-2">{{ trans('plugins/ecommerce::customer-dashboard.cancel_order_reason_description') }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body bb-modal-body">
                        {!! $cancelOrderForm->renderForm() !!}
                    </div>
                    <div class="modal-footer bb-modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <x-core::icon name="ti ti-x" />
                            {{ trans('plugins/ecommerce::customer-dashboard.close') }}
                        </button>
                        <button type="submit" class="btn btn-danger" form="cancel-order-form">
                            <x-core::icon name="ti ti-check" />
                            {{ trans('plugins/ecommerce::customer-dashboard.submit') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($order->shipment->can_confirm_delivery)
        <div class="modal fade" id="confirmDeliveredModal" tabindex="-1" aria-labelledby="confirmDeliveredModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bb-modal-content">
                    <button type="button" class="btn-close position-absolute" data-bs-dismiss="modal" aria-label="Close" style="top: 1rem; inset-inline-end: 1rem; z-index: 1000"></button>
                    <form action="{{ route('customer.orders.confirm-delivery', $order) }}" method="post" class="modal-body text-center bb-modal-body">
                        @csrf
                        <div class="bb-modal-icon-wrapper mb-3">
                            <div class="bb-modal-icon bg-success">
                                <x-core::icon name="ti ti-truck-delivery" class="text-white" style="width: 2rem; height: 2rem;" />
                            </div>
                        </div>
                        <h5 class="modal-title mb-2 fw-semibold" id="confirmDeliveredModalLabel">{{ trans('plugins/ecommerce::customer-dashboard.confirm_delivery') }}</h5>
                        <p class="mb-4 text-muted small">{{ trans('plugins/ecommerce::customer-dashboard.confirm_delivery_description') }}</p>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <x-core::icon name="ti ti-x" />
                                {{ trans('plugins/ecommerce::customer-dashboard.close') }}
                            </button>
                            <button type="submit" class="btn btn-success">
                                <x-core::icon name="ti ti-check" />
                                {{ trans('plugins/ecommerce::customer-dashboard.confirm') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@stop
