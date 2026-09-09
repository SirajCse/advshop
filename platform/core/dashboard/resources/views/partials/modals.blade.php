<x-core::modal
    id="widgets-management-modal"
    data-bb-toggle="widgets-management-modal"
    :title="trans('core/dashboard::dashboard.manage_widgets')"
    :form-action="route('dashboard.hide_widgets')"
    size="lg"
>
    <p class="text-secondary mb-3">{{ trans('core/dashboard::dashboard.manage_widgets_description') }}</p>

    <div class="row g-2">
        @foreach ($widgets as $widget)
            @php
                $widgetId = "widgets[$widget->name]";
                $checked = !($widgetSetting = $widget->settings->first()) || $widgetSetting->status;
            @endphp

            <div class="col-12 col-sm-6">
                <label
                    for="{{ $widgetId }}"
                    @class([
                        'card card-sm mb-0 cursor-pointer',
                        'text-muted' => !$checked,
                    ])
                    data-bb-toggle="widgets-management-item-wrapper"
                >
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <span @class([
                            'avatar avatar-sm rounded',
                            'bg-primary-lt' => $checked,
                            'bg-secondary-lt' => !$checked,
                        ])>
                            <x-core::icon name="ti ti-layout-dashboard" />
                        </span>
                        <span class="flex-fill fw-medium">{{ $widget->title }}</span>
                        <x-core::form.toggle
                            :name="$widgetId"
                            :single="true"
                            :checked="$checked"
                            data-bb-toggle="widgets-management-item"
                        />
                    </div>
                </label>
            </div>
        @endforeach
    </div>

    <x-slot:footer>
        <x-core::button
            class="me-auto"
            data-bs-dismiss="modal"
        >
            {{ trans('core/base::forms.cancel') }}
        </x-core::button>

        <x-core::button
            type="submit"
            color="primary"
            icon="ti ti-device-floppy"
        >
            {{ trans('core/base::forms.save') }}
        </x-core::button>
    </x-slot:footer>
</x-core::modal>
