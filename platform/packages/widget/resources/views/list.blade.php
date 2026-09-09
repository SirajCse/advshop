@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        do_action(BASE_ACTION_TOP_FORM_CONTENT_NOTIFICATION, request(), WIDGET_MANAGER_MODULE_SCREEN_NAME);
    @endphp
    <div
        class="widget-main"
        id="wrap-widgets"
    >
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">{{ trans('packages/widget::widget.name') }}</div>
                    <h2 class="page-title">{{ trans('packages/widget::widget.manage_widgets') }}</h2>
                </div>
            </div>
        </div>

        @if ($isInheriting ?? false)
            <x-core::alert type="info" class="mb-4">
                {{ trans('packages/widget::widget.inheriting_from_default') }}
            </x-core::alert>
        @endif

        <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
            <div class="d-flex align-items-center">
                <span class="avatar avatar-sm bg-info-lt me-3">
                    <x-core::icon name="ti ti-bulb" class="text-info" />
                </span>
                <div>
                    <div class="text-body">{{ trans('packages/widget::widget.usage_instruction') }}</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-4 col-xl-4">
                <div class="card card-stacked h-100 widget-source-panel">
                    <div class="card-header">
                        <div class="d-flex align-items-center w-100">
                            <span class="avatar avatar-sm bg-primary-lt me-3">
                                <x-core::icon name="ti ti-puzzle" class="text-primary" />
                            </span>
                            <div>
                                <h3 class="card-title mb-0">{{ trans('packages/widget::widget.available') }}</h3>
                                <p class="text-muted small mb-0">{{ trans('packages/widget::widget.drag_or_click') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="widget-source-search px-3 py-2 border-bottom">
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <x-core::icon name="ti ti-search" size="sm" />
                                </span>
                                <input
                                    type="text"
                                    class="form-control form-control-sm"
                                    id="widget-search"
                                    placeholder="{{ trans('packages/widget::widget.search_widgets') }}"
                                    autocomplete="off"
                                >
                            </div>
                        </div>
                        <div
                            id="wrap-widget-1"
                            class="widget-source-list p-3"
                        >
                            @forelse (Widget::getWidgets() as $widget)
                                <li
                                    data-id="{{ $widget->getId() }}"
                                    data-name="{{ strtolower($widget->getName()) }}"
                                    class="widget-source-item list-unstyled"
                                >
                                    <form method="post">
                                        <input
                                            name="id"
                                            type="hidden"
                                            value="{{ $widget->getId() }}"
                                        >
                                        <div class="widget-source-card widget-draggable-handler">
                                            <div class="widget-source-drag">
                                                <x-core::icon name="ti ti-grip-vertical" size="sm" />
                                            </div>
                                            <div class="widget-source-icon">
                                                <x-core::icon name="ti ti-box" />
                                            </div>
                                            <div class="widget-source-info">
                                                <h5
                                                    class="widget-source-name"
                                                    title="{{ $name = $widget->getName() }}"
                                                >
                                                    {{ $name }}
                                                </h5>
                                                <p
                                                    class="widget-source-desc"
                                                    title="{{ $description = $widget->getDescription() }}"
                                                >
                                                    {{ $description }}
                                                </p>
                                            </div>
                                            <button
                                                type="button"
                                                class="widget-source-add widget-add-btn"
                                                data-widget-id="{{ $widget->getId() }}"
                                                title="{{ trans('packages/widget::widget.click_to_add') }}"
                                                aria-label="{{ trans('packages/widget::widget.click_to_add') }}"
                                            >
                                                <x-core::icon name="ti ti-plus" size="sm" />
                                            </button>
                                        </div>
                                    </form>
                                </li>
                            @empty
                                <div class="empty py-4">
                                    <div class="empty-icon">
                                        <x-core::icon name="ti ti-puzzle-off" />
                                    </div>
                                    <p class="empty-title">{{ trans('packages/widget::widget.no_widgets') }}</p>
                                </div>
                            @endforelse
                        </div>
                        <div id="widget-search-empty" class="empty py-4" style="display: none;">
                            <div class="empty-icon">
                                <x-core::icon name="ti ti-search-off" />
                            </div>
                            <p class="empty-title">{{ trans('packages/widget::widget.no_search_results') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="col-12 col-lg-8 col-xl-8"
                id="added-widget"
            >
                <div class="card widget-areas-panel">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-sm bg-azure-lt me-3">
                                <x-core::icon name="ti ti-layout-sidebar-right" class="text-azure" />
                            </span>
                            <div>
                                <h3 class="card-title mb-0">{{ trans('packages/widget::widget.widget_areas') }}</h3>
                                <p class="text-muted small mb-0">{{ trans('packages/widget::widget.drop_widgets_here') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        {!! apply_filters(WIDGET_TOP_META_BOXES, null, WIDGET_MANAGER_MODULE_SCREEN_NAME) !!}
                        <div class="row g-3">
                            @foreach (WidgetGroup::getGroups() as $group)
                                <div
                                    class="col-12 col-xl-6 sidebar-item"
                                    data-id="{{ $group->getId() }}"
                                >
                                    <div class="card widget-area-card h-100">
                                        <div class="card-header widget-area-header">
                                            <div class="d-flex align-items-center w-100">
                                                <span class="widget-area-icon">
                                                    <x-core::icon name="ti ti-layout-dashboard" size="sm" />
                                                </span>
                                                <div class="flex-grow-1">
                                                    <h4 class="card-title mb-0">{{ $group->getName() }}</h4>
                                                    @if ($group->getDescription())
                                                        <p class="text-muted small mb-0">{{ $group->getDescription() }}</p>
                                                    @endif
                                                </div>
                                                <span class="widget-area-count badge bg-secondary-lt">
                                                    {{ count($group->getWidgets()) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body widget-area-body">
                                            <ul
                                                id="wrap-widget-{{ $loop->index + 2 }}"
                                                class="widget-area-list list-unstyled mb-0"
                                            >
                                                @include('packages/widget::item', [
                                                    'widgetAreas' => $group->getWidgets(),
                                                ])
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-core::modal
        id="widget-add-modal"
        :title="trans('packages/widget::widget.select_sidebar')"
        size="md"
    >
        <input type="hidden" id="widget-add-modal-widget-id" value="">
        <input type="hidden" id="widget-add-modal-sidebar-id" value="">

        <div id="widget-add-step-1">
            <div class="d-flex align-items-center mb-3">
                <span class="avatar avatar-sm bg-primary-lt me-2">
                    <x-core::icon name="ti ti-layout-sidebar-right" class="text-primary" />
                </span>
                <div>
                    <p class="text-muted mb-0">{{ trans('packages/widget::widget.select_sidebar_description') }}</p>
                </div>
            </div>

            <div class="list-group widget-sidebar-list">
                @foreach (WidgetGroup::getGroups() as $group)
                    <button
                        type="button"
                        class="list-group-item list-group-item-action widget-sidebar-option"
                        data-sidebar-id="{{ $group->getId() }}"
                        data-sidebar-name="{{ $group->getName() }}"
                    >
                        <div class="d-flex align-items-center">
                            <span class="widget-sidebar-option-icon">
                                <x-core::icon name="ti ti-layout-dashboard" size="sm" />
                            </span>
                            <div class="flex-grow-1 text-start">
                                <div class="fw-medium">{{ $group->getName() }}</div>
                                @if ($group->getDescription())
                                    <small class="text-muted">{{ $group->getDescription() }}</small>
                                @endif
                            </div>
                            <span class="widget-sidebar-option-arrow">
                                <x-core::icon name="ti ti-chevron-right" size="sm" />
                            </span>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <div id="widget-add-step-2" style="display: none;">
            <div class="widget-add-step-header">
                <button type="button" class="btn btn-sm btn-ghost-secondary widget-add-back-btn" id="widget-add-back">
                    <x-core::icon name="ti ti-arrow-left" size="sm" />
                </button>
                <div class="widget-add-step-info">
                    <small class="text-muted">{{ trans('packages/widget::widget.adding_to') }}</small>
                    <div class="fw-medium" id="widget-add-sidebar-name"></div>
                </div>
            </div>

            <div id="widget-add-form-container" class="widget-add-form-container">
                <div class="widget-add-loading">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2 text-muted">{{ trans('packages/widget::widget.loading_form') }}</span>
                </div>
            </div>

            <div class="widget-add-actions">
                <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">
                    {{ trans('core/base::forms.cancel') }}
                </button>
                <button type="button" class="btn btn-primary" id="widget-add-submit">
                    <x-core::icon name="ti ti-plus" size="sm" class="me-1" />
                    {{ trans('packages/widget::widget.add_widget') }}
                </button>
            </div>
        </div>
    </x-core::modal>
@endsection

@push('footer')
    <script>
        'use strict';
        var BWidget = BWidget || {};
        BWidget.routes = {
            'delete': '{{ route('widgets.destroy', ['ref_lang' => BaseHelper::stringify(request()->input('ref_lang'))]) }}',
            'save_widgets_sidebar': '{{ route('widgets.save_widgets_sidebar', ['ref_lang' => BaseHelper::stringify(request()->input('ref_lang'))]) }}',
            'get_widget_form': '{{ route('widgets.get_form', ['ref_lang' => BaseHelper::stringify(request()->input('ref_lang'))]) }}'
        };
    </script>
@endpush
