class ShipmentManagement {
    init() {
        $(document).on('click', '[data-bb-toggle="update-shipping-status"]', () => {
            $('#update-shipping-status-modal').modal('show')
        })

        $(document).on('click', '[data-bb-toggle="update-shipping-cod-status"]', () => {
            $('#update-shipping-cod-status-modal').modal('show')
        })

        $(document).on('click', '#confirm-update-shipping-status-button', (event) => {
            event.preventDefault()

            const _self = $(event.currentTarget)
            const form = _self.closest('.modal-dialog').find('form')

            $httpClient
                .make()
                .withButtonLoading(_self)
                .post(form.prop('action'), form.serialize())
                .then(({ data }) => {
                    if (!data.error) {
                        Botble.showSuccess(data.message)
                        _self.closest('.modal').modal('hide')

                        const $pageBody = $('.page-body')
                        if ($pageBody.length) {
                            $pageBody.load(`${window.location.href} .page-body > *`)
                        } else {
                            window.location.reload()
                        }
                    } else {
                        Botble.showError(data.message)
                    }
                })
        })

        this.handleShipmentInfoForm()
    }

    handleShipmentInfoForm() {
        const form = $('#botble-ecommerce-forms-shipment-info-form')

        if (!form.length) {
            return
        }

        form.on('submit', (event) => {
            event.preventDefault()

            const submitButton = form.find('button[type="submit"]')

            $httpClient
                .make()
                .withButtonLoading(submitButton)
                .post(form.prop('action'), form.serialize())
                .then(({ data }) => {
                    if (!data.error) {
                        Botble.showSuccess(data.message)
                    } else {
                        Botble.showError(data.message)
                    }
                })
        })
    }
}

$(() => {
    new ShipmentManagement().init()
})
