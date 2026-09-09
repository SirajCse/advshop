<!DOCTYPE html>
<html
    lang="{{ app()->getLocale() }}"
    class="h-100"
>

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover"
    >
    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >
    <title>{{ trans('packages/page::pages.visual_builder', ['name' => $page->name]) }}</title>

    @php
        $faviconUrl = AdminHelper::getAdminFaviconUrl();
        $faviconType = setting('admin_favicon_type', 'image/x-icon');
    @endphp
    <link
        href="{{ $faviconUrl }}"
        rel="icon shortcut"
        type="{{ $faviconType }}"
    >

    <style>
        [v-cloak],
        [x-cloak] {
            display: none;
        }
    </style>

    {!! BaseHelper::googleFonts(
        'https://fonts.googleapis.com/' .
            sprintf(
                'css2?family=%s:wght@300;400;500;600;700&display=swap',
                urlencode(setting('admin_primary_font', 'Inter')),
            ),
    ) !!}

    <style>
        :root {
            --primary-font: "{{ setting('admin_primary_font', 'Inter') }}";
            --primary-color: {{ $primaryColor = setting('admin_primary_color', '#206bc4') }};
            --primary-color-rgb: {{ implode(', ', BaseHelper::hexToRgb($primaryColor)) }};
            --secondary-color: {{ $secondaryColor = setting('admin_secondary_color', '#6c7a91') }};
            --secondary-color-rgb: {{ implode(', ', BaseHelper::hexToRgb($secondaryColor)) }};
            --heading-color: {{ setting('admin_heading_color', 'inherit') }};
            --text-color: {{ $textColor = setting('admin_text_color', '#182433') }};
            --text-color-rgb: {{ implode(', ', BaseHelper::hexToRgb($textColor)) }};
            --link-color: {{ $linkColor = setting('admin_link_color', '#206bc4') }};
            --link-color-rgb: {{ implode(', ', BaseHelper::hexToRgb($linkColor)) }};
            --link-hover-color: {{ $linkHoverColor = setting('admin_link_hover_color', '#206bc4') }};
            --link-hover-color-rgb: {{ implode(', ', BaseHelper::hexToRgb($linkHoverColor)) }};
        }
    </style>

    {!! Assets::renderHeader(['core']) !!}

    {{-- Load RvMedia assets for image picker in edit panel --}}
    {!! RvMedia::renderHeader() !!}

    @include('core/base::elements.common')
</head>

<body
    class="d-flex flex-column h-100 vb-body"
    @if (AdminHelper::themeMode() === 'dark') data-bs-theme="dark" @endif
