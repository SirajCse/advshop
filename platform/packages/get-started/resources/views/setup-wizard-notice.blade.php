<x-core::alert
    type="info"
    class="resume-setup-wizard-wrapper"
>
    <div class="d-flex flex-wrap align-items-center gap-2 w-100">
        <span class="flex-grow-1">
            {!! BaseHelper::clean(
                trans('packages/get-started::get-started.setup_wizard_button', [
                    'link' => Html::link(
                        '#',
                        trans('packages/get-started::get-started.click_here'),
                        ['class' => 'resume-setup-wizard'],
                        null,
                        false,
                    )->toHtml(),
                ]),
            ) !!}
        </span>
        <a
            href="#"
            class="dismiss-setup-wizard text-muted text-nowrap ms-auto"
            data-url="{{ route('get-started.dismiss') }}"
        >
            <x-core::icon name="ti ti-x" />
            {{ trans('packages/get-started::get-started.dont_show_again') }}
        </a>
    </div>
</x-core::alert>
