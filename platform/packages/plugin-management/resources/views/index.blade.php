@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
    <style>
        .plugin-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid var(--bb-border-color);
            border-radius: 0.75rem;
            overflow: hidden;
        }
        .plugin-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }
        .plugin-card .plugin-image-area {
            position: relative;
            aspect-ratio: 2 / 1;
            overflow: hidden;
        }
        .plugin-card .plugin-image-placeholder {
            background: linear-gradient(160deg, #1e293b 0%, #334155 100%);
        }
        .plugin-card .plugin-icon-wrapper {
            width: 2.75rem;
            height: 2.75rem;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 0.625rem;
            backdrop-filter: blur(8px);
        }
        .plugin-card .plugin-icon-wrapper .icon {
            color: rgba(255, 255, 255, 0.85) !important;
        }
        .plugin-card .plugin-name {
            font-size: 0.9375rem;
            font-weight: 600;
            line-height: 1.3;
        }
        .plugin-card .card-body {
            padding: 1rem 1rem 0.75rem;
        }
        .plugin-card .plugin-description {
            font-size: 0.8125rem;
            line-height: 1.5;
            color: var(--tblr-secondary);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.4em;
        }
        .plugin-card .plugin-meta {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-size: 0.75rem;
            color: var(--tblr-secondary);
        }
        .plugin-card .plugin-meta .meta-divider {
            width: 1px;
            height: 0.875rem;
            background: var(--bb-border-color);
        }
        .plugin-card .plugin-status-dot {
            width: 0.4375rem;
            height: 0.4375rem;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }
        .plugin-card .plugin-status-dot.active {
            background: #2fb344;
            box-shadow: 0 0 0 2px rgba(47, 179, 68, 0.2);
        }
        .plugin-card .plugin-status-dot.inactive {
            background: #9ba4ae;
        }
        .plugin-card .card-footer {
            background: var(--tblr-bg-surface-secondary, #f8fafc);
            border-top: 1px solid var(--bb-border-color);
            padding: 0.5rem 0.75rem;
        }
        .plugin-card .card-footer .btn {
            font-size: 0.75rem;
            padding: 0.25rem 0.625rem;
            border-color: transparent;
            background: transparent;
            color: var(--tblr-secondary);
        }
        .plugin-card .card-footer .btn:hover {
            background: var(--tblr-bg-surface, #fff);
            color: var(--tblr-body-color);
            border-color: var(--bb-border-color);
        }
        .plugin-card .card-footer .btn-activate {
            color: var(--tblr-primary);
        }
        .plugin-card .card-footer .btn-activate:hover {
            background: rgba(var(--bb-primary-rgb), 0.06);
            color: var(--tblr-primary);
            border-color: rgba(var(--bb-primary-rgb), 0.2);
        }
        .plugin-card .card-footer .btn-deactivate {
            color: var(--tblr-warning);
        }
        .plugin-card .card-footer .btn-deactivate:hover {
            background: rgba(var(--tblr-warning-rgb, 245, 159, 0), 0.06);
            color: var(--tblr-warning);
            border-color: rgba(var(--tblr-warning-rgb, 245, 159, 0), 0.2);
        }
        .plugin-card .card-footer .btn-remove {
            color: var(--tblr-danger);
        }
        .plugin-card .card-footer .btn-remove:hover {
            background: rgba(var(--tblr-danger-rgb, 214, 57, 57), 0.06);
            color: var(--tblr-danger);
            border-color: rgba(var(--tblr-danger-rgb, 214, 57, 57), 0.2);
        }
        .plugin-card .card-footer .btn-list {
            gap: 0.125rem;
        }
        .plugin-card.plugin-deactivated {
            opacity: 0.55;
        }
        .plugin-card.plugin-deactivated:hover {
            opacity: 1;
        }
    </style>
@endpush

@push('header-action')
    @if (
        $isEnabledMarketplaceFeature =
            config('packages.plugin-management.general.enable_marketplace_feature') &&
            auth()->user()->hasPermission('plugins.marketplace'))
        <x-core::button
            tag="a"
            :href="route('plugins.new')"
            color="primary"
            icon="ti ti-plus"
            class="ms-auto"
        >
            {{ trans('packages/plugin-management::plugin.plugins_add_new') }}
        </x-core::button>
    @endif

    {!! apply_filters('plugin_management_installed_header_actions', null) !!}
@endpush

@section('content')
    @if ($plugins->isNotEmpty())
        <x-core::card class="mb-4">
            <x-core::card.body class="py-3">
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-sm-center">
                    <div class="w-100" style="max-width: 320px;">
                        <x-core::form.text-input
                            type="search"
                            name="search"
                            :placeholder="trans('packages/plugin-management::plugin.search')"
                            :group-flat="true"
                            data-bb-toggle="change-search"
                        >
                            <x-slot:prepend>
                                <span class="input-group-text">
                                    <x-core::icon name="ti ti-search" />
                                </span>
                            </x-slot:prepend>
                        </x-core::form.text-input>
                    </div>

                    <div class="flex-shrink-0">
                        <div class="d-block d-sm-none dropdown">
                            <x-core::button
                                class="dropdown-toggle"
                                data-bs-toggle="dropdown"
                            >
                                <span
                                    data-bb-toggle="status-filter-label"
                                    class="ms-1"
                                >
                                    {{ $filterStatuses[array_key_first($filterStatuses)] }}
                                    (<span
                                        data-bb-toggle="plugins-count"
                                        data-status="{{ array_key_first($filterStatuses) }}"
                                    >{{ $plugins->count() }}</span>)
                                </span>
                            </x-core::button>
                            <div
                                class="dropdown-menu dropdown-menu-end"
                                data-popper-placement="bottom-end"
                            >
                                @foreach ($filterStatuses as $key => $value)
                                    <button
                                        @class(['dropdown-item', 'active' => $loop->first])
                                        type="button"
                                        data-value="{{ $key }}"
                                        data-bb-toggle="change-filter-plugin-status"
                                    >
                                        {{ $value }}
                                        (<span
                                            data-bb-toggle="plugins-count"
                                            data-status="{{ $key }}"
                                        >0</span>)
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-none d-sm-flex form-selectgroup">
                            @foreach ($filterStatuses as $key => $value)
                                <label class="form-selectgroup-item">
                                    <input
                                        type="radio"
                                        name="status"
                                        value="{{ $key }}"
                                        data-bb-toggle="change-filter-plugin-status"
                                        class="form-selectgroup-input"
                                        @checked($loop->first)
                                    />
                                    <span class="form-selectgroup-label">
                                        {{ $value }}
                                        (<span
                                            data-bb-toggle="plugins-count"
                                            data-status="{{ $key }}"
                                        >0</span>)
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-core::card.body>
        </x-core::card>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4 plugin-list">
            @foreach ($plugins as $plugin)
                <div
                    class="col plugin-item"
                    data-name="{{ $plugin->name }}"
                    data-author="{{ $plugin->author }}"
                    data-description="{{ $plugin->description }}"
                    data-status="{{ $plugin->status ? 'activated' : 'not-activated' }}"
                >
                    <x-core::card @class(['h-100 plugin-card', 'plugin-deactivated' => !$plugin->status])>
                        <div @class(['plugin-image-area d-flex align-items-center justify-content-center', 'plugin-image-placeholder' => !$plugin->image])
                            @style(["background-image: url('$plugin->image'); background-size: cover; background-position: center" => $plugin->image])
                        >
                            @if (!$plugin->image)
                                <div class="d-flex align-items-center justify-content-center plugin-icon-wrapper">
                                    <x-core::icon
                                        name="ti ti-puzzle"
                                        style="font-size: 1.25rem;"
                                    />
                                </div>
                            @endif
                        </div>

                        <x-core::card.body class="d-flex flex-column">
                            <h4 class="plugin-name text-truncate mb-1" title="{{ $plugin->name }}">{{ $plugin->name }}</h4>
                            @if ($plugin->description)
                                <p class="plugin-description mb-0" title="{{ $plugin->description }}">
                                    {{ $plugin->description }}
                                </p>
                            @endif

                            <div class="mt-auto pt-2">
                                <div class="plugin-meta">
                                    <span class="d-flex align-items-center gap-1">
                                        <span @class(['plugin-status-dot', 'active' => $plugin->status, 'inactive' => !$plugin->status])></span>
                                        {{ $plugin->status ? trans('packages/plugin-management::plugin.activated') : trans('packages/plugin-management::plugin.deactivated') }}
                                    </span>
                                    @if (!config('packages.plugin-management.general.hide_plugin_author', false) && $plugin->author)
                                        <span class="meta-divider"></span>
                                        @if (!empty($plugin->url))
                                            <a href="{{ $plugin->url }}" target="_blank" class="text-reset text-decoration-none">{{ $plugin->author }}</a>
                                        @else
                                            <span>{{ $plugin->author }}</span>
                                        @endif
                                    @endif
                                    @if ($plugin->version)
                                        <span class="ms-auto">v{{ $plugin->version }}</span>
                                    @endif
                                </div>
                            </div>
                        </x-core::card.body>

                        <x-core::card.footer>
                            <div class="btn-list justify-content-start">
                                @if (auth()->user()->hasPermission('plugins.edit'))
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-trigger-change-status {{ $plugin->status ? 'btn-deactivate' : 'btn-activate' }}"
                                        data-plugin="{{ $plugin->path }}"
                                        data-status="{{ $plugin->status }}"
                                        data-check-requirement-url="{{ route('plugins.check-requirement', ['name' => $plugin->path]) }}"
                                        data-change-status-url="{{ route('plugins.change.status', ['name' => $plugin->path]) }}"
                                    >
                                        <x-core::icon :name="$plugin->status ? 'ti ti-player-pause' : 'ti ti-player-play'" class="me-1" />
                                        @if ($plugin->status)
                                            {{ trans('packages/plugin-management::plugin.deactivate') }}
                                        @else
                                            {{ trans('packages/plugin-management::plugin.activate') }}
                                        @endif
                                    </button>
                                @endif

                                @if ($isEnabledMarketplaceFeature)
                                    <x-core::button
                                        class="btn-trigger-update-plugin"
                                        color="success"
                                        size="sm"
                                        icon="ti ti-refresh"
                                        style="display: none;"
                                        data-name="{{ $plugin->path }}"
                                        data-check-update="{{ $plugin->id ?? 'plugin-' . $plugin->path }}"
                                        :data-check-update-url="route('plugins.marketplace.ajax.check-update')"
                                        :data-update-url="route('plugins.marketplace.ajax.update', [
                                            'id' => '__id__',
                                            'name' => $plugin->path,
                                        ])"
                                        data-version="{{ $plugin->version }}"
                                    >
                                        {{ trans('packages/plugin-management::plugin.update') }}
                                    </x-core::button>
                                @endif

                                @if (auth()->user()->hasPermission('plugins.remove'))
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-remove btn-trigger-remove-plugin ms-auto"
                                        data-plugin="{{ $plugin->path }}"
                                        data-url="{{ route('plugins.remove', ['plugin' => $plugin->path]) }}"
                                    >
                                        <x-core::icon name="ti ti-trash" class="me-1" />
                                        {{ trans('packages/plugin-management::plugin.remove') }}
                                    </button>
                                @endif
                            </div>
                        </x-core::card.footer>
                    </x-core::card>
                </div>
            @endforeach
        </div>
    @endif

    <x-core::empty-state
        :title="trans('No plugins found')"
        :subtitle="trans('It looks as there are no plugins here.')"
        icon="ti ti-puzzle"
        @style(['display: none' => $plugins->isNotEmpty()])
    />
@stop

@push('footer')
    <x-core::modal.action
        id="remove-plugin-modal"
        type="danger"
        :title="trans('packages/plugin-management::plugin.remove_plugin')"
        :description="trans('packages/plugin-management::plugin.remove_plugin_confirm_message')"
        :submit-button-attrs="['id' => 'confirm-remove-plugin-button']"
        :submit-button-label="trans('packages/plugin-management::plugin.remove_plugin_confirm_yes')"
    />

    @if ($isEnabledMarketplaceFeature)
        <x-core::modal
            id="confirm-install-plugin-modal"
            :title="trans('packages/plugin-management::plugin.install_plugin')"
            button-id="confirm-install-plugin-button"
            :button-label="trans('packages/plugin-management::plugin.install')"
        >
            <input
                type="hidden"
                name="plugin_name"
                value=""
            >
            <input
                type="hidden"
                name="ids"
                value=""
            >

            <p id="requirement-message"></p>
        </x-core::modal>
    @endif
@endpush
