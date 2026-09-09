@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce::customer-dashboard.address_books'))

@section('content')
    {!! apply_filters('ecommerce_customer_addresses_before_content', '') !!}

    <div class="bb-customer-content-wrapper">
        @if($addresses->isNotEmpty())
            <div class="dashboard-address">
            <!-- Page Header -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
                <div>
                    <h2 class="h5 mb-1">{{ trans('plugins/ecommerce::customer-dashboard.your_addresses') }}</h2>
                    <p class="text-muted small mb-0">{{ trans('plugins/ecommerce::customer-dashboard.manage_shipping_billing_addresses') }}</p>
                </div>
            </div>

            <!-- Address Grid -->
            <div class="bb-customer-card-list">
                <div class="row row-cols-1 row-cols-lg-2 g-4">
                    @foreach ($addresses as $address)
                        @include(EcommerceHelper::viewPath('customers.address.item'), ['address' => $address])
                    @endforeach
                </div>
            </div>

            <!-- Add Address Prompt -->
            @if($addresses->count() < 5)
                <div class="card border-0 bg-light mt-4">
                    <div class="card-body text-center py-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                            <x-core::icon name="ti ti-map-pin-plus" class="text-primary" />
                        </div>
                        <h5 class="card-title h6 mb-2">{{ trans('plugins/ecommerce::customer-dashboard.need_another_address') }}</h5>
                        <p class="card-text text-muted small mb-3">
                            {{ trans('plugins/ecommerce::customer-dashboard.add_multiple_addresses_description') }}
                        </p>
                        <a href="{{ route('customer.address.create') }}" class="btn btn-outline-primary btn-sm">
                            <x-core::icon name="ti ti-plus" class="me-1" />
                            {{ trans('plugins/ecommerce::customer-dashboard.add_another_address') }}
                        </a>
                    </div>
                </div>
            @endif
            </div>
        @else
            @include(EcommerceHelper::viewPath('customers.partials.empty-state'), [
                'title' => trans('plugins/ecommerce::customer-dashboard.no_addresses_yet'),
                'subtitle' => trans('plugins/ecommerce::customer-dashboard.add_first_address_description'),
                'actionUrl' => route('customer.address.create'),
                'actionLabel' => trans('plugins/ecommerce::customer-dashboard.add_your_first_address'),
            ])
        @endif
    </div>

    {!! apply_filters('ecommerce_customer_addresses_after_content', '') !!}
@endsection
