<div class="col">
    <div class="bb-customer-card">
        <div class="bb-customer-card-header">
            <div class="bb-customer-card-title">
                <span class="value">{{ $address->name }}</span>
            </div>
            <div class="bb-customer-card-status">
                @if ($address->is_default)
                    <x-core::badge color="info">{{ trans('plugins/ecommerce::customer-dashboard.default') }}</x-core::badge>
                @endif
            </div>
        </div>
        <div class="bb-customer-card-body">
            <div class="bb-customer-card-info text-start">
                <div class="info-item align-items-start bb-customer-card-info-address">
                    <span class="label me-1"><x-core::icon name="ti ti-book" /></span>
                    <span class="value text-start">{{ $address->full_address }}</span>
                </div>
                <div class="info-item align-items-start">
                    <span class="label me-1"><x-core::icon name="ti ti-phone" /></span>
                    <span class="value text-start">{{ $address->phone }}</span>
                </div>
            </div>
        </div>
        <div class="bb-customer-card-footer">
            <a class="btn btn-sm btn-primary" href="{{ route('customer.address.edit', $address->id) }}">
                <x-core::icon name="ti ti-edit" class="me-1" />
                {{ trans('plugins/ecommerce::customer-dashboard.edit') }}
            </a>
            <x-core::form :url="route('customer.address.destroy', $address->id)" method="get" onsubmit="return confirm('{{ trans('plugins/ecommerce::customer-dashboard.are_you_sure_delete_address') }}')">
                <button type="submit" class="btn btn-sm btn-danger">
                    <x-core::icon name="ti ti-trash" class="me-1" />
                    {{ trans('plugins/ecommerce::customer-dashboard.remove') }}
                </button>
            </x-core::form>
        </div>
    </div>
</div>
