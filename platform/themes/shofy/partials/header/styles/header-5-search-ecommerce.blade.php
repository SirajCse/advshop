<div class="col-xxl-4 d-none d-xxl-block">
    <div class="tp-header-search-5">
        <x-plugins-ecommerce::fronts.ajax-search>
            <div class="tp-header-search-input-box-5">
                <div class="tp-header-search-input-5">
                    <x-plugins-ecommerce::fronts.ajax-search.input />
                    <span class="tp-header-search-icon-5">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M8.11111 15.2222C12.0385 15.2222 15.2222 12.0385 15.2222 8.11111C15.2222 4.18375 12.0385 1 8.11111 1C4.18375 1 1 4.18375 1 8.11111C1 12.0385 4.18375 15.2222 8.11111 15.2222Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path d="M16.9995 17L13.1328 13.1333" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </div>
                <x-plugins-ecommerce::fronts.ajax-search.categories-dropdown />
                <button type="submit">{{ __('Search') }}</button>
            </div>
        </x-plugins-ecommerce::fronts.ajax-search>
    </div>
</div>
