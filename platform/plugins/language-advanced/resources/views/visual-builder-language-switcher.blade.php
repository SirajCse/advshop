@if ($languages->count() > 1)
    <div class="nav-item me-2">
        <x-core::dropdown>
            <x-slot:trigger>
                <button
                    class="btn btn-ghost-secondary d-flex align-items-center gap-2"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                >
                    @if ($currentLanguage)
                        {!! language_flag($currentLanguage->lang_flag, $currentLanguage->lang_name) !!}
                        <span class="d-none d-sm-inline">{{ $currentLanguage->lang_name }}</span>
                    @endif
                    <x-core::icon name="ti ti-chevron-down" class="icon-sm" />
                </button>
            </x-slot:trigger>

            @foreach ($languages as $language)
                @php
                    $isCurrentLanguage = $currentLanguage && $language->lang_code === $currentLanguage->lang_code;
                    $refLangParam = $language->lang_code === Language::getDefaultLocaleCode() ? '' : '?ref_lang=' . $language->lang_code;
                    $url = route('pages.visual-builder', $page) . $refLangParam;
                @endphp

                <x-core::dropdown.item
                    :href="$url"
                    class="d-flex gap-2 align-items-center {{ $isCurrentLanguage ? 'active' : '' }}"
                >
                    {!! language_flag($language->lang_flag, $language->lang_name) !!}
                    {{ $language->lang_name }}
                    @if ($isCurrentLanguage)
                        <x-core::icon name="ti ti-check" class="icon-sm ms-auto" />
                    @endif
                </x-core::dropdown.item>
            @endforeach
        </x-core::dropdown>
    </div>
@endif
