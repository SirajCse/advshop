<link
    rel="stylesheet"
    href="{{ asset('vendor/core/core/base/libraries/intl-tel-input/css/intlTelInput-v2.min.css') }}"
>

<style>
    .iti {
        width: 100%;
        display: block;
    }

    .iti__input {
        width: 100% !important;
    }

    .position-relative .iti {
        width: 100%;
    }

    .auth-input-icon+.iti {
        padding-left: 2.5rem;
    }

    .auth-input-icon+.iti .iti__input {
        padding-left: 3rem;
    }

    .iti__country-list {
        z-index: 1050;
        max-width: 500px;
        list-style: none !important;
        padding: 0 !important;
    }

    @media (max-width: 576px) {
        .iti__country-list {
            max-width: calc(100vw - 30px);
        }
    }

    .iti__country-list .iti__search-container {
        position: sticky;
        top: 0;
        background: #fff;
        padding: 8px !important;
        border-bottom: 1px solid #e0e0e0;
        z-index: 1;
    }

    .iti__country-list .iti__search-input {
        width: 100%;
        padding: 8px 10px !important;
        border: 1px solid #ddd;
        border-radius: 4px;
        outline: none;
        font-size: 14px;
        box-sizing: border-box;
        color: #333;
        background-color: #fff;
    }

    .iti__country-list .iti__search-input:focus {
        border-color: #1e88e5;
    }

    .iti__country.iti__hidden {
        display: none !important;
    }

    .iti__no-results {
        padding: 10px 15px;
        color: #999;
        text-align: center;
        font-size: 14px;
    }

    .iti__country-list li {
        list-style: none !important;
    }

    .iti--separate-dial-code .iti__selected-dial-code {
        padding-left: 6px;
    }

    body[dir="rtl"] .iti {
        direction: ltr;
        text-align: left;
    }

    body[dir="rtl"] .iti__input {
        direction: ltr;
        text-align: left;
    }

    body[dir="rtl"] .auth-input-icon+.iti {
        padding-left: 0;
        padding-right: 2.5rem;
    }

    body[dir="rtl"] .auth-input-icon+.iti .iti__input {
        padding-left: 0;
        padding-right: 3rem;
    }

    body[dir="rtl"] .iti--separate-dial-code .iti__selected-dial-code {
        padding-left: 0;
        padding-right: 6px;
    }

    body[dir="rtl"] .iti__country-list {
        text-align: left;
    }

    body[dir="rtl"] .iti__country {
        padding-left: 15px !important;
    }

    body[dir="rtl"] .iti-mobile .iti--container {
        left: 15px !important;
        right: 15px !important;
        direction: ltr;
    }

    body[dir="rtl"] .iti-mobile .iti__country-list {
        width: 100%;
        max-width: none;
    }

    @media (max-width: 768px) {
        body[dir="rtl"] .iti--container {
            direction: ltr;
            left: 15px !important;
            right: 15px !important;
        }

        body[dir="rtl"] .iti__country-list {
            white-space: normal;
            padding-left: 5px !important;
        }

        body[dir="rtl"] .iti__country {
            padding: 10px 10px 10px 18px !important;
        }
    }

    .iti--container {
        z-index: 9999;
    }

    [data-bs-theme="dark"] .iti__country-list,
    [data-bs-theme="dark"] .iti--container .iti__country-list,
    .dark-mode .iti__country-list,
    .dark-mode .iti--container .iti__country-list {
        background-color: #1e1e2d;
        border-color: rgba(255, 255, 255, 0.1);
    }

    [data-bs-theme="dark"] .iti__country-list .iti__search-container,
    .dark-mode .iti__country-list .iti__search-container {
        background: #1e1e2d;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    [data-bs-theme="dark"] .iti__country-list .iti__search-input,
    .dark-mode .iti__country-list .iti__search-input {
        background-color: #2a2a3c;
        border-color: rgba(255, 255, 255, 0.15);
        color: #fff;
    }

    [data-bs-theme="dark"] .iti__country-list .iti__search-input::placeholder,
    .dark-mode .iti__country-list .iti__search-input::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    [data-bs-theme="dark"] .iti__country-list .iti__search-input:focus,
    .dark-mode .iti__country-list .iti__search-input:focus {
        border-color: #3699ff;
    }

    [data-bs-theme="dark"] .iti__country,
    .dark-mode .iti__country {
        background-color: transparent;
    }

    [data-bs-theme="dark"] .iti__country:hover,
    [data-bs-theme="dark"] .iti__country.iti__highlight,
    .dark-mode .iti__country:hover,
    .dark-mode .iti__country.iti__highlight {
        background-color: rgba(255, 255, 255, 0.08);
    }

    [data-bs-theme="dark"] .iti__country-name,
    .dark-mode .iti__country-name {
        color: rgba(255, 255, 255, 0.9);
    }

    [data-bs-theme="dark"] .iti__dial-code,
    .dark-mode .iti__dial-code {
        color: rgba(255, 255, 255, 0.6);
    }

    [data-bs-theme="dark"] .iti__no-results,
    .dark-mode .iti__no-results {
        color: rgba(255, 255, 255, 0.5);
    }

    [data-bs-theme="dark"] .iti__divider,
    .dark-mode .iti__divider {
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }
</style>

<script data-pagespeed-no-defer src="{{ asset('vendor/core/core/base/libraries/intl-tel-input/js/intlTelInput.min.js') }}"></script>

<script>
    (function() {
        if (window.bbPhoneNumberFieldInitialized) {
            return;
        }

        window.bbPhoneNumberFieldInitialized = true;

        function initPhoneNumberFields() {
            document.querySelectorAll('.js-phone-number-mask[data-country-code-selection="true"]').forEach(function(element) {
                if (element.dataset.itiInitialized === 'true') {
                    return;
                }

                const hasCountryCodeSelection = element.dataset.countryCodeSelection === 'true';

                @php
                    $selectedCountries = json_decode(setting('phone_number_available_countries', '[]'), true) ?: [];
                    $availableCountries = array_map('strtolower', $selectedCountries);
                @endphp

                const availableCountries = @json($availableCountries);

                const getDefaultCountry = function() {
                    if (availableCountries.length === 0) {
                        return 'us';
                    }
                    return availableCountries.includes('us') ? 'us' : availableCountries[0];
                };

                const defaultCountry = getDefaultCountry();

                const config = {
                    autoPlaceholder: 'polite',
                    dropdownContainer: document.body,
                    geoIpLookup: function(callback) {
                        const cacheKey = 'ipinfo_country_code';
                        const cacheExpiry = 'ipinfo_country_expiry';
                        const cachedCountry = localStorage.getItem(cacheKey);
                        const cachedExpiry = localStorage.getItem(cacheExpiry);
                        const now = new Date().getTime();

                        if (cachedCountry && cachedExpiry && now < parseInt(cachedExpiry)) {
                            const isCountryAvailable = availableCountries.length === 0 || availableCountries.includes(cachedCountry.toLowerCase());
                            callback(isCountryAvailable ? cachedCountry : defaultCountry);
                            return;
                        }

                        fetch('https://ipinfo.io/json', {
                                credentials: 'omit',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(function(response) {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(function(data) {
                                let countryCode = data && data.country ? data.country.toLowerCase() : defaultCountry;

                                const isCountryAvailable = availableCountries.length === 0 || availableCountries.includes(countryCode);

                                if (!isCountryAvailable) {
                                    countryCode = defaultCountry;
                                }

                                if (countryCode && countryCode !== defaultCountry) {
                                    try {
                                        localStorage.setItem(cacheKey, countryCode);
                                        localStorage.setItem(cacheExpiry, (now + 24 * 60 * 60 *
                                            1000).toString());
                                    } catch (e) {
                                        console.warn('Could not cache country code:', e);
                                    }
                                }

                                callback(countryCode);
                            })
                            .catch(function() {
                                callback(defaultCountry);
                            });
                    },
                    initialCountry: 'auto',
                    utilsScript: '{{ asset('vendor/core/core/base/libraries/intl-tel-input/js/utils.js') }}',
                };

                if (availableCountries && availableCountries.length > 0) {
                    config.onlyCountries = availableCountries;
                }

                if (hasCountryCodeSelection) {
                    config.separateDialCode = true;
                    config.nationalMode = false;
                    config.autoHideDialCode = false;
                }

                const iti = window.intlTelInput(element, config);
                element.dataset.itiInitialized = 'true';

                const itiContainer = element.closest('.iti');
                const searchPlaceholder = @json(trans('core/base::forms.search_country'));
                if (itiContainer) {
                    const flagContainer = itiContainer.querySelector('.iti__flag-container');
                    if (flagContainer) {
                        flagContainer.addEventListener('pointerdown', function(e) {
                            if (e.pointerType === 'mouse') {
                                return;
                            }
                            const selectedFlag = itiContainer.querySelector('.iti__selected-flag');
                            if (!selectedFlag) {
                                return;
                            }
                            if (e.cancelable) {
                                e.preventDefault();
                            }
                            e.stopPropagation();
                            setTimeout(function() {
                                if (selectedFlag.getAttribute('aria-expanded') === 'true') {
                                    return;
                                }
                                selectedFlag.click();
                            }, 50);
                        }, { passive: false });

                        flagContainer.addEventListener('click', function() {
                            setTimeout(function() {
                                const countryList = document.querySelector('.iti--container .iti__country-list') ||
                                                   itiContainer.querySelector('.iti__country-list');
                                if (countryList && !countryList.querySelector('.iti__search-container')) {
                                    const searchContainer = document.createElement('li');
                                    searchContainer.className = 'iti__search-container';
                                    const searchInput = document.createElement('input');
                                    searchInput.type = 'text';
                                    searchInput.className = 'iti__search-input';
                                    searchInput.placeholder = searchPlaceholder;
                                    searchInput.autocomplete = 'off';
                                    searchContainer.appendChild(searchInput);
                                    countryList.insertBefore(searchContainer, countryList.firstChild);

                                    searchContainer.addEventListener('click', function(e) {
                                        e.stopPropagation();
                                    });

                                    searchInput.addEventListener('click', function(e) {
                                        e.stopPropagation();
                                    });

                                    searchInput.addEventListener('keydown', function(e) {
                                        e.stopPropagation();
                                    });

                                    searchInput.addEventListener('keyup', function(e) {
                                        e.stopPropagation();
                                    });

                                    let noResultsEl = null;

                                    searchInput.addEventListener('input', function() {
                                        const searchTerm = this.value.toLowerCase().trim();
                                        const countries = countryList.querySelectorAll('.iti__country');
                                        let visibleCount = 0;

                                        countries.forEach(function(country) {
                                            const countryName = country.querySelector('.iti__country-name');
                                            const dialCode = country.querySelector('.iti__dial-code');
                                            const name = countryName ? countryName.textContent.toLowerCase() : '';
                                            const code = dialCode ? dialCode.textContent.toLowerCase() : '';
                                            if (name.includes(searchTerm) || code.includes(searchTerm)) {
                                                country.classList.remove('iti__hidden');
                                                visibleCount++;
                                            } else {
                                                country.classList.add('iti__hidden');
                                            }
                                        });

                                        if (visibleCount === 0) {
                                            if (!noResultsEl) {
                                                noResultsEl = document.createElement('li');
                                                noResultsEl.className = 'iti__no-results';
                                                noResultsEl.textContent = @json(trans('core/base::forms.no_results'));
                                                countryList.appendChild(noResultsEl);
                                            }
                                            noResultsEl.style.display = 'block';
                                        } else if (noResultsEl) {
                                            noResultsEl.style.display = 'none';
                                        }
                                    });

                                    searchInput.focus();
                                }
                            }, 10);
                        });
                    }
                }

                if (hasCountryCodeSelection) {
                    const hiddenFieldId = element.id + '-full';
                    const hiddenField = document.getElementById(hiddenFieldId);

                    if (hiddenField) {
                        const updateHiddenField = function() {
                            const fullNumber = iti.getNumber();
                            const oldValue = hiddenField.value;
                            let newValue = '';

                            if (fullNumber) {
                                newValue = fullNumber;
                            } else if (element.value) {
                                const selectedCountryData = iti.getSelectedCountryData();
                                if (selectedCountryData && selectedCountryData.dialCode) {
                                    newValue = '+' + selectedCountryData.dialCode + element.value
                                        .replace(/\D/g, '');
                                } else {
                                    newValue = element.value;
                                }
                            }

                            hiddenField.value = newValue;

                            if (oldValue !== newValue) {
                                const changeEvent = new Event('change', {
                                    bubbles: true
                                });
                                element.dispatchEvent(changeEvent);
                            }
                        };

                        const initialValue = hiddenField.value || element.value;

                        if (initialValue) {
                            if (initialValue.startsWith('+')) {
                                iti.setNumber(initialValue);
                            } else if (initialValue) {
                                element.value = initialValue;
                            }

                            setTimeout(function() {
                                updateHiddenField();
                            }, 100);
                        }

                        element.addEventListener('countrychange', updateHiddenField);
                        element.addEventListener('input', updateHiddenField);
                        element.addEventListener('blur', updateHiddenField);

                        const form = element.closest('form');
                        if (form) {
                            form.addEventListener('submit', function() {
                                updateHiddenField();
                            });
                        }
                    }
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPhoneNumberFields);
        } else {
            initPhoneNumberFields();
        }

        document.addEventListener('payment-form-reloaded', function() {
            initPhoneNumberFields();
        });

        document.addEventListener('phone-number-field:init', function() {
            initPhoneNumberFields();
        });
    })();
</script>
