<x-core::form.field
    :showLabel="$showLabel"
    :showField="$showField"
    :options="$options"
    :name="$name"
    :prepend="$prepend ?? null"
    :append="$append ?? null"
    :showError="$showError"
    :nameKey="$nameKey"
>
    <x-slot:label>
        @if ($showLabel && $options['label'] !== false && $options['label_show'])
            {!! Form::customLabel($name, $options['label'], $options['label_attr']) !!}
        @endif
    </x-slot:label>

    <div class="input-group">
        <input
            type="password"
            name="{{ $name }}"
            id="{{ $name }}"
            {!! Html::attributes($options['attr']) !!}
            autocomplete="new-password"
            @if (!empty($options['value']))
                value="{{ $options['value'] }}"
            @endif
            data-bb-password
        >
        <span
            class="input-password-toggle"
            data-bb-toggle-password
        >
            <x-core::icon name="ti ti-eye" />
        </span>
    </div>
</x-core::form.field>

@once
    @include('core/base::forms.fields.password-toggle-script')
@endonce
