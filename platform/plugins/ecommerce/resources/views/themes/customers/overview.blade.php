@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce::customer-dashboard.overview'))

@section('content')
    @php
        $customer = auth('customer')->user();
        EcommerceHelper::registerThemeAssets();
    @endphp

    {!! apply_filters('ecommerce_customer_dashboard_before_content', '', $customer) !!}

    <!-- Welcome Section -->
    <div class="bb-customer-profile-wrapper">
        <div class="bb-customer-profile">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="bb-customer-profile-avatar">
                        {{ RvMedia::image($customer->avatar_url, $customer->name, attributes: ['class' => 'bb-customer-profile-avatar-img', 'data-bb-value' => 'customer-avatar']) }}
                        <div class="bb-customer-profile-avatar-overlay">
                            <input type="file" id="customer-avatar" name="avatar" data-bb-toggle="change-customer-avatar" data-url="{{ route('customer.avatar') }}" />
                            <label for="customer-avatar" title="{{ trans('plugins/ecommerce::customer-dashboard.change_avatar') }}">
                                <x-core::icon name="ti ti-camera" />
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="bb-customer-profile-info text-md-center text-start">
                        <h2 class="h4 mb-2">
                            {!! BaseHelper::clean(trans('plugins/ecommerce::customer-dashboard.welcome_back', ['name' => $customer->name])) !!}
                        </h2>
                        <p class="text-muted mb-0">
                            {{ trans('plugins/ecommerce::customer-dashboard.manage_account_description') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 bg-primary bg-opacity-10">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-20 rounded-circle p-3 d-inline-flex mb-3">
                        <x-core::icon name="ti ti-shopping-bag" class="text-white" size="lg" />
                    </div>
                    <h5 class="card-title h6 mb-2">{{ trans('plugins/ecommerce::customer-dashboard.view_orders') }}</h5>
                    <p class="card-text text-muted small mb-3">{{ trans('plugins/ecommerce::customer-dashboard.track_orders_description') }}</p>
                    <a href="{{ route('customer.orders') }}" class="btn btn-primary btn-sm">
                        {{ trans('plugins/ecommerce::customer-dashboard.view_orders') }}
                        <x-core::icon name="ti ti-arrow-right" class="ms-1" />
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <div class="bg-success bg-opacity-20 rounded-circle p-3 d-inline-flex mb-3">
                        <x-core::icon name="ti ti-map-pin" class="text-white" size="lg" />
                    </div>
                    <h5 class="card-title h6 mb-2">{{ trans('plugins/ecommerce::customer-dashboard.manage_addresses') }}</h5>
                    <p class="card-text text-muted small mb-3">{{ trans('plugins/ecommerce::customer-dashboard.update_addresses_description') }}</p>
                    <a href="{{ route('customer.address') }}" class="btn btn-success btn-sm">
                        {{ trans('plugins/ecommerce::customer-dashboard.manage_addresses') }}
                        <x-core::icon name="ti ti-arrow-right" class="ms-1" />
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 bg-warning bg-opacity-10">
                <div class="card-body text-center">
                    <div class="bg-warning bg-opacity-20 rounded-circle p-3 d-inline-flex mb-3">
                        <x-core::icon name="ti ti-settings" class="text-white" size="lg" />
                    </div>
                    <h5 class="card-title h6 mb-2">{{ trans('plugins/ecommerce::customer-dashboard.account_settings') }}</h5>
                    <p class="card-text text-muted small mb-3">{{ trans('plugins/ecommerce::customer-dashboard.edit_profile_description') }}</p>
                    <a href="{{ route('customer.edit-account') }}" class="btn btn-warning btn-sm">
                        {{ trans('plugins/ecommerce::customer-dashboard.edit_account') }}
                        <x-core::icon name="ti ti-arrow-right" class="ms-1" />
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- First Order Prompt -->
    @if (! $customer->orders()->exists())
        <div class="card border-0 bg-info bg-opacity-10">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-auto">
                        <span class="bg-info rounded-circle p-3 d-inline-flex">
                            <x-core::icon name="ti ti-shopping-cart" class="text-white" size="lg" />
                        </span>
                    </div>
                    <div class="col">
                        <h5 class="card-title h6 mb-1">{{ trans('plugins/ecommerce::customer-dashboard.ready_to_start_shopping') }}</h5>
                        <p class="card-text text-muted small mb-0">
                            {{ trans('plugins/ecommerce::customer-dashboard.no_orders_browse_description') }}
                        </p>
                    </div>
                    <div class="col-12 col-md-auto">
                        <a href="{{ route('public.products') }}" class="btn btn-outline-info w-100 w-md-auto">
                            <x-core::icon name="ti ti-shopping-bag" class="me-1" />
                            {{ trans('plugins/ecommerce::customer-dashboard.browse_products') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {!! apply_filters('ecommerce_customer_dashboard_after_content', '', $customer) !!}
@endsection
