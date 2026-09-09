@if ($data->canEditStatus() && !$data->transaction_id)
    <a
        class="btn btn-warning btn-payout-button"
        href="{{ route('paypal-payout.make', $data->id) }}"
    ><i class="fab fa-paypal"></i> {{ trans('plugins/paypal-payout::paypal-payout.process_payout') }}</a>
@elseif($data->transaction_id)
    <div
        id="payout-transaction-detail"
        data-url="{{ route('paypal-payout.retrieve', $data->transaction_id) }}"
    >
        <x-core::loading />
    </div>
@endif
