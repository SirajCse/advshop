$(() => {
    const $productLabelBox = $('.box-search-advance')

    if (!$productLabelBox.length) {
        return
    }

    $(document).on('click', '.box-search-advance .list-search-data .selectable-item', (event) => {
        event.preventDefault()

        const $item = $(event.currentTarget)
        const $box = $item.closest('.box-search-advance')
        const $input = $box.find('input[name="label_products"]')

        const productId = $item.data('id')
        const existingIds = $input.val() ? $input.val().split(',').map((id) => parseInt(id.trim())).filter(Boolean) : []

        if (existingIds.includes(productId)) {
            $item.closest('.card').hide()
            return
        }

        existingIds.push(productId)
        $input.val(existingIds.join(','))

        const template = $('#selected_product_label_template').html()
        const productHtml = template
            .replace(/__id__/g, $item.data('id'))
            .replace(/__name__/g, $item.data('name'))
            .replace(/__url__/g, $item.data('url'))
            .replace(/__image__/g, $item.data('image'))
            .replace(/__attributes__/g, $item.find('a span').text() || '')

        const $list = $box.find('.list-selected-products')
        $list.show().append(productHtml)

        $item.closest('.card').hide()
    })

    $(document).on('click', '[data-bb-toggle="product-search-advanced"]', (event) => {
        const $searchInput = $(event.currentTarget)
        const $card = $searchInput.closest('.box-search-advance').find('.card')

        $card.show().addClass('active')

        if ($card.find('.card-body').length === 0) {
            Botble.showLoading($card)

            $.ajax({
                url: $searchInput.data('bb-target'),
                type: 'GET',
                success: (res) => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        $card.html(res.data)
                    }
                },
                error: (data) => {
                    Botble.handleError(data)
                },
                complete: () => {
                    Botble.hideLoading($card)
                },
            })
        }
    })

    let searchTimeout = null
    $(document).on('keyup', '[data-bb-toggle="product-search-advanced"]', (event) => {
        const $searchInput = $(event.currentTarget)
        const $card = $searchInput.closest('.box-search-advance').find('.card')

        clearTimeout(searchTimeout)

        searchTimeout = setTimeout(() => {
            Botble.showLoading($card)

            $.ajax({
                url: `${$searchInput.data('bb-target')}?keyword=${$searchInput.val()}`,
                type: 'GET',
                success: (res) => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        $card.html(res.data)
                    }
                },
                error: (data) => {
                    Botble.handleError(data)
                },
                complete: () => {
                    Botble.hideLoading($card)
                },
            })
        }, 300)
    })

    $(document).on('click', '.box-search-advance .page-link', (event) => {
        event.preventDefault()

        const $link = $(event.currentTarget)
        const $box = $link.closest('.box-search-advance')
        const $searchInput = $box.find('[data-bb-toggle="product-search-advanced"]')
        const $card = $box.find('.card')

        if ($link.closest('.page-item').hasClass('disabled')) {
            return
        }

        Botble.showLoading($card)

        $.ajax({
            url: `${$link.prop('href')}&keyword=${$searchInput.val()}`,
            type: 'GET',
            success: (res) => {
                if (res.error) {
                    Botble.showError(res.message)
                } else {
                    $card.html(res.data)
                }
            },
            error: (data) => {
                Botble.handleError(data)
            },
            complete: () => {
                Botble.hideLoading($card)
            },
        })
    })

    $(document).on('click', (event) => {
        const $target = $(event.target)

        if (!$target.closest('.box-search-advance').length) {
            $('.box-search-advance .card').hide()
        }
    })

    $(document).on('click', '[data-bb-toggle="product-delete-item"]', (event) => {
        event.preventDefault()

        const $btn = $(event.currentTarget)
        const $box = $btn.closest('.box-search-advance')
        const $input = $box.find('input[name="label_products"]')
        const productId = $btn.data('bb-target')

        const existingIds = $input.val().split(',').map((id) => parseInt(id.trim())).filter(Boolean)
        const newIds = existingIds.filter((id) => id !== productId)
        $input.val(newIds.join(','))

        const $listItem = $btn.closest('.list-group-item')
        const $list = $listItem.closest('.list-group')

        $listItem.remove()

        if ($list.find('.list-group-item').length === 0) {
            $box.find('.list-selected-products').hide()
        }
    })
})
