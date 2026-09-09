<x-core::form.fieldset class="mb-3">
    <h4>{{ trans('plugins/paypal-payout::paypal-payout.payout_info') }}</h4>

    <x-core::datagrid>
        <x-core::datagrid.item>
            <x-slot:title>{{ trans('plugins/paypal-payout::paypal-payout.transaction_id') }}</x-slot:title>
            {{ $transactionId }}
        </x-core::datagrid.item>

        <x-core::datagrid.item>
            <x-slot:title>{{ trans('plugins/payment::payment.status') }}</x-slot:title>
            {{ $status }}
        </x-core::datagrid.item>

        <x-core::datagrid.item>
            <x-slot:title>{{ trans('plugins/payment::payment.amount') }}</x-slot:title>
            {{ $amount }}
        </x-core::datagrid.item>

        <x-core::datagrid.item>
            <x-slot:title>{{ trans('plugins/paypal-payout::paypal-payout.fee') }}</x-slot:title>
            {{ $fee }}
        </x-core::datagrid.item>

        <x-core::datagrid.item>
            <x-slot:title>{{ trans('plugins/payment::payment.created_at') }}</x-slot:title>
            {{ $createdAt }}
        </x-core::datagrid.item>

        <x-core::datagrid.item>
            <x-slot:title>{{ trans('plugins/paypal-payout::paypal-payout.completed_at') }}</x-slot:title>
            {{ $completedAt }}
        </x-core::datagrid.item>

        <x-core::datagrid.item>
            <x-slot:title>{{ trans('plugins/paypal-payout::paypal-payout.funding_source') }}</x-slot:title>
            {{ $fundingSource }}
        </x-core::datagrid.item>
    </x-core::datagrid>
</x-core::form.fieldset>
