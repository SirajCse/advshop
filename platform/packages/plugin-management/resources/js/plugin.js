class PluginManagement {
    init() {
        $(document).on('click', '.btn-trigger-remove-plugin', (event) => {
            event.preventDefault()

            $('#confirm-remove-plugin-button').data('url', $(event.currentTarget).data('url'))
            $('#remove-plugin-modal').modal('show')
        })

        $(document).on('click', '#confirm-remove-plugin-button', (event) => {
            event.preventDefault()

            const _self = $(event.currentTarget)

            $httpClient
                .make()
                .withButtonLoading(_self)
                .delete(_self.data('url'))
                .then(({ data }) => {
                    Botble.showSuccess(data.message)
                    setTimeout(() => window.location.reload(), 1000)
                })
                .finally(() => $('#remove-plugin-modal').modal('hide'))
        })

        $(document).on('click', '.btn-trigger-update-plugin', (event) => {
            event.preventDefault()

            const currentTarget = $(event.currentTarget)
            const url = currentTarget.data('update-url')

            currentTarget.prop('disabled', true)

            $httpClient
                .make()
                .withButtonLoading(currentTarget)
                .post(url)
                .then(({ data }) => {
                    Botble.showSuccess(data.message)

                    localStorage.removeItem('plugin_update_check_time')
                    localStorage.removeItem('plugin_update_data')

                    setTimeout(() => window.location.reload(), 2000)
                })
                .finally(() => currentTarget.prop('disabled', false))
        })

        $(document).on('click', '.btn-trigger-change-status', async (event) => {
            event.preventDefault()

            const _self = $(event.currentTarget)

            const pluginName = _self.data('plugin')

            const changeStatusUrl = _self.data('change-status-url')

            if (_self.data('status') === 1) {
                Botble.showButtonLoading(_self)
                await this.activateOrDeactivatePlugin(changeStatusUrl)
                Botble.hideButtonLoading(_self)
                return
            }

            $httpClient
                .makeWithoutErrorHandler()
                .withButtonLoading(_self)
                .post(_self.data('check-requirement-url'))
                .then(() => this.activateOrDeactivatePlugin(changeStatusUrl))
                .catch((e) => {
                    const { data, message } = e.response.data

                    if (data && data.existing_plugins_on_marketplace) {
                        const $modal = $('#confirm-install-plugin-modal')
                        $modal.find('.modal-body #requirement-message').html(message)
                        $modal.find('input[name="plugin_name"]').val(pluginName)
                        $modal.find('input[name="ids"]').val(data.existing_plugins_on_marketplace)
                        $modal.modal('show')

                        return
                    }

                    Botble.showError(message)
                })
        })

        if ($('button[data-check-update]').length) {
            this.checkUpdate()
        }

        this.handleFilters()
    }

    handleFilters() {
        const $searchInput = $('[data-bb-toggle="change-search"]')
        const urlParams = new URLSearchParams(window.location.search)
        const savedSearch = urlParams.get('search') || ''
        const savedStatus = urlParams.get('status') || ''
        const defaultStatus = $('input[data-bb-toggle="change-filter-plugin-status"]').first().val()

        if (savedSearch) {
            $searchInput.val(savedSearch)
        }

        if (savedStatus) {
            const $radio = $(`input[data-bb-toggle="change-filter-plugin-status"][value="${savedStatus}"]`)
            if ($radio.length) {
                $('input[data-bb-toggle="change-filter-plugin-status"]').prop('checked', false)
                $radio.prop('checked', true)
            }

            const $dropdownItem = $(`button[data-bb-toggle="change-filter-plugin-status"][data-value="${savedStatus}"]`)
            if ($dropdownItem.length) {
                $('button[data-bb-toggle="change-filter-plugin-status"]').removeClass('active')
                $dropdownItem.addClass('active')
                $('[data-bb-toggle="status-filter-label"]').text($dropdownItem.text())
            }
        }

        let search = $searchInput.val().toLowerCase()
        let status = $('input[data-bb-toggle="change-filter-plugin-status"]:checked').val()

        $('button[data-bb-toggle="change-filter-plugin-status"]').each((index, element) => {
            const itemStatus = $(element).data('value') || $(element).val()
            const $pluginItems =
                itemStatus === 'all' ? $('.plugin-item') : $(`.plugin-item[data-status="${itemStatus}"]`)
            $(`[data-bb-toggle="plugins-count"][data-status="${itemStatus}"]`).text($pluginItems.length)
        })

        const updateUrlParams = () => {
            const url = new URL(window.location.href)

            if (search) {
                url.searchParams.set('search', search)
            } else {
                url.searchParams.delete('search')
            }

            if (status && status !== defaultStatus) {
                url.searchParams.set('status', status)
            } else {
                url.searchParams.delete('status')
            }

            window.history.replaceState({}, '', url)
        }

        const applyFilters = () => {
            const $pluginItems = $('.plugin-item')

            $pluginItems.each((index, element) => {
                const $element = $(element)
                const name = $element.data('name').toLowerCase()
                const description = $element.data('description').toLowerCase()
                const author = $element.data('author').toLowerCase()

                const nameMatch = name.includes(search)
                const authorMatch = author.includes(search)
                const descriptionMatch = description.includes(search)
                const statusMatch =
                    status === 'all' ||
                    $element.data('status') === status ||
                    (status === 'updates-available' && $element.data('available-for-updates'))

                if ((nameMatch || descriptionMatch || authorMatch) && statusMatch) {
                    $element.show()
                } else {
                    $element.hide()
                }
            })

            const $visiblePluginItems = $('.plugin-item:visible')

            if ($visiblePluginItems.length === 0) {
                $('.empty').show()
            } else {
                $('.empty').hide()
            }
        }

        $(document).on('keyup', '[data-bb-toggle="change-search"]', (event) => {
            event.preventDefault()

            search = $(event.currentTarget).val().toLowerCase()
            applyFilters()
            updateUrlParams()
        })

        $(document).on('change', 'input[data-bb-toggle="change-filter-plugin-status"]', (event) => {
            status = $(event.currentTarget).val()
            applyFilters()
            updateUrlParams()
        })

        $(document).on('click', 'button[data-bb-toggle="change-filter-plugin-status"]', (event) => {
            const newValue = $(event.target).data('value')
            $('[data-bb-toggle="status-filter-label"]').text($(event.target).text())

            $('.dropdown-item').removeClass('active')

            $(event.target).addClass('active')

            status = newValue
            applyFilters()
            updateUrlParams()
        })

        if (savedSearch || savedStatus) {
            applyFilters()
        }
    }

    checkUpdate() {
        // Check if we should make the update check request
        const shouldCheckUpdate = () => {
            const lastCheckTime = localStorage.getItem('plugin_update_check_time')
            if (!lastCheckTime) {
                return true
            }

            // Call once every 15 minutes (900000 ms)
            const fifteenMinutesInMs = 15 * 60 * 1000
            return Date.now() - parseInt(lastCheckTime) > fifteenMinutesInMs
        }

        // Try to get cached update data
        const cachedUpdateData = localStorage.getItem('plugin_update_data')

        if (cachedUpdateData && !shouldCheckUpdate()) {
            try {
                const data = JSON.parse(cachedUpdateData)
                this.processUpdateData(data)
                return
            } catch (e) {
                // If there's an error parsing the cached data, proceed with the request
            }
        }

        if (!shouldCheckUpdate()) {
            return
        }

        $httpClient
            .make()
            .post($('button[data-check-update]').data('check-update-url'))
            .then(({ data }) => {
                // Store the current time as the last check time
                localStorage.setItem('plugin_update_check_time', Date.now().toString())

                if (!data.data) {
                    localStorage.removeItem('plugin_update_data')
                    return
                }

                // Store the update data
                localStorage.setItem('plugin_update_data', JSON.stringify(data.data))

                this.processUpdateData(data.data)
            })
            .catch(() => {
                // Even on error, we've made the request, so store the time
                localStorage.setItem('plugin_update_check_time', Date.now().toString())
            })
    }

    processUpdateData(data) {
        if (!data) {
            return
        }

        Object.keys(data).forEach((key) => {
            const plugin = data[key]

            const $button = $(`button[data-check-update="${plugin.name}"]`)

            const url = $button.data('update-url').replace('__id__', plugin.id)

            $button.data('update-url', url).show()

            const $parent = $button.closest('.plugin-item')

            $parent.data('available-for-updates', true).trigger('change')

            $('[data-bb-toggle="plugins-count"][data-status="updates-available"]').text(Object.keys(data).length)
        })
    }

    async activateOrDeactivatePlugin(url, reload = true) {
        return $httpClient
            .make()
            .put(url)
            .then(({ data }) => {
                Botble.showSuccess(data.message)

                if (reload) {
                    setTimeout(() => window.location.reload(), 1000)
                }
            })
    }
}

$(() => {
    new PluginManagement().init()
})
