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

    @php
        $countryCodeEnabled = setting('phone_number_enable_country_code', true);
        $withCountryCodeSelection =
            $countryCodeEnabled &&
            isset($options['with_country_code_selection']) &&
            $options['with_country_code_selection'];
        $inputName = $withCountryCodeSelection ? $name . '_display' : $name;
        $fieldId = $options['attr']['id'] ?? 'phone-field-' . Str::random(8);
        $inputAttributes = $options['attr'];
        $inputAttributes['class'] = trim(($options['attr']['class'] ?? '') . ' js-phone-number-mask form-control');
        $inputAttributes['id'] = $fieldId;

        if ($withCountryCodeSelection) {
            $inputAttributes['data-country-code-selection'] = 'true';
        }
    @endphp

    {!! Form::text($inputName, $options['value'], $inputAttributes) !!}

    @if ($withCountryCodeSelection)
        {!! Form::hidden($name, $options['value'], [
            'class' => 'js-phone-number-full',
            'data-phone-field' => $inputName,
            'id' => $fieldId . '-full',
        ]) !!}
    @endif
</x-core::form.field>

@if ($withCountryCodeSelection)
    @once
        @include('core/base::forms.fields.phone-number-script')
    @endonce
@endif
