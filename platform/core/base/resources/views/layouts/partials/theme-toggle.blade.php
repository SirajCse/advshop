@if (AdminHelper::themeMode() === 'dark')
    <a
        href="{{ route('toggle-theme-mode', ['theme' => 'light']) }}"
        class="px-0 nav-link"
        title="{{ trans('core/base::forms.enable_light_mode') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="bottom"
    >
        <x-core::icon name="ti ti-sun" />
    </a>
@else
    <a
        href="{{ route('toggle-theme-mode', ['theme' => 'dark']) }}"
        class="px-0 nav-link"
        title="{{ trans('core/base::forms.enable_dark_mode') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="bottom"
    >
        <x-core::icon name="ti ti-moon" />
    </a>
@endif
