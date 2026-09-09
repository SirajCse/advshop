{{-- Horizontal progress stepper shown on the wizard config steps. --}}
{{-- $active: 1 = Branding, 2 = Account. $shouldChangeAccount: whether the Account step exists. --}}
@props([
    'active' => 1,
    'shouldChangeAccount' => true,
])

<x-core::step
    :counter="true"
    class="get-started-steps"
>
    <x-core::step.item :is-active="$active === 1">
        {{ trans('packages/get-started::get-started.step_branding') }}
    </x-core::step.item>

    @if ($shouldChangeAccount)
        <x-core::step.item :is-active="$active === 2">
            {{ trans('packages/get-started::get-started.step_account') }}
        </x-core::step.item>
    @endif
</x-core::step>
