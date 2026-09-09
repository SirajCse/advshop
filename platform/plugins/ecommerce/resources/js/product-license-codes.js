$(() => {
    const $container = $('.license-codes-container')

    if (!$container.length) {
        return
    }

    const routes = $container.data('routes')
    const confirmDeleteMessage = $container.data('confirm-delete')
    const confirmBulkDeleteMessage = $container.data('confirm-bulk-delete')

    // Update bulk actions visibility and count
    function updateBulkActions() {
        const selectedCount = $('.license-code-checkbox:checked').length
        const $wrapper = $('.bulk-actions-wrapper')
        const $count = $wrapper.find('.selected-count')

        $count.text(selectedCount)

        if (selectedCount > 0) {
            $wrapper.show()
        } else {
            $wrapper.hide()
        }
    }

    // Select all checkbox
    $('.select-all-checkbox').on('change', function () {
        const isChecked = $(this).prop('checked')
        $('.license-code-checkbox').prop('checked', isChecked)
        updateBulkActions()
    })

    // Individual checkbox
    $(document).on('change', '.license-code-checkbox', function () {
        const totalCheckboxes = $('.license-code-checkbox').length
        const checkedCheckboxes = $('.license-code-checkbox:checked').length
        $('.select-all-checkbox').prop('checked', totalCheckboxes === checkedCheckboxes)
        updateBulkActions()
    })

    // Bulk delete
    $('.btn-bulk-delete').on('click', function () {
        const selectedIds = $('.license-code-checkbox:checked')
            .map(function () {
                return $(this).data('id')
            })
            .get()

        if (selectedIds.length === 0) {
            return
        }

        if (confirm(confirmBulkDeleteMessage)) {
            const $button = $(this)
            Botble.showButtonLoading($button)

            $httpClient
                .make()
                .delete(routes.bulkDelete, { ids: selectedIds })
                .then(({ data }) => {
                    if (data.error) {
                        Botble.showError(data.message)
                    } else {
                        Botble.showSuccess(data.message)
                        location.reload()
                    }
                })
                .catch((error) => {
                    Botble.handleError(error)
                })
                .finally(() => {
                    Botble.hideButtonLoading($button)
                })
        }
    })

    // Add license code
    $('#add-license-code-form').on('submit', function (e) {
        e.preventDefault()

        const $form = $(this)
        const $button = $form.find('button[type="submit"]')

        Botble.showButtonLoading($button)

        $httpClient
            .make()
            .postForm(routes.store, new FormData($form[0]))
            .then(({ data }) => {
                if (data.error) {
                    Botble.showError(data.message)
                } else {
                    Botble.showSuccess(data.message)
                    location.reload()
                }
            })
            .catch((error) => {
                Botble.handleError(error)
            })
            .finally(() => {
                Botble.hideButtonLoading($button)
            })
    })

    // Edit license code - open modal
    $(document).on('click', '.edit-license-code-btn', function () {
        const id = $(this).data('license-code-id')
        const code = $(this).data('license-code')

        $('#edit-license-code-id').val(id)
        $('#edit-license-code').val(code)
        $('#edit-license-code-modal').modal('show')
    })

    // Edit license code - submit form
    $('#edit-license-code-form').on('submit', function (e) {
        e.preventDefault()

        const $form = $(this)
        const $button = $form.find('button[type="submit"]')
        const id = $('#edit-license-code-id').val()

        Botble.showButtonLoading($button)

        $httpClient
            .make()
            .postForm(routes.update.replace('__ID__', id), new FormData($form[0]))
            .then(({ data }) => {
                if (data.error) {
                    Botble.showError(data.message)
                } else {
                    Botble.showSuccess(data.message)
                    location.reload()
                }
            })
            .catch((error) => {
                Botble.handleError(error)
            })
            .finally(() => {
                Botble.hideButtonLoading($button)
            })
    })

    // Delete license code
    $(document).on('click', '.delete-license-code-btn', function () {
        const id = $(this).data('license-code-id')

        if (confirm(confirmDeleteMessage)) {
            $httpClient
                .make()
                .delete(routes.destroy.replace('__ID__', id))
                .then(({ data }) => {
                    if (data.error) {
                        Botble.showError(data.message)
                    } else {
                        Botble.showSuccess(data.message)
                        location.reload()
                    }
                })
                .catch((error) => {
                    Botble.handleError(error)
                })
        }
    })

    // Bulk generate
    $('#bulk-generate-form').on('submit', function (e) {
        e.preventDefault()

        const $form = $(this)
        const $button = $form.find('button[type="submit"]')

        Botble.showButtonLoading($button)

        $httpClient
            .make()
            .postForm(routes.bulkGenerate, new FormData($form[0]))
            .then(({ data }) => {
                if (data.error) {
                    Botble.showError(data.message)
                } else {
                    Botble.showSuccess(data.message)
                    location.reload()
                }
            })
            .catch((error) => {
                Botble.handleError(error)
            })
            .finally(() => {
                Botble.hideButtonLoading($button)
            })
    })

    // Handle format change for custom pattern visibility
    $('#license-code-format').on('change', function () {
        if ($(this).val() === 'custom') {
            $('#custom-pattern-group').show()
        } else {
            $('#custom-pattern-group').hide()
        }
    })
})
