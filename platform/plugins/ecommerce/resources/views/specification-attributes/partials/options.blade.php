@php
    $isNotDefaultLanguage = $isNotDefaultLanguage ?? false;

    // Build a map of option ID => translated value for quick lookup
    $translatedMap = [];
    if ($isNotDefaultLanguage && !empty($translatedOptions)) {
        foreach ($translatedOptions as $tOpt) {
            if (is_array($tOpt) && isset($tOpt['id'])) {
                $translatedMap[$tOpt['id']] = $tOpt['value'] ?? '';
            }
        }
    }
@endphp

<x-core::table>
    <x-core::table.header>
        <x-core::table.header.cell>
            {{ trans('plugins/ecommerce::product-specification.product.specification_table.options') }}
        </x-core::table.header.cell>
        @if (!$isNotDefaultLanguage)
            <x-core::table.header.cell />
        @endif
    </x-core::table.header>

    <x-core::table.body>
        @foreach($options as $index => $option)
            @php
                $optionId = is_array($option) && isset($option['id']) ? $option['id'] : '';
                $optionValue = is_array($option) && isset($option['value']) ? $option['value'] : (is_string($option) ? $option : '');

                if ($isNotDefaultLanguage) {
                    $defaultLabel = $optionValue;
                    $translatedValue = $translatedMap[$optionId] ?? '';
                }
            @endphp

            <x-core::table.body.row>
                <x-core::table.body.cell>
                    <input type="hidden" name="options[{{ $index }}][id]" value="{{ $optionId }}" />

                    @if ($isNotDefaultLanguage)
                        <div class="text-muted small mb-1">{{ $defaultLabel }}</div>
                        <input
                            type="text"
                            class="form-control"
                            name="options[{{ $index }}][value]"
                            value="{{ $translatedValue }}"
                            data-bb-toggle="option-value"
                            placeholder="{{ trans('plugins/ecommerce::product-specification.product.specification_table.enter_translation') }}"
                        />
                    @else
                        <input
                            type="text"
                            class="form-control"
                            name="options[{{ $index }}][value]"
                            value="{{ $optionValue }}"
                            data-bb-toggle="option-value"
                        />
                    @endif
                </x-core::table.body.cell>

                @if (!$isNotDefaultLanguage)
                    <x-core::table.body.cell style="width: 7%">
                        <x-core::button
                            type="button"
                            :icon-only="true"
                            icon="ti ti-trash"
                            data-bb-toggle="remove-option"
                        />
                    </x-core::table.body.cell>
                @endif
            </x-core::table.body.row>
        @endforeach
    </x-core::table.body>
</x-core::table>

@if (!$isNotDefaultLanguage)
<script>
    $(function() {
        let optionIndex = {{ count($options) }};

        $(document)
            .on('change', '.js-base-form select[name="type"]', (e) => {
                const $currentTarget = $(e.currentTarget)
                const $options = $currentTarget.closest('form').find('.specification-attribute-options')

                if ($currentTarget.val() === 'select' || $currentTarget.val() === 'radio') {
                    $options.show()
                } else {
                    $options.hide()
                }
            })
            .on('click', '[data-bb-toggle="add-option"]', (e) => {
                e.preventDefault()

                const newId = [...Array(8)].map(() => Math.floor(Math.random() * 16).toString(16)).join('')
                const $table = $(e.currentTarget).closest('.card').find('table tbody')

                $table.append(`<tr>
                    <td>
                        <input type="hidden" name="options[${optionIndex}][id]" value="${newId}" />
                        <input type="text" class="form-control" name="options[${optionIndex}][value]" data-bb-toggle="option-value" />
                    </td>
                    <td style="width: 7%">
                        <button type="button" class="btn btn-icon" data-bb-toggle="remove-option">
                            <i class="ti ti-trash"></i>
                        </button>
                    </td>
                </tr>`)

                optionIndex++
            })
            .on('click', '[data-bb-toggle="remove-option"]', (e) => {
                e.preventDefault()

                $(e.currentTarget).closest('tr').remove()
            })
    });
</script>
@endif
