@if ($store->isOnVacation())
    <div class="bb-store-vacation-notice alert alert-warning d-flex gap-2 align-items-start" role="alert">
        <x-core::icon name="ti ti-beach" />
        <div>
            <strong>{{ trans('plugins/marketplace::store.forms.vacation_badge') }}</strong>
            <div class="bb-store-vacation-message">
                @if ($store->vacation_message)
                    {{ $store->vacation_message }}
                @else
                    {{ trans('plugins/marketplace::store.forms.vacation_default_notice', ['store' => $store->name]) }}
                @endif
            </div>
        </div>
    </div>
@endif
