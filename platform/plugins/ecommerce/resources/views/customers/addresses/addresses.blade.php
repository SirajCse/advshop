<div id="address-histories">
    <x-core::table>
        <x-core::table.header>
            <x-core::table.header.cell>
                {{ trans('plugins/ecommerce::addresses.address') }}
            </x-core::table.header.cell>
            <x-core::table.header.cell class="text-end">
                {{ trans('plugins/ecommerce::addresses.action') }}
            </x-core::table.header.cell>
        </x-core::table.header>

        <x-core::table.body>
        @forelse ($addresses as $address)
            <x-core::table.body.row>
                <x-core::table.body.cell class="text-start">
                    <div>
                        <strong>{{ $address->name }}</strong>
                        @if ($address->is_default)
                            <span class="badge bg-blue-lt ms-1">{{ trans('plugins/ecommerce::customer.default') }}</span>
                        @endif
                    </div>
                    <div class="text-muted mt-1">{{ $address->full_address }}</div>
                    @if ($address->phone)
                        <div class="text-muted mt-1">
                            <x-core::icon name="ti ti-phone" /> {{ $address->phone }}
                        </div>
                    @endif
                </x-core::table.body.cell>
                <x-core::table.body.cell class="text-end">
                    <x-core::button
                        :title="trans('core/base::forms.edit')"
                        :data-section="route('customers.addresses.edit', $address->id)"
                        icon="ti ti-edit"
                        class="me-1 btn-trigger-edit-address"
                        color="primary"
                        size="sm"
                        :icon-only="true"
                    />

                    <x-core::button
                        :title="trans('plugins/ecommerce::ecommerce.forms.delete')"
                        :data-section="route('customers.addresses.destroy', $address->id)"
                        icon="ti ti-trash"
                        class="deleteDialog"
                        size="sm"
                        color="danger"
                        :icon-only="true"
                    />
                </x-core::table.body.cell>
            </x-core::table.body.row>
        @empty
            <x-core::table.body.row class="text-center text-muted">
                <x-core::table.body.cell colspan="2">
                    {{ trans('plugins/ecommerce::addresses.no_data') }}
                </x-core::table.body.cell>
            </x-core::table.body.row>
        @endforelse
        </x-core::table.body>
    </x-core::table>
</div>
