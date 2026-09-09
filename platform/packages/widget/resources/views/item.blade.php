@if (count($widgetAreas))
    @foreach ($widgetAreas as $item)
        @continue(!class_exists($item->widget_id))

        @php
            $widget = new $item->widget_id();
        @endphp

        <li
            data-id="{{ $widget->getId() }}"
            data-position="{{ $item->position }}"
            class="widget-item list-unstyled"
        >
            <div class="widget-item-card">
                <div class="widget-item-header widget-draggable-handler">
                    <div class="widget-item-drag">
                        <x-core::icon name="ti ti-grip-vertical" size="sm" />
                    </div>
                    <div class="widget-item-icon">
                        <x-core::icon name="ti ti-box" size="sm" />
                    </div>
                    <div class="widget-item-title">
                        <h5 class="mb-0">{{ $widget->getName() }}</h5>
                    </div>
                    <button
                        class="widget-item-toggle"
                        type="button"
                        aria-expanded="false"
                        aria-label="{{ trans('packages/widget::widget.toggle_widget') }}"
                    >
                        <x-core::icon name="ti ti-chevron-down" size="sm" />
                    </button>
                </div>
                <div class="widget-item-content widget-content">
                    <div class="widget-item-body">
                        <form method="post">
                            <input
                                name="id"
                                type="hidden"
                                value="{{ $widget->getId() }}"
                            >
                            {!! $widget->form($item->sidebar_id, $item->position) !!}
                            <div class="widget-item-actions">
                                <x-core::button
                                    type="button"
                                    :outlined="true"
                                    color="danger"
                                    size="sm"
                                    class="widget-control-delete"
                                >
                                    <x-core::icon
                                        name="ti ti-trash"
                                        size="sm"
                                        class="me-1"
                                    />
                                    {{ trans('packages/widget::widget.delete') }}
                                </x-core::button>

                                <x-core::button
                                    type="button"
                                    color="primary"
                                    size="sm"
                                    class="widget-save"
                                >
                                    <x-core::icon
                                        name="ti ti-device-floppy"
                                        size="sm"
                                        class="me-1"
                                    />
                                    {{ trans('core/base::forms.save_and_continue') }}
                                </x-core::button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </li>
    @endforeach
@else
    <li class="widget-dropzone list-unstyled">
        <div class="widget-dropzone-inner">
            <div class="widget-dropzone-icon">
                <x-core::icon name="ti ti-drag-drop-2" />
            </div>
            <p class="widget-dropzone-text">{{ trans('packages/widget::widget.drag_widget_to_sidebar') }}</p>
            <span class="widget-dropzone-hint">{{ trans('packages/widget::widget.or_click_add') }}</span>
        </div>
    </li>
@endif
