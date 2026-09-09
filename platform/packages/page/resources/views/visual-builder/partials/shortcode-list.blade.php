@php
    $shortcodes = Shortcode::getAll();
@endphp

<x-core::form.text-input
    type="search"
    name="search"
    :placeholder="trans('packages/shortcode::shortcode.search')"
    :group-flat="true"
    class="mb-3"
>
    <x-slot:prepend>
        <span class="input-group-text">
            <x-core::icon name="ti ti-search" />
        </span>
    </x-slot:prepend>

    <x-slot:append>
        <button
            type="button"
            class="input-group-text"
            data-bb-toggle="shortcode-clear-search"
        >
            <x-core::icon name="ti ti-x" />
        </button>
    </x-slot:append>
</x-core::form.text-input>

<div class="row shortcode-list">
    @foreach ($shortcodes as $key => $shortcode)
        @continue(!isset($shortcode['name']))
        <div class="col-xl-3 col-lg-4 col-sm-6 mb-3">
            <label
                class="shortcode-item-wrapper w-100"
                data-bb-toggle="vb-shortcode-select"
                data-name="{{ $shortcode['name'] }}"
                data-url="{{ route('short-codes.ajax-get-admin-config', $key) }}"
                data-description="{{ $shortcode['description'] }}"
                data-key="{{ $key }}"
                for="vb-shortcode-item-{{ $loop->index }}"
            >
                <input
                    class="d-none shortcode-item-input"
                    id="vb-shortcode-item-{{ $loop->index }}"
                    value="{{ $loop->index }}"
                    type="radio"
                    name="shortcode_name"
                    data-bb-toggle="shortcode-item-radio"
                >
                <div class="shortcode-item">
                    <x-core::card>
                        <div class="image-wrapper w-100 position-relative overflow-hidden">
                            <img
                                src="{{ $image = Arr::get($shortcode, 'previewImage') ?: asset('vendor/core/packages/shortcode/images/placeholder-code.jpg') }}"
                                alt="{{ $shortcode['name'] }}"
                            />
                        </div>

                        <x-core::card.header>
                            <div class="w-100">
                                <x-core::card.title
                                    class="mb-1"
                                    title="{{ $shortcode['name'] }}"
                                >
                                    {{ $shortcode['name'] }}
                                </x-core::card.title>

                                <div class="row align-items-center">
                                    <x-core::card.subtitle
                                        class="col-9"
                                        title="{{ $shortcode['description'] }}"
                                    >
                                        {{ $shortcode['description'] }}
                                    </x-core::card.subtitle>

                                    <div class="col-3 text-end">
                                        <x-core::button
                                            size="xs"
                                            class="use-button"
                                            data-bb-toggle="vb-shortcode-button-use"
                                        >
                                            {{ trans('packages/shortcode::shortcode.use') }}
                                        </x-core::button>
                                    </div>
                                </div>
                            </div>
                        </x-core::card.header>
                    </x-core::card>
                </div>
            </label>
        </div>
    @endforeach
</div>

<x-core::empty-state
    :title="trans('packages/shortcode::shortcode.no_shortcode_found')"
    style="display: none;"
    class="shortcode-empty"
/>
