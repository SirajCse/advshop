'use strict'

class PaymentMethodManagement {
    init() {
        this.initSortable()
        this.initDefaultPaymentMethod()

        $('.toggle-payment-item')
            .off('click')
            .on('click', (event) => {
                $(event.currentTarget).closest('tbody').find('.payment-content-item').toggleClass('hidden')

                window.EDITOR = new EditorManagement().init()
                window.EditorManagement = window.EditorManagement || EditorManagement
            })

        $('.disable-payment-item')
            .off('click')
            .on('click', (event) => {
                event.preventDefault()
                let _self = $(event.currentTarget)
                $('#confirm-disable-payment-method-modal').modal('show')
                $('#confirm-disable-payment-method-button').on('click', (event) => {
                    event.preventDefault()

                    $httpClient.make()
                        .withButtonLoading($(event.currentTarget))
                        .post(route('payments.methods.update.status'), {
                            type: _self.closest('form').find('.payment_type').val(),
                        })
                        .then(({ data }) => {
                            if (!data.error) {
                                _self.closest('tbody').find('.payment-name-label-group').addClass('hidden')
                                _self.closest('tbody').find('.edit-payment-item-btn-trigger').addClass('hidden')
                                _self.closest('tbody').find('.save-payment-item-btn-trigger').removeClass('hidden')
                                _self.closest('tbody').find('.btn-text-trigger-update').addClass('hidden')
                                _self.closest('tbody').find('.btn-text-trigger-save').removeClass('hidden')
                                _self.addClass('hidden')
                                $(event.currentTarget).closest('.modal').modal('hide')
                                Botble.showSuccess(data.message)
                            } else {
                                Botble.showError(data.message)
                            }
                        })
                })
            })

        $('.save-payment-item')
            .off('click')
            .on('click', (event) => {
                event.preventDefault()

                const _self = $(event.currentTarget)
                const form = _self.closest('form')

                if (typeof tinymce != 'undefined') {
                    for (let instance in tinymce.editors) {
                        if (tinymce.editors[instance].getContent) {
                            $(`#${instance}`).html(tinymce.editors[instance].getContent())
                        }
                    }
                }

                $httpClient.make()
                    .withButtonLoading(_self)
                    .post(form.prop('action') + window.location.search, form.serialize())
                    .then(({ data }) => {
                        _self.closest('tbody').find('.payment-name-label-group').removeClass('hidden')
                        _self
                            .closest('tbody')
                            .find('.method-name-label')
                            .text(_self.closest('form').find('input.input-name').val())
                        _self.closest('tbody').find('.disable-payment-item').removeClass('hidden')
                        _self.closest('tbody').find('.edit-payment-item-btn-trigger').removeClass('hidden')
                        _self.closest('tbody').find('.save-payment-item-btn-trigger').addClass('hidden')
                        _self.closest('tbody').find('.btn-text-trigger-update').removeClass('hidden')
                        _self.closest('tbody').find('.btn-text-trigger-save').addClass('hidden')
                        Botble.showSuccess(data.message)
                    })
            })
    }

    initSortable() {
        const container = document.getElementById('payment-methods-sortable')

        if (!container || typeof Sortable === 'undefined') {
            return
        }

        new Sortable(container, {
            handle: '.drag-handle',
            draggable: '.payment-method-item',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: () => {
                this.saveSortOrder(container)
            },
        })
    }

    initDefaultPaymentMethod() {
        $(document)
            .off('click', '.set-default-payment-method')
            .on('click', '.set-default-payment-method', (event) => {
                event.preventDefault()

                const button = $(event.currentTarget)
                const card = button.closest('.payment-method-item')
                const type = card.data('payment-type')
                const container = document.getElementById('payment-methods-sortable')

                if (!container || !type) {
                    return
                }

                const url = container.getAttribute('data-sort-order-url')

                $httpClient.make()
                    .withButtonLoading(button)
                    .post(url, { default_payment_method: type })
                    .then(({ data }) => {
                        if (data.error) {
                            Botble.showError(data.message)
                            return
                        }

                        $('.set-default-payment-method').removeClass('text-warning').addClass('text-muted')
                        $('.set-default-payment-method svg use').each(function () {
                            const href = $(this).attr('href') || $(this).attr('xlink:href') || ''
                            if (href.includes('star-filled')) {
                                const newHref = href.replace('star-filled', 'star')
                                $(this).attr('href', newHref)
                                if ($(this).attr('xlink:href')) {
                                    $(this).attr('xlink:href', newHref)
                                }
                            }
                        })

                        button.removeClass('text-muted').addClass('text-warning')
                        button.find('svg use').each(function () {
                            const href = $(this).attr('href') || $(this).attr('xlink:href') || ''
                            if (href.includes('#ti-star')) {
                                const newHref = href.replace('#ti-star', '#ti-star-filled')
                                $(this).attr('href', newHref)
                                if ($(this).attr('xlink:href')) {
                                    $(this).attr('xlink:href', newHref)
                                }
                            }
                        })

                        Botble.showSuccess(data.message)
                    })
            })
    }

    saveSortOrder(container) {
        const items = container.querySelectorAll('.payment-method-item')
        const order = {}

        items.forEach((item, index) => {
            const type = item.getAttribute('data-payment-type')
            if (type) {
                order[type] = index
            }
        })

        const url = container.getAttribute('data-sort-order-url')

        $httpClient.make()
            .post(url, { order })
            .then(({ data }) => {
                if (data.error) {
                    Botble.showError(data.message)
                } else {
                    Botble.showSuccess(data.message)
                }
            })
    }
}

$(() => {
    new PaymentMethodManagement().init()
})
