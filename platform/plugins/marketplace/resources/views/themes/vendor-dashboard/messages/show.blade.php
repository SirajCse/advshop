@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <x-core::card>
        <x-core::card.body>
            <x-core::datagrid>
                <x-core::datagrid.item>
                    <x-slot:title>{{ trans('plugins/marketplace::message.sent_at') }}</x-slot:title>
                    {{ BaseHelper::formatDateTime($message->created_at) }}
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>{{ trans('core/base::forms.name') }}</x-slot:title>
                    {{ $message->name }}
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>{{ trans('core/base::forms.email') }}</x-slot:title>
                    <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                </x-core::datagrid.item>
            </x-core::datagrid>

            <x-core::datagrid class="mt-3">
                <x-core::datagrid.item>
                    <x-slot:title>{{ trans('core/base::forms.content') }}</x-slot:title>
                    <div class="bg-body-tertiary rounded p-2">
                        {!! BaseHelper::clean(nl2br($message->content)) !!}
                    </div>
                </x-core::datagrid.item>
            </x-core::datagrid>
        </x-core::card.body>

        <x-core::card.footer>
            <x-core::button tag="a" :href="route('marketplace.vendor.messages.index')" icon="ti ti-arrow-left">
                {{ trans('plugins/marketplace::marketplace.forms.back') }}
            </x-core::button>
        </x-core::card.footer>
    </x-core::card>
@stop
