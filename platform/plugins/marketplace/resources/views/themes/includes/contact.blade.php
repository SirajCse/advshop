@if (MarketplaceHelper::isEnabledMessagingSystem() && (! auth('customer')->check() || $store->id != auth('customer')->user()->store?->id))
    <div class="mb-4 row">
        <div class="col-md-6">
            <h3 class="fs-4">{{ trans('plugins/marketplace::store.email_store', ['store' => $store->name]) }}</h3>
            <p>{{ trans('plugins/marketplace::store.contact_warning_message') }}</p>
            {!! $contactForm->renderForm() !!}
        </div>
    </div>

    @include(MarketplaceHelper::viewPath('includes.contact-form-script'))
@endif
