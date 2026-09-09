export class CheckoutAddress {
    init() {
        $(document).on('change', '#address_id', (event) => {
            if ($(event.currentTarget).val() !== 'new') {
                $('.address-item-selected')
                    .removeClass('d-none')
                    .html(
                        $(`.list-available-address .address-item-wrapper[data-id=${$(event.currentTarget).val()}]`).html()
                    )
                $('.address-form-wrapper').addClass('d-none')
            } else {
                $('.address-item-selected').addClass('d-none')
                $('.address-form-wrapper').removeClass('d-none')
            }
        })

        $(document).on('click', '#create_account', (event) => {
            if ($(event.currentTarget).is(':checked')) {
                $('.password-group').removeClass('d-none').fadeIn()
            } else {
                $('.password-group').addClass('d-none').fadeOut()
            }
        })

        $(document).on('click', '#billing_address_same_as_shipping_address', (event) => {
            if ($(event.currentTarget).is(':checked')) {
                $('.billing-address-form-wrapper').addClass('d-none').fadeOut()
            } else {
                $('.billing-address-form-wrapper').removeClass('d-none').fadeIn()
            }
        })

        $(document).on('click', '#add_billing_info_checkbox', (event) => {
            if ($(event.currentTarget).is(':checked')) {
                $('.billing-address-form-wrapper').removeClass('d-none').fadeIn()
                $('#billing_address_same_as_shipping_address_hidden').val('0')
            } else {
                $('.billing-address-form-wrapper').addClass('d-none').fadeOut()
                $('#billing_address_same_as_shipping_address_hidden').val('1')
            }
        })

        $(document).on('change', '#billing_address_id', (event) => {
            if ($(event.currentTarget).val() === '') {
                $('.billing-address-form-wrapper').removeClass('d-none').fadeIn()
                $('#billing_address_same_as_shipping_address_select').val('0')
            } else {
                $('.billing-address-form-wrapper').addClass('d-none').fadeOut()
                $('#billing_address_same_as_shipping_address_select').val('1')
            }
        })
    }
}
