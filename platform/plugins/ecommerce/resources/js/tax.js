$(() => {
    const spinner = `<div class='w-100 text-center py-3'><div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
    </div></div>`
    const taxRuleTable = 'ecommerce-tax-rule-table'

    // Tax Rule Modal
    const $ruleModal = $('.create-tax-rule-form-modal')
    const $ruleModalBody = $ruleModal.find('.modal-body')
    const $ruleModalTitle = $ruleModal.find('.modal-title strong')

    // Tax Modal
    const $taxModal = $('.create-tax-form-modal')
    const $taxModalBody = $taxModal.find('.modal-body')
    const $taxModalTitle = $taxModal.find('.modal-title strong')

    const resetRuleModal = () => {
        $ruleModalBody.html(spinner)
        $ruleModalTitle.text('...')
    }

    const setRuleModal = (res) => {
        $ruleModalBody.html(res.data.html)
        $ruleModalTitle.text(res.message || '...')
    }

    const resetTaxModal = () => {
        $taxModalBody.html(spinner)
        $taxModalTitle.text('...')
    }

    const setTaxModal = (res) => {
        $taxModalBody.html(res.data.html)
        $taxModalTitle.text(res.message || '...')
    }

    const refreshPage = () => {
        // Refresh tax rule table if exists (in tax edit page)
        if (window.LaravelDataTables && window.LaravelDataTables[taxRuleTable]) {
            LaravelDataTables[taxRuleTable].draw()
        } else {
            // On settings page with cards, reload the page
            window.location.reload()
        }
    }

    $ruleModal.on('show.bs.modal', function () {
        resetRuleModal()
    })

    $taxModal.on('show.bs.modal', function () {
        resetTaxModal()
    })

    // Handle create tax button
    $(document).on('click', '.btn-create-tax', function (e) {
        e.preventDefault()
        const $this = $(e.currentTarget)
        $taxModal.modal('show')

        $.ajax({
            url: $this.data('href'),
            success: (res) => {
                if (res.error == false) {
                    setTaxModal(res)
                    Botble.initResources()
                } else {
                    Botble.showError(res.message)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
        })
    })

    // Handle edit tax button in card UI
    $(document).on('click', '.btn-edit-tax', function (e) {
        e.preventDefault()
        const $this = $(e.currentTarget)
        $taxModal.modal('show')

        $.ajax({
            url: $this.prop('href'),
            success: (res) => {
                if (res.error == false) {
                    setTaxModal(res)
                    Botble.initResources()
                } else {
                    Botble.showError(res.message)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
        })
    })

    // Handle tax form submit
    $(document).on('submit', '#ecommerce-tax-form', function (e) {
        e.preventDefault()
        const $this = $(e.currentTarget)

        $.ajax({
            url: $this.prop('action'),
            method: 'POST',
            data: $this.serializeArray(),
            success: (res) => {
                if (res.error == false) {
                    $taxModal.modal('hide')
                    Botble.showSuccess(res.message)
                    refreshPage()
                } else {
                    Botble.showError(res.message)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
        })
    })

    // Handle create tax rule button (both in tax rule table and card UI)
    $(document)
        .off('click', '.create-tax-rule-item')
        .on('click', '.create-tax-rule-item', function (e) {
            e.preventDefault()
            const $this = $(e.currentTarget)
            $ruleModal.modal('show')

            // Get URL from data-href attribute or href attribute
            let url = $this.find('[data-action=create]').data('href') || $this.attr('href')

            $.ajax({
                url: url,
                success: (res) => {
                    if (res.error == false) {
                        setRuleModal(res)
                        Botble.initResources()
                    } else {
                        Botble.showError(res.message)
                    }
                },
                error: (res) => {
                    Botble.handleError(res)
                },
            })
        })

    // Handle edit button in tax rule table (tax edit page)
    $(document).on('click', '#' + taxRuleTable + ' .btn-edit-item', function (e) {
        e.preventDefault()
        const $this = $(e.currentTarget)
        $ruleModal.modal('show')

        $.ajax({
            url: $this.prop('href'),
            success: (res) => {
                if (res.error == false) {
                    setRuleModal(res)
                    Botble.initResources()
                } else {
                    Botble.showError(res.message)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
        })
    })

    // Handle edit button in card UI (tax settings page)
    $(document).on('click', '.btn-edit-tax-rule', function (e) {
        e.preventDefault()
        const $this = $(e.currentTarget)
        $ruleModal.modal('show')

        $.ajax({
            url: $this.prop('href'),
            success: (res) => {
                if (res.error == false) {
                    setRuleModal(res)
                    Botble.initResources()
                } else {
                    Botble.showError(res.message)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
        })
    })

    // Handle delete tax rule button in card UI
    $(document).on('click', '.btn-delete-tax-rule', function (e) {
        e.preventDefault()
        const $this = $(e.currentTarget)
        const url = $this.data('url')

        if (!confirm('Are you sure you want to delete this tax rule?')) {
            return
        }

        $.ajax({
            url: url,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: (res) => {
                if (res.error == false) {
                    Botble.showSuccess(res.message)
                    refreshPage()
                } else {
                    Botble.showError(res.message)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
        })
    })

    // Handle set default tax button
    $(document).on('click', '.btn-set-default-tax', function (e) {
        e.preventDefault()
        const $this = $(e.currentTarget)
        const url = $this.data('url')

        $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: (res) => {
                if (res.error == false) {
                    Botble.showSuccess(res.message)
                    refreshPage()
                } else {
                    Botble.showError(res.message)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
        })
    })

    // Handle delete tax button in card UI
    $(document).on('click', '.btn-delete-tax', function (e) {
        e.preventDefault()
        const $this = $(e.currentTarget)
        const url = $this.data('url')

        if (!confirm('Are you sure you want to delete this tax?')) {
            return
        }

        $.ajax({
            url: url,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: (res) => {
                if (res.error == false) {
                    Botble.showSuccess(res.message)
                    refreshPage()
                } else {
                    Botble.showError(res.message)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
        })
    })

    $(document).on('submit', '#ecommerce-tax-rule-form', function (e) {
        e.preventDefault()
        const $this = $(e.currentTarget)

        $.ajax({
            url: $this.prop('action'),
            method: 'POST',
            data: $this.serializeArray(),
            success: (res) => {
                if (res.error == false) {
                    $ruleModal.modal('hide')
                    Botble.showSuccess(res.message)
                    refreshPage()
                } else {
                    Botble.showError(res.message)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
        })
    })
})
