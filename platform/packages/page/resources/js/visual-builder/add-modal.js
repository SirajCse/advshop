const AddShortcodeModal = {
    $listModal: null,
    $formModal: null,
    listModalInstance: null,
    formModalInstance: null,
    selectedShortcode: null,

    init() {
        this.$listModal = $('#vb-shortcode-list-modal')
        this.$formModal = $('#vb-shortcode-modal')

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            this.listModalInstance = new bootstrap.Modal(this.$listModal[0])
            this.formModalInstance = new bootstrap.Modal(this.$formModal[0])
        }

        this.attachEvents()
    },

    attachEvents() {
        const self = this

        $(document).on('change', '[data-bb-toggle="shortcode-item-radio"]', function () {
            $('[data-bb-toggle="vb-shortcode-use"]').prop('disabled', false).removeClass('disabled')
        })

        $(document).on('dblclick', '[data-bb-toggle="vb-shortcode-select"]', function (event) {
            const $currentTarget = $(event.currentTarget)
            self.triggerShortcode($currentTarget)
        })

        $('[data-bb-toggle="vb-shortcode-use"]').on('click', function () {
            const $shortcodeSelected = self.$listModal
                .find('.shortcode-item-input:checked')
                .closest('.shortcode-item-wrapper')

            self.triggerShortcode($shortcodeSelected)

            $('[data-bb-toggle="shortcode-item-radio"]').prop('checked', false)
            $('[data-bb-toggle="vb-shortcode-use"]').prop('disabled', true).addClass('disabled')
        })

        $(document).on('click', '[data-bb-toggle="vb-shortcode-button-use"]', function (event) {
            event.stopPropagation()
            const $shortcodeSelected = $(event.currentTarget).closest('.shortcode-item-wrapper')
            self.triggerShortcode($shortcodeSelected)
        })

        $('[data-bb-toggle="vb-shortcode-add-single"]').on('click', function (event) {
            event.preventDefault()
            self.insertShortcode()
        })

        this.$formModal.on('show.bs.modal', function () {
            if (self.listModalInstance) {
                self.listModalInstance.hide()
            }
            $('[data-bb-toggle="shortcode-item-radio"]').prop('checked', false)
            $('[data-bb-toggle="vb-shortcode-use"]').prop('disabled', true).addClass('disabled')
        })

        $('[data-bb-toggle="shortcode-clear-search"]').on('click', function () {
            const $searchInput = self.$listModal.find('input[name="search"]')
            $searchInput.val('')
            self.filterShortcodes('')
        })

        self.$listModal.find('input[name="search"]').on('input', function () {
            self.filterShortcodes($(this).val())
        })
    },

    show() {
        if (this.listModalInstance) {
            this.listModalInstance.show()
        } else {
            this.$listModal.modal('show')
        }
    },

    triggerShortcode($element) {
        this.loadShortcodeConfig({
            href: $element.data('url'),
            key: $element.data('key'),
            name: $element.data('name'),
            description: $element.data('description'),
        })
    },

    loadShortcodeConfig(params = {}) {
        const { href, key, name, description = null, data = {}, update = false } = params

        this.selectedShortcode = { key, name, description }

        $('.shortcode-admin-config').html('')

        const $addButton = $('.shortcode-modal button[data-bb-toggle="vb-shortcode-add-single"]')
        $addButton.text($addButton.data(update ? 'update-text' : 'add-text'))

        $('.shortcode-modal .modal-title').text(name)

        if (this.formModalInstance) {
            this.formModalInstance.show()
        } else {
            this.$formModal.modal('show')
        }

        const $modalContent = this.$formModal.find('.modal-content')
        if (typeof Botble !== 'undefined' && Botble.showLoading) {
            Botble.showLoading($modalContent)
        }

        $.ajax({
            url: href,
            method: 'POST',
            dataType: 'json',
            data: {
                _token: (window.visualBuilderData || {}).csrfToken,
                ...data,
            },
            success: (response) => {
                $('.shortcode-data-form').trigger('reset')
                $('.shortcode-input-key').val(key)
                $('.shortcode-admin-config').html(response.data)

                if (typeof Botble !== 'undefined') {
                    Botble.hideLoading($modalContent)
                    Botble.initResources()
                    Botble.initMediaIntegrate()
                    Botble.initFieldCollapse()
                }
            },
            error: () => {
                if (typeof Botble !== 'undefined') {
                    Botble.hideLoading($modalContent)
                    Botble.showError('Failed to load shortcode configuration')
                }
            },
        })
    },

    insertShortcode() {
        if (!this.selectedShortcode) return

        const $form = $('.shortcode-modal').find('.shortcode-data-form')
        const formData = $form.serializeObject()
        const attributes = {}
        let content = ''

        $.each(formData, function (name, value) {
            const $element = $form.find('*[name="' + name + '"]')
            const shortcodeAttribute = $element.data('shortcode-attribute')

            if (shortcodeAttribute === 'content') {
                content = value
            } else if (value) {
                name = name.replace('[]', '')
                attributes[name] = value
            }
        })

        const newShortcode = {
            name: this.selectedShortcode.key,
            attributes: attributes,
            content: content || '',
            isSelfClosing: !content,
        }

        VisualBuilderState.addShortcode(newShortcode)

        if (typeof PreviewIframe !== 'undefined') {
            PreviewIframe.reload()
        }

        if (this.formModalInstance) {
            this.formModalInstance.hide()
        } else {
            this.$formModal.modal('hide')
        }
    },

    filterShortcodes(searchTerm) {
        const term = searchTerm.toLowerCase()
        const $items = this.$listModal.find('.shortcode-item-wrapper')
        let visibleCount = 0

        $items.each(function () {
            const $item = $(this)
            const name = $item.data('name').toLowerCase()
            const description = ($item.data('description') || '').toLowerCase()

            if (name.includes(term) || description.includes(term)) {
                $item.closest('.col-xl-3').show()
                visibleCount++
            } else {
                $item.closest('.col-xl-3').hide()
            }
        })

        if (visibleCount === 0) {
            this.$listModal.find('.shortcode-empty').show()
            this.$listModal.find('.shortcode-list').hide()
        } else {
            this.$listModal.find('.shortcode-empty').hide()
            this.$listModal.find('.shortcode-list').show()
        }
    },
}

$.fn.serializeObject = function () {
    const o = {}
    const a = this.serializeArray()

    $.each(a, function () {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]]
            }
            o[this.name].push(this.value || '')
        } else {
            o[this.name] = this.value || ''
        }
    })

    return o
}

window.AddShortcodeModal = AddShortcodeModal
