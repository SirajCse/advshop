@php
    $refLang = request()->input('ref_lang');
    $currentLanguage = null;
    if ($refLang && defined('LANGUAGE_MODULE_SCREEN_NAME') && function_exists('Language')) {
        $currentLanguage = Language::getCurrentAdminLanguage();
    }
@endphp
<header
    class="navbar navbar-expand-md d-print-none vb-header"
    id="vb-header"
>
    <div class="container-fluid">
        <div class="navbar-brand d-flex align-items-center gap-2">
            <x-core::icon name="ti ti-layout-dashboard" />
            <span class="fw-bold">{{ $page->name }}</span>
            @if ($currentLanguage)
                <span class="badge bg-info-lt d-flex align-items-center gap-1">
                    @if (!empty($currentLanguage['lang_flag']))
                        <span class="flag-icon flag-icon-{{ $currentLanguage['lang_flag'] }}"></span>
                    @endif
                    {{ $currentLanguage['lang_name'] ?? $refLang }}
                </span>
            @endif
            <span
                class="badge bg-warning-lt d-none"
                id="vb-unsaved-indicator"
            >
                <x-core::icon
                    name="ti ti-circle-dot"
                    class="icon-sm"
                />
                {{ trans('packages/page::pages.visual_builder_unsaved_changes') }}
            </span>
        </div>

        <div class="navbar-nav order-md-last flex-row">
            <div class="nav-item me-2">
                <div class="btn-group" role="group">
                    <button
                            type="button"
                            class="btn btn-icon"
                            id="vb-undo-btn"
                            disabled
                            data-bs-toggle="tooltip"
                            title="Undo (Ctrl+Z)">
                        <x-core::icon name="ti ti-arrow-back-up" />
                    </button>
                    <button
                            type="button"
                            class="btn btn-icon"
                            id="vb-redo-btn"
                            disabled
                            data-bs-toggle="tooltip"
                            title="Redo (Ctrl+Y)">
                        <x-core::icon name="ti ti-arrow-forward-up" />
                    </button>
                </div>
            </div>

            <div class="nav-item d-none d-md-flex me-2">
                <div
                    class="btn-group"
                    role="group"
                    id="vb-device-modes"
                >
                    <input
                        type="radio"
                        class="btn-check"
                        name="device-mode"
                        id="device-desktop"
                        autocomplete="off"
                        checked
                        data-device="desktop"
                    >
                    <label
                        class="btn btn-icon"
                        for="device-desktop"
                        data-bs-toggle="tooltip"
                        title="{{ trans('packages/page::pages.visual_builder_device_desktop') }}"
                    >
                        <x-core::icon name="ti ti-device-desktop" />
                    </label>

                    <input
                        type="radio"
                        class="btn-check"
                        name="device-mode"
                        id="device-tablet"
                        autocomplete="off"
                        data-device="tablet"
                    >
                    <label
                        class="btn btn-icon"
                        for="device-tablet"
                        data-bs-toggle="tooltip"
                        title="{{ trans('packages/page::pages.visual_builder_device_tablet') }}"
                    >
                        <x-core::icon name="ti ti-device-tablet" />
                    </label>

                    <input
                        type="radio"
                        class="btn-check"
                        name="device-mode"
                        id="device-mobile"
                        autocomplete="off"
                        data-device="mobile"
                    >
                    <label
                        class="btn btn-icon"
                        for="device-mobile"
                        data-bs-toggle="tooltip"
                        title="{{ trans('packages/page::pages.visual_builder_device_mobile') }}"
                    >
                        <x-core::icon name="ti ti-device-mobile" />
                    </label>
                </div>
            </div>

            {!! apply_filters('page_visual_builder_header_actions', '', $page) !!}

            <div class="nav-item">
                <div class="btn-list">
                    <x-core::button
                        type="button"
                        id="vb-save-btn"
                        color="success"
                        icon="ti ti-device-floppy"
                    >
                        <span class="d-none d-sm-inline">{{ trans('packages/page::pages.save') }}</span>
                    </x-core::button>

                    <x-core::button
                        tag="a"
                        :href="route('pages.edit', $page) . ($refLang ? '?' . http_build_query(['ref_lang' => $refLang]) : '')"
                        id="vb-close-btn"
                        color="secondary"
                        icon="ti ti-x"
                    >
                        <span class="d-none d-sm-inline">{{ trans('packages/page::pages.close') }}</span>
                    </x-core::button>
                </div>
            </div>
        </div>
    </div>
</header>