>
    <div id="visual-builder-app">
        @include('packages/page::visual-builder.header')

        @php
            $visualBuilderNotification = apply_filters('page_visual_builder_after_header', '', $page);
        @endphp
        @if ($visualBuilderNotification)
            <div class="vb-notification px-3 py-2 border-bottom">{!! $visualBuilderNotification !!}</div>
        @endif

        <div class="vb-container d-flex flex-fill overflow-hidden">
            <!-- Sidebar -->
            <aside
                class="vb-sidebar border-end bg-body-tertiary d-flex flex-column"
                id="vb-sidebar"
            >
                <div class="flex-fill overflow-auto p-3">
                    <!-- Shortcode List View -->
                    <div
                        class="vb-list-view"
                        id="vb-list-view"
                    >
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 class="fw-bold mb-0">{{ trans('packages/page::pages.visual_builder_shortcodes') }}</h3>
                            <x-core::button
                                type="button"
                                id="vb-add-shortcode-btn"
                                size="sm"
                                color="primary"
                                icon="ti ti-plus"
                            >
                                {{ trans('packages/page::pages.add') }}
                            </x-core::button>
                        </div>

                        <div
                            class="vb-shortcode-list"
                            id="vb-shortcode-list"
                        >
                            <!-- Shortcodes will be rendered here by JavaScript -->
                        </div>
                    </div>

                    <!-- Edit Panel View (hidden by default) -->
                    <div
                        class="vb-edit-panel d-none"
                        id="vb-edit-panel"
                    >
                        <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
                            <x-core::button
                                type="button"
                                id="vb-back-btn"
                                size="sm"
                                color="ghost-secondary"
                                icon="ti ti-arrow-left"
                                ::tooltip="trans('packages/page::pages.back')"
                            />
                            <h3
                                class="mb-0 fw-bold flex-fill"
                                id="vb-panel-title"
                            >{{ trans('packages/page::pages.visual_builder_edit_shortcode') }}</h3>
                            <x-core::button
                                type="button"
                                id="vb-close-panel-btn"
                                size="sm"
                                color="ghost-secondary"
                                icon="ti ti-x"
                                ::tooltip="trans('packages/page::pages.close')"
                            />
                        </div>

                        <div
                            class="vb-panel-content"
                            id="vb-panel-content"
                        >
                            <!-- Edit form will be rendered here by JavaScript -->
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Preview Iframe -->
            <main
                class="vb-preview flex-fill d-flex flex-column align-items-center justify-content-center bg-body position-relative"
                id="vb-preview"
            >
                <div
                    class="vb-preview-loading text-center"
                    id="vb-preview-loading"
                >
                    <div
                        class="spinner-border text-primary mb-3"
                        role="status"
                    >
                        <span class="visually-hidden">{{ trans('packages/page::pages.loading') }}</span>
                    </div>
                    <p class="text-muted">{{ trans('packages/page::pages.loading_preview') }}</p>
                </div>

                <div
                    class="vb-preview-error text-center p-4 d-none"
                    id="vb-preview-error"
                >
                    <x-core::alert
                        type="danger"
                        class="d-inline-flex flex-column align-items-center gap-3"
                    >
                        <div class="d-flex align-items-center gap-2">
                            <x-core::icon name="ti ti-alert-circle" />
                            <span id="vb-error-message">{{ trans('packages/page::pages.visual_builder_unable_to_load_preview') }}</span>
                        </div>
                        <x-core::button
                            type="button"
                            id="vb-reload-preview-btn"
                            size="sm"
                            color="primary"
                            icon="ti ti-refresh"
                        >
                            {{ trans('packages/page::pages.visual_builder_reload_preview') }}
                        </x-core::button>
                    </x-core::alert>
                </div>

                <div
                    class="vb-preview-frame-container d-none"
                    id="vb-preview-frame-container"
                >
                    @php
                        $previewParams = ['visual_builder' => 1];
                        if ($refLangParam = request()->input('ref_lang')) {
                            $previewParams['ref_lang'] = $refLangParam;
                        }
                    @endphp
                    <iframe
                        id="vb-preview-iframe"
                        name="vb-preview-iframe"
                        src="{{ route('pages.preview', $page) }}?{{ http_build_query($previewParams) }}"
                        frameborder="0"
                        sandbox="allow-same-origin allow-scripts allow-forms allow-popups"
                        class="border-0"
                    ></iframe>
                </div>
            </main>
        </div>

        <!-- Add Shortcode List Modal -->
        <x-core::modal
            :title="trans('packages/shortcode::shortcode.ui-blocks')"
            id="vb-shortcode-list-modal"
            class="shortcode-list-modal"
            size="full"
            :scrollable="true"
        >
            <div id="vb-shortcode-list-content">
                @include('packages/page::visual-builder.partials.shortcode-list')
            </div>

            <x-slot:footer>
                <div class="btn-list">
                    <x-core::button data-bs-dismiss="modal">
                        {{ trans('core/base::base.close') }}
                    </x-core::button>

                    <x-core::button
                        color="primary"
                        data-bb-toggle="vb-shortcode-use"
                        disabled
                    >
                        {{ trans('packages/shortcode::shortcode.use') }}
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal>

        <!-- Shortcode Config Modal -->
        <x-core::modal
            :title="trans('core/base::forms.add_short_code')"
            id="vb-shortcode-modal"
            class="shortcode-modal"
            :scrollable="true"
            data-bs-backdrop="static"
        >
            <form class="shortcode-data-form">
                <input
                    type="hidden"
                    class="shortcode-input-key"
                >
                <div class="shortcode-admin-config short-code-admin-config"></div>
            </form>

            <x-slot:footer>
                <x-core::button data-bs-dismiss="modal">
                    {{ trans('core/base::tables.cancel') }}
                </x-core::button>
                <x-core::button
                    color="primary"
                    data-bb-toggle="vb-shortcode-add-single"
                    :data-add-text="trans('core/base::forms.add')"
                    :data-update-text="trans('core/base::forms.update')"
                >
                    {{ trans('core/base::forms.add') }}
                </x-core::button>
            </x-slot:footer>
        </x-core::modal>
    </div>

    <!-- Pass data to JavaScript -->
    @php
        $refLang = request()->input('ref_lang');
        $refLangQuery = $refLang ? '?' . http_build_query(['ref_lang' => $refLang]) : '';
    @endphp
    <script>
        window.visualBuilderData = {
            pageId: @json($page->id),
            pageName: @json($page->name),
            shortcodes: @json($shortcodes),
            availableShortcodes: @json($availableShortcodes),
            previewUrl: @json(route('pages.preview', $page) . $refLangQuery),
            saveUrl: @json(route('pages.visual-builder.save', $page) . $refLangQuery),
            editUrl: @json(route('pages.edit', $page) . $refLangQuery),
            renderItemsUrl: @json(route('pages.visual-builder.render-items')),
            renderTypesUrl: @json(route('pages.visual-builder.render-types')),
            refLang: @json($refLang),
            csrfToken: @json(csrf_token()),
            translations: {
                loading: @json(trans('packages/page::pages.loading')),
                saving: @json(trans('packages/page::pages.visual_builder_saving')),
                saved: @json(trans('packages/page::pages.visual_builder_saved')),
                error: @json(trans('packages/page::pages.visual_builder_error')),
                confirmDelete: @json(trans('packages/page::pages.visual_builder_confirm_delete_shortcode')),
                unsavedChanges: @json(trans('packages/page::pages.visual_builder_confirm_leave')),
            }
        };
    </script>

    {!! Assets::renderFooter() !!}
    {!! RvMedia::renderFooter() !!}
    @include('core/media::partials.media')
</body>

</html>
