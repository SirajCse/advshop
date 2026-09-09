@extends(EcommerceHelper::viewPath('customers.master'))

@section('content')
    <h3 class="alert-heading">{{ SeoHelper::getTitle() }}</h3>

    <div class="alert alert-warning mb-0 mt-3" role="alert">
        <p class="mb-0">{{ trans('plugins/marketplace::marketplace.wait_for_approval') }}</p>
    </div>

    <div class="mt-4">
        <h5 class="mb-3">{{ trans('plugins/marketplace::marketplace.vendor_information') }}</h5>
        <ul class="list-group">
            <li class="list-group-item"><strong>{{ trans('plugins/marketplace::store.store_name') }}:</strong> {{ $store->name }}</li>
            <li class="list-group-item"><strong>{{ trans('plugins/marketplace::store.forms.store_owner') }}:</strong> {{ $store->customer->name }}</li>
            <li class="list-group-item"><strong>{{ trans('plugins/marketplace::store.forms.phone') }}:</strong> {{ $store->phone }}</li>
            @if (MarketplaceHelper::getSetting('requires_vendor_documentations_verification', true))
                @if ($store->certificate_file && Storage::disk('local')->exists($store->certificate_file))
                    <li class="list-group-item">
                        <strong>{{ trans('plugins/marketplace::marketplace.uploaded_certificate') }}: </strong>
                        <a href="{{ route('marketplace.vendor.become-vendor.download-certificate') }}" target="_blank" class="text-primary">{{ trans('plugins/marketplace::marketplace.view_certificate') }}</a>
                    </li>
                @endif
                @if ($store->government_id_file && Storage::disk('local')->exists($store->government_id_file))
                    <li class="list-group-item">
                        <strong>{{ trans('plugins/marketplace::marketplace.uploaded_government_id') }}: </strong>
                        <a href="{{ route('marketplace.vendor.become-vendor.download-government-id') }}" target="_blank" class="text-primary">{{ trans('plugins/marketplace::marketplace.view_government_id') }}</a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
@endsection
