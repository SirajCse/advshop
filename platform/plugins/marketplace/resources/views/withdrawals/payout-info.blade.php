@php
    $fields = Botble\Marketplace\Enums\PayoutPaymentMethodsEnum::getFields($paymentChannel);
@endphp

@if($fields)
    <x-core::form.fieldset class="mb-3">
        <h4>{{ $title ?? trans('plugins/marketplace::withdrawal.receive_money_info') }}</h4>

        <x-core::datagrid>
            @foreach ($fields as $key => $field)
                @if (Arr::get($bankInfo, $key))
                    <x-core::datagrid.item>
                        <x-slot:title>{{ Arr::get($field, 'title') }}</x-slot:title>
                        {{ Arr::get($bankInfo, $key) }}
                    </x-core::datagrid.item>
                @endif
            @endforeach
        </x-core::datagrid>
    </x-core::form.fieldset>

    @isset($link)
        <p class="mb-3">{!! BaseHelper::clean(trans('plugins/marketplace::withdrawal.change_info_here', ['link' => $link])) !!}.</p>
    @endisset
@endif

{!! apply_filters('marketplace_withdrawal_payout_info', null) !!}

@if ($taxInfo && (Arr::get($taxInfo, 'business_name') || Arr::get($taxInfo, 'tax_id') || Arr::get($taxInfo, 'address')))
    <x-core::form.fieldset class="mb-3">
        <h4>{{ trans('plugins/marketplace::marketplace.tax_info') }}</h4>

        <x-core::datagrid>
            @if (Arr::get($taxInfo, 'business_name'))
                <x-core::datagrid.item>
                    <x-slot:title>{{ trans('plugins/marketplace::marketplace.business_name') }}</x-slot:title>
                    {{ Arr::get($taxInfo, 'business_name') }}
                </x-core::datagrid.item>
            @endif

            @if ($taxId = Arr::get($taxInfo, 'tax_id'))
                <x-core::datagrid.item>
                    <x-slot:title>{{ trans('plugins/marketplace::marketplace.tax_id') }}</x-slot:title>
                    {{ $taxId }}
                </x-core::datagrid.item>
            @endif

            @if ($address = Arr::get($taxInfo, 'address'))
                <x-core::datagrid.item>
                    <x-slot:title>{{ trans('plugins/marketplace::marketplace.address') }}</x-slot:title>
                    {{ $address }}
                </x-core::datagrid.item>
            @endif
        </x-core::datagrid>
    </x-core::form.fieldset>
@endif
