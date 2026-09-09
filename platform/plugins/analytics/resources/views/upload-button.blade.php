<x-core::button
    size="md"
    data-bb-toggle="analytics-trigger-upload-json"
    data-url="{{ route('analytics.settings.json') }}"
>
    <x-core::icon name="ti ti-upload" /> {{ trans('plugins/analytics::analytics.upload_service_account_json') }}
</x-core::button>
