'use strict'
import forEach from 'lodash/forEach'

class FrontendProductOption {
    constructor() {
        this.priceSale = $('.product-details-content .product-price-sale .js-product-price')
        this.priceOriginal = $('.product-details-content .product-price-original .js-product-price')
        let priceElement = this.priceOriginal
        if (!this.priceSale.hasClass('d-none')) {
            priceElement = this.priceSale
        }
        this.basePrice = parseFloat(priceElement.text().replaceAll('$', ''))
        this.priceElement = priceElement
        this.extraPrice = {}
        this.eventListeners()
        this.restoreOptionsFromUrl()
        this.formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        })
    }

    isInModal($element) {
        return $element.closest('[data-bb-toggle="quick-shop-modal"], .modal').length > 0
    }

    eventListeners() {
        $('.product-option input[type="radio"]').change((e) => {
            const $input = $(e.target)
            const name = $input.attr('name')
            this.extraPrice[name] = parseFloat($input.attr('data-extra-price'))
            this.changeDisplayedPrice()
            if (!this.isInModal($input)) {
                this.updateUrlWithOptions()
            }
        })

        $('.product-option input[type="checkbox"]').change((e) => {
            const $input = $(e.target)
            const name = $input.attr('name')
            const extraPrice = parseFloat($input.attr('data-extra-price'))
            if (typeof this.extraPrice[name] == 'undefined') {
                this.extraPrice[name] = []
            }
            if ($input.is(':checked')) {
                this.extraPrice[name].push(extraPrice)
            } else {
                const index = this.extraPrice[name].indexOf(extraPrice)
                if (index > -1) {
                    this.extraPrice[name].splice(index, 1)
                }
            }
            this.changeDisplayedPrice()
            if (!this.isInModal($input)) {
                this.updateUrlWithOptions()
            }
        })

        $('.product-option select').change((e) => {
            const $select = $(e.target)
            const name = $select.attr('name')
            const $selectedOption = $select.find('option:selected')
            this.extraPrice[name] = parseFloat($selectedOption.attr('data-extra-price') || 0)
            this.changeDisplayedPrice()
            if (!this.isInModal($select)) {
                this.updateUrlWithOptions()
            }
        })

        let fieldTimeout
        $('.product-option input[type="text"]').on('input', (e) => {
            const $input = $(e.target)
            clearTimeout(fieldTimeout)
            fieldTimeout = setTimeout(() => {
                if (!this.isInModal($input)) {
                    this.updateUrlWithOptions()
                }
            }, 500)
        })

        $(window).on('popstate', () => {
            this.restoreOptionsFromUrl()
        })
    }

    changeDisplayedPrice() {
        let extra = 0
        forEach(this.extraPrice, (value) => {
            if (typeof value == 'number') {
                extra = extra + value
            } else if (typeof value == 'object') {
                value.map((sub_value) => {
                    extra = extra + sub_value
                })
            }
        })
    }

    updateUrlWithOptions() {
        const url = new URL(window.location)
        const optionSlugs = {}

        $('.product-option').each((index, element) => {
            const $option = $(element)
            const optionSlug = $option.data('option-slug')

            if (!optionSlug) return

            const $checkedRadio = $option.find('input[type="radio"]:checked')
            if ($checkedRadio.length) {
                const valueSlug = $checkedRadio.data('value-slug')
                if (valueSlug) {
                    optionSlugs[optionSlug] = valueSlug
                }
                return
            }

            const $checkedCheckboxes = $option.find('input[type="checkbox"]:checked')
            if ($checkedCheckboxes.length) {
                const values = []
                $checkedCheckboxes.each((i, cb) => {
                    const valueSlug = $(cb).data('value-slug')
                    if (valueSlug) {
                        values.push(valueSlug)
                    }
                })
                if (values.length) {
                    optionSlugs[optionSlug] = values
                }
                return
            }

            const $select = $option.find('select')
            if ($select.length) {
                const $selectedOption = $select.find('option:selected')
                const valueSlug = $selectedOption.data('value-slug')
                if (valueSlug) {
                    optionSlugs[optionSlug] = valueSlug
                }
                return
            }

            const $textField = $option.find('input[type="text"]')
            if ($textField.length && $textField.val()) {
                optionSlugs[optionSlug] = $textField.val()
            }
        })

        const existingOptionSlugs = []
        $('.product-option').each((index, element) => {
            const slug = $(element).data('option-slug')
            if (slug) existingOptionSlugs.push(slug)
        })
        existingOptionSlugs.forEach((slug) => {
            url.searchParams.delete(slug)
        })

        Object.keys(optionSlugs).forEach((key) => {
            const value = optionSlugs[key]
            if (Array.isArray(value)) {
                url.searchParams.set(key, value.join(','))
            } else {
                url.searchParams.set(key, value)
            }
        })

        if (url.href !== window.location.href) {
            window.history.pushState({ options: optionSlugs }, '', url)
        }

        this.updateClipboardButton(url.href)
    }

    updateClipboardButton(url) {
        const $clipboardBtn = $('[data-bb-toggle="social-sharing-clipboard"]')
        if ($clipboardBtn.length) {
            $clipboardBtn.attr('data-clipboard-text', url)
        }
    }

    restoreOptionsFromUrl() {
        const url = new URL(window.location)
        const params = url.searchParams

        $('.product-option').each((index, element) => {
            const $option = $(element)
            const optionSlug = $option.data('option-slug')

            if (!optionSlug || !params.has(optionSlug)) return

            const paramValue = params.get(optionSlug)

            const $radios = $option.find('input[type="radio"]')
            if ($radios.length) {
                $radios.each((i, radio) => {
                    const $radio = $(radio)
                    if ($radio.data('value-slug') === paramValue) {
                        $radio.prop('checked', true).trigger('change')
                    }
                })
                return
            }

            const $checkboxes = $option.find('input[type="checkbox"]')
            if ($checkboxes.length) {
                const values = paramValue.split(',')
                $checkboxes.each((i, cb) => {
                    const $cb = $(cb)
                    const isChecked = values.includes($cb.data('value-slug'))
                    $cb.prop('checked', isChecked)
                })
                $checkboxes.first().trigger('change')
                return
            }

            const $select = $option.find('select')
            if ($select.length) {
                $select.find('option').each((i, opt) => {
                    const $opt = $(opt)
                    if ($opt.data('value-slug') === paramValue) {
                        $select.val($opt.val()).trigger('change')
                    }
                })
                return
            }

            const $textField = $option.find('input[type="text"]')
            if ($textField.length) {
                $textField.val(paramValue)
            }
        })
    }
}

$(() => {
    if ($('.product-option').length) {
        new FrontendProductOption()
    }
})
