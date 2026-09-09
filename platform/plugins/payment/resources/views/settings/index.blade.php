@php
    use Botble\Payment\Enums\PaymentMethodEnum;
    use Botble\Payment\Models\Payment;
    use Botble\Payment\Supports\PaymentHelper;

    $defaultPaymentMethod = PaymentHelper::defaultPaymentMethod();
@endphp

@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        do_action(BASE_ACTION_META_BOXES, 'top', new Payment);
    @endphp

    <div class="my-5 d-block d-md-flex">
        <div class="col-12 col-md-3">
            <h2>{{ trans('plugins/payment::payment.payment_methods') }}</h2>
            <p class="text-muted">{{ trans('plugins/payment::payment.payment_methods_description') }}</p>
            <ul class="text-muted small ps-3">
                <li>{{ trans('plugins/payment::payment.sort_order_instruction') }}</li>
                <li>{{ trans('plugins/payment::payment.default_method_instruction') }}</li>
            </ul>
        </div>
        <div class="col-12 col-md-9">
            <div id="payment-methods-sortable" data-sort-order-url="{{ route('payments.methods.sort-order') }}">
                {!! apply_filters(PAYMENT_METHODS_SETTINGS_PAGE, null) !!}

                @php $codIsDefault = $defaultPaymentMethod === PaymentMethodEnum::COD; @endphp
                <x-core::card class="mb-3 payment-method-item" data-payment-type="cod">
                    <x-core::table :hover="false" :striped="false">
                        @php
                            $codStatus = get_payment_setting('status', PaymentMethodEnum::COD);
                        @endphp
                        <x-core::table.body>
                            <x-core::table.body.row>
                                <x-core::table.body.cell class="border-end drag-handle" style="width: 40px; cursor: grab;">
                                    <x-core::icon name="ti ti-grip-vertical" />
                                </x-core::table.body.cell>
                                <x-core::table.body.cell class="border-end" style="width: 40px;">
                                    <button type="button" class="btn btn-icon btn-sm set-default-payment-method @if($codIsDefault) text-warning @else text-muted @endif" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('plugins/payment::payment.default_payment_method') }}">
                                        <x-core::icon name="ti ti-star{{ $codIsDefault ? '-filled' : '' }}" />
                                    </button>
                                </x-core::table.body.cell>
                                <x-core::table.body.cell style="width: 20%">
                                    {{ trans('plugins/payment::payment.payment_methods') }}
                                </x-core::table.body.cell>
                                <x-core::table.body.cell>
                                    <p class="mb-0">{{ trans('plugins/payment::payment.payment_methods_instruction') }}</p>
                                </x-core::table.body.cell>
                            </x-core::table.body.row>
                            <x-core::table.body.row>
                                <x-core::table.body.cell colspan="4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="payment-name-label-group">
                                                @if($codStatus)
                                                    {{ trans('plugins/payment::payment.use') }}
                                                @endif
                                                <span class="method-name-label">{{ get_payment_setting('name', PaymentMethodEnum::COD, PaymentMethodEnum::COD()->label()) }}</span>
                                            </div>
                                        </div>

                                        <x-core::button @class(['toggle-payment-item edit-payment-item-btn-trigger', 'hidden' => !$codStatus])>
                                            {{ trans('plugins/payment::payment.edit') }}
                                        </x-core::button>
                                        <x-core::button @class(['toggle-payment-item save-payment-item-btn-trigger', 'hidden' => $codStatus])>
                                            {{ trans('plugins/payment::payment.settings') }}
                                        </x-core::button>
                                    </div>
                                </x-core::table.body.cell>
                            </x-core::table.body.row>
                            <x-core::table.body.row class="payment-content-item hidden">
                                <x-core::table.body.cell colspan="4">
                                    <x-core::form>
                                        {!! $codForm->renderForm() !!}

                                        <div class="btn-list justify-content-end">
                                            <x-core::button
                                                type="button"
                                                @class(['disable-payment-item', 'hidden' => !$codStatus])
                                            >
                                                {{ trans('plugins/payment::payment.deactivate') }}
                                            </x-core::button>

                                            <x-core::button
                                                @class(['save-payment-item btn-text-trigger-save', 'hidden' => $codStatus])
                                                type="button"
                                                color="info"
                                            >
                                                {{ trans('plugins/payment::payment.activate') }}
                                            </x-core::button>
                                            <x-core::button
                                                type="button"
                                                color="info"
                                                @class(['save-payment-item btn-text-trigger-update', 'hidden' => !$codStatus])
                                            >
                                                {{ trans('plugins/payment::payment.update') }}
                                            </x-core::button>
                                        </div>
                                    </x-core::form>
                                </x-core::table.body.cell>
                            </x-core::table.body.row>
                        </x-core::table.body>
                    </x-core::table>
                </x-core::card>

                @php
                    $bankTransferStatus = setting('payment_bank_transfer_status');
                    $bankTransferIsDefault = $defaultPaymentMethod === PaymentMethodEnum::BANK_TRANSFER;
                @endphp
                <x-core::card class="mb-3 payment-method-item" data-payment-type="bank_transfer">
                    <x-core::table :hover="false" :striped="false">
                        <x-core::table.body>
                            <x-core::table.body.row>
                                <x-core::table.body.cell colspan="3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="drag-handle" style="cursor: grab;"><x-core::icon name="ti ti-grip-vertical" /></span>
                                            <button type="button" class="btn btn-icon btn-sm set-default-payment-method @if($bankTransferIsDefault) text-warning @else text-muted @endif" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('plugins/payment::payment.default_payment_method') }}">
                                                <x-core::icon name="ti ti-star{{ $bankTransferIsDefault ? '-filled' : '' }}" />
                                            </button>
                                            <div class="payment-name-label-group">
                                                @if($bankTransferStatus)
                                                    {{ trans('plugins/payment::payment.use') }}
                                                @endif
                                                <span class="method-name-label">{{ get_payment_setting('name', 'bank_transfer', PaymentMethodEnum::BANK_TRANSFER()->label()) }}</span>
                                            </div>
                                        </div>

                                        <x-core::button @class(['toggle-payment-item edit-payment-item-btn-trigger', 'hidden' => !$bankTransferStatus])>
                                            {{ trans('plugins/payment::payment.edit') }}
                                        </x-core::button>
                                        <x-core::button @class(['toggle-payment-item save-payment-item-btn-trigger', 'hidden' => $bankTransferStatus])>
                                            {{ trans('plugins/payment::payment.settings') }}
                                        </x-core::button>
                                    </div>
                                </x-core::table.body.cell>
                            </x-core::table.body.row>
                            <x-core::table.body.row class="payment-content-item hidden">
                                <x-core::table.body.cell colspan="3">
                                    <x-core::form>
                                        {!! $bankTransferForm->renderForm() !!}

                                        <div class="btn-list justify-content-end">
                                            <x-core::button
                                                type="button"
                                                @class(['disable-payment-item', 'hidden' => !$bankTransferStatus])
                                            >
                                                {{ trans('plugins/payment::payment.deactivate') }}
                                            </x-core::button>

                                            <x-core::button
                                                @class(['save-payment-item btn-text-trigger-save', 'hidden' => $bankTransferStatus])
                                                type="button"
                                                color="info"
                                            >
                                                {{ trans('plugins/payment::payment.activate') }}
                                            </x-core::button>
                                            <x-core::button
                                                type="button"
                                                color="info"
                                                @class(['save-payment-item btn-text-trigger-update', 'hidden' => !$bankTransferStatus])
                                            >
                                                {{ trans('plugins/payment::payment.update') }}
                                            </x-core::button>
                                        </div>
                                    </x-core::form>
                                </x-core::table.body.cell>
                            </x-core::table.body.row>
                        </x-core::table.body>
                    </x-core::table>
                </x-core::card>
            </div>
        </div>
    </div>

    @php
        do_action(BASE_ACTION_META_BOXES, 'main', new Payment);
    @endphp

    <div class="row">
        <div class="col-md-9 offset-col-md-3">
            @php
                do_action(BASE_ACTION_META_BOXES, 'advanced', new Payment);
            @endphp
        </div>
    </div>

    {!! apply_filters('payment_method_after_settings', null) !!}
@endsection

@push('footer')
    <x-core::modal.action
        id="confirm-disable-payment-method-modal"
        :title="trans('plugins/payment::payment.deactivate_payment_method')"
        :description="trans('plugins/payment::payment.deactivate_payment_method_description')"
        :submit-button-attrs="['id' => 'confirm-disable-payment-method-button']"
        :submit-button-label="trans('plugins/payment::payment.agree')"
    />
@endpush
