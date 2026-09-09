@php
    /** @var \Botble\Translation\Services\GetGroupedTranslationsService $service */
    $service = app(\Botble\Translation\Services\GetGroupedTranslationsService::class);

    $currentSource = (string) request('source', '');
    $currentModule = (string) request('module', '');
    $currentGroup = (string) request('group', '');
    $currentStatus = (string) request('status', '');
    $currentKeyword = (string) request('q', '');

    $sources = $service->getSources();
    $modules = $service->getModules($currentSource !== '' ? $currentSource : null);
    $groups = collect($service->getGroupsFiltered(
        $currentSource !== '' ? $currentSource : null,
        $currentModule !== '' && in_array($currentModule, $modules, true) ? $currentModule : null,
    ))
        ->mapWithKeys(fn ($group) => [$group => $service->formatGroupLabel($group) . ' — ' . $group])
        ->all();

    $hasActiveFilters = $currentSource !== '' || $currentModule !== '' || $currentGroup !== ''
        || $currentStatus !== '' || $currentKeyword !== '';

    $localeForStatus = isset($locale['locale']) ? $locale['locale'] : 'en';

    $preserved = collect(request()->query())
        ->except(['source', 'module', 'group', 'status', 'q', 'page', 'filter_table_id', 'filter_columns', 'filter_operators', 'filter_values'])
        ->all();

    $resetUrl = url()->current() . (count($preserved) ? '?' . http_build_query($preserved) : '');
@endphp

<x-core::card class="mb-3 translation-quick-filters">
    <x-core::card.body class="py-3">
        <form method="get" action="{{ url()->current() }}">
            @foreach ($preserved as $name => $value)
                @if (is_scalar($value))
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endif
            @endforeach

            <div class="row g-2 align-items-end">
                <div class="col-12 col-xl">
                    <label for="translation-filter-q" class="form-label small text-muted mb-1">
                        {{ trans('core/table::table.search') }}
                    </label>
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <x-core::icon name="ti ti-search" />
                        </span>
                        <input
                            type="search"
                            id="translation-filter-q"
                            name="q"
                            class="form-control"
                            value="{{ $currentKeyword }}"
                            placeholder="{{ trans('plugins/translation::translation.search_translations_placeholder') }}"
                            autocomplete="off"
                        >
                    </div>
                </div>

                <div class="col-6 col-md-4 col-xl-2">
                    <label for="translation-filter-source" class="form-label small text-muted mb-1">
                        {{ trans('plugins/translation::translation.source') }}
                    </label>
                    <select name="source" id="translation-filter-source" class="select-full" data-placeholder="{{ trans('plugins/translation::translation.all_sources') }}">
                        <option value="">{{ trans('plugins/translation::translation.all_sources') }}</option>
                        @foreach ($sources as $value => $label)
                            <option value="{{ $value }}" @selected($currentSource === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-4 col-xl-2">
                    <label for="translation-filter-module" class="form-label small text-muted mb-1">
                        {{ trans('plugins/translation::translation.module') }}
                    </label>
                    <select name="module" id="translation-filter-module" class="select-search-full" data-placeholder="{{ trans('plugins/translation::translation.all_modules') }}">
                        <option value="">{{ trans('plugins/translation::translation.all_modules') }}</option>
                        @foreach ($modules as $value => $label)
                            <option value="{{ $value }}" @selected($currentModule === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 col-xl-3">
                    <label for="translation-filter-group" class="form-label small text-muted mb-1">
                        {{ trans('plugins/translation::translation.group') }}
                    </label>
                    <select name="group" id="translation-filter-group" class="select-search-full" data-placeholder="{{ trans('plugins/translation::translation.all_groups') }}">
                        <option value="">{{ trans('plugins/translation::translation.all_groups') }}</option>
                        @foreach ($groups as $value => $label)
                            <option value="{{ $value }}" @selected($currentGroup === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($localeForStatus !== 'en')
                    <div class="col-12 col-md-6 col-xl-2">
                        <label for="translation-filter-status" class="form-label small text-muted mb-1">
                            {{ trans('plugins/translation::translation.status') }}
                        </label>
                        <select name="status" id="translation-filter-status" class="select-full" data-placeholder="{{ trans('plugins/translation::translation.all_statuses') }}">
                            <option value="">{{ trans('plugins/translation::translation.all_statuses') }}</option>
                            <option value="untranslated" @selected($currentStatus === 'untranslated')>
                                {{ trans('plugins/translation::translation.status_untranslated') }}
                            </option>
                        </select>
                    </div>
                @endif

                <div class="col-12 col-xl-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <x-core::icon name="ti ti-filter" class="me-1" />
                        {{ trans('plugins/translation::translation.apply_filters') }}
                    </button>
                    @if ($hasActiveFilters)
                        <a
                            href="{{ $resetUrl }}"
                            class="btn btn-outline-secondary"
                            title="{{ trans('plugins/translation::translation.reset_filters') }}"
                        >
                            <x-core::icon name="ti ti-refresh" />
                            <span class="d-none d-sm-inline ms-1">{{ trans('plugins/translation::translation.reset_filters') }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </x-core::card.body>
</x-core::card>
