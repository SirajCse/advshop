class Location {
    static showError(message) {
        if (typeof Botble !== 'undefined' && Botble.showError) {
            Botble.showError(message)
        } else if (typeof Theme !== 'undefined' && Theme.showError) {
            Theme.showError(message)
        } else if (typeof toastr !== 'undefined') {
            toastr.error(message)
        } else {
            console.error(message)
        }
    }

    static initAjaxSelect2($el) {
        if (!jQuery().select2) {
            return
        }

        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy')
        }

        const type = $el.data('type')
        const url = $el.data('url')
        const placeholder = $el.find('option:first').text() || 'Select...'

        let options = {
            width: '100%',
            placeholder: placeholder,
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    const data = {
                        term: params.term || '',
                        page: params.page || 1,
                    }

                    if (type === 'state') {
                        data.country_id = $el.data('country-id') || ''
                    } else if (type === 'city') {
                        data.state_id = $el.data('state-id') || ''
                        data.country_id = $el.data('country-id') || ''
                    }

                    return data
                },
                processResults: function (response, params) {
                    params.page = params.page || 1

                    const items = response.data?.data || response.data || []
                    const results = []

                    if (params.page === 1 && !params.term) {
                        results.push({ id: '', text: placeholder })
                    }

                    $.each(items, function (index, item) {
                        if (item.id) {
                            results.push({
                                id: item.id,
                                text: item.name,
                            })
                        }
                    })

                    return {
                        results: results,
                        pagination: {
                            more: response.data?.pagination?.more || false,
                        },
                    }
                },
                cache: true,
            },
        }

        let parent = $el.closest('div[data-select2-dropdown-parent]') || $el.closest('.modal')
        if (parent.length) {
            options.dropdownParent = parent
        }

        $el.select2(options)
    }

    static refreshSelect2($el) {
        if (jQuery().select2 && $el.hasClass('select2-hidden-accessible')) {
            if ($el.hasClass('select-location-ajax')) {
                Location.initAjaxSelect2($el)
            } else if ($el.hasClass('select-search-location')) {
                $el.select2('destroy')
                Location.initSelect2($el)
            }
        }
    }

    static initSelect2($el) {
        if (!jQuery().select2 || !$el.hasClass('select-search-location')) {
            return
        }

        if ($el.hasClass('select2-hidden-accessible')) {
            return
        }

        const placeholder = $el.find('option:first').text() || 'Select...'

        let options = {
            width: '100%',
            placeholder: placeholder,
            allowClear: false,
            minimumResultsForSearch: 0,
        }

        let parent = $el.closest('div[data-select2-dropdown-parent]') || $el.closest('.modal')
        if (parent.length) {
            options.dropdownParent = parent
        }

        $el.select2(options)
    }

    static getStates($el, countryId, $button = null) {
        $.ajax({
            url: $el.data('url'),
            data: {
                country_id: countryId,
            },
            type: 'GET',
            beforeSend: () => {
                $button && $button.prop('disabled', true)
            },
            success: (res) => {
                if (res.error) {
                    Location.showError(res.message)
                } else {
                    let options = ''
                    $.each(res.data, (index, item) => {
                        options += '<option value="' + (item.id || '') + '">' + item.name + '</option>'
                    })

                    $el.html(options)
                    Location.refreshSelect2($el)
                }
            },
            complete: () => {
                $button && $button.prop('disabled', false)
            },
        })
    }

    static getCities($el, stateId, $button = null, countryId = null) {
        $.ajax({
            url: $el.data('url'),
            data: {
                state_id: stateId,
                country_id: countryId,
            },
            type: 'GET',
            beforeSend: () => {
                $button && $button.prop('disabled', true)
            },
            success: (res) => {
                if (res.error) {
                    Location.showError(res.message)
                } else {
                    let options = ''
                    $.each(res.data, (index, item) => {
                        options += '<option value="' + (item.id || '') + '">' + item.name + '</option>'
                    })

                    $el.html(options)
                    Location.refreshSelect2($el)
                    $el.trigger('change')
                }
            },
            complete: () => {
                $button && $button.prop('disabled', false)
            },
        })
    }

    init() {
        const country = 'select[data-type="country"]'
        const state = 'select[data-type="state"]'
        const city = 'select[data-type="city"]'

        function initLocationSelects() {
            if (!jQuery().select2) {
                return
            }

            $(document).find('select.select-search-location[data-type]').each(function (index, el) {
                Location.initSelect2($(el))
            })

            $(document).find('select.select-location-ajax[data-type]').each(function (index, el) {
                Location.initAjaxSelect2($(el))
            })
        }

        initLocationSelects()

        $(document).on('change', country, function (e) {
            e.preventDefault()

            const $parent = getParent($(e.currentTarget))
            const $state = $parent.find(state)
            const $city = $parent.find(city)
            const countryId = $(e.currentTarget).val()

            if ($state.hasClass('select-location-ajax')) {
                $state.data('country-id', countryId)
                $state.val(null).trigger('change')
                $state.find('option:not(:first)').remove()
                Location.initAjaxSelect2($state)

                $city.data('country-id', countryId)
                $city.data('state-id', '')
                $city.val(null).trigger('change')
                $city.find('option:not(:first)').remove()
                Location.initAjaxSelect2($city)
            } else {
                $state.find('option:not([value=""]):not([value="0"])').remove()
                $city.find('option:not([value=""]):not([value="0"])').remove()

                Location.refreshSelect2($state)
                Location.refreshSelect2($city)

                const $button = $(e.currentTarget).closest('form').find('button[type=submit], input[type=submit]')

                if (countryId) {
                    if ($state.length) {
                        Location.getStates($state, countryId, $button)
                        Location.getCities($city, null, $button, countryId)
                    } else {
                        Location.getCities($city, null, $button, countryId)
                    }
                }
            }
        })

        $(document).on('change', state, function (e) {
            e.preventDefault()

            const $parent = getParent($(e.currentTarget))
            const $city = $parent.find(city)
            const stateId = $(e.currentTarget).val()

            if (!$city.length) {
                return
            }

            if ($city.hasClass('select-location-ajax')) {
                $city.data('state-id', stateId)
                $city.val(null).trigger('change')
                $city.find('option:not(:first)').remove()
                Location.initAjaxSelect2($city)
            } else {
                $city.find('option:not([value=""]):not([value="0"])').remove()
                Location.refreshSelect2($city)

                const $button = $(e.currentTarget).closest('form').find('button[type=submit], input[type=submit]')

                if (stateId) {
                    Location.getCities($city, stateId, $button)
                } else {
                    const countryId = $parent.find(country).val()
                    Location.getCities($city, null, $button, countryId)
                }
            }
        })

        function getParent($el) {
            let $parent = $(document)
            let formParent = $el.data('form-parent')
            if (formParent && $(formParent).length) {
                $parent = $(formParent)
            }

            return $parent
        }
    }
}

$(() => {
    new Location().init()
})
