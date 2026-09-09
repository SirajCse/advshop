$(() => {
    'use strict'

    const loadAjaxCart = (data) => {
        $('.cartmini__area').html(data.cart_mini)
        $('[data-bb-value="cart-count"]').text(data.count)

        const $cartArea = $('.tp-cart-area')

        if ($cartArea.length) {
            $cartArea.replaceWith(data.cart_content)
        }

        if (data.additional_cart_data) {
            Object.keys(data.additional_cart_data).forEach(key => {
                $(`[data-bb-value="${key}"]`).text(data.additional_cart_data[key])
            })
        }

        if (typeof Theme.lazyLoadInstance !== 'undefined') {
            Theme.lazyLoadInstance.update()
        }
    }

    const handleUpdateCart = (element) => {
        let form

        if (element) {
            form = $(element).closest('form')
        } else {
            form = $('form.cart-form')
        }

        $.ajax({
            type: 'POST',
            url: form.prop('action'),
            data: form.serialize(),
            success: ({error, message, data}) => {
                if (error) {
                    Theme.showError(message)
                }

                loadAjaxCart(data)
            },
            error: (error) => Theme.handleError(error),
        })
    }

    /**
     * @param {Array<Number>} data
     * @param {jQuery} element
     */
    window.onBeforeChangeSwatches = (data, element) => {
        const form = element.closest('form')

        if (data) {
            form.find('button[type="submit"]').prop('disabled', true)
            form.find('button[data-bb-toggle="add-to-cart"]').prop('disabled', true)
        }
    }

    $(document)
        .on('click', '[data-bb-toggle="remove-coupon"]', (e) => {
            e.preventDefault()

            const currentTarget = $(e.currentTarget)

            $.ajax({
                url: currentTarget.prop('href'),
                type: 'POST',
                success: ({error, message}) => {
                    if (error) {
                        Theme.showError(message)

                        return
                    }

                    Theme.showSuccess(message)

                    handleUpdateCart()
                },
                error: (error) => Theme.handleError(error),
            })
        })
        .on('click', '[data-bb-toggle="decrease-qty"]', (e) => {
            const $input = $(e.currentTarget).parent().find('input')

            let count = parseInt($input.val()) - 1
            count = count < 1 ? 1 : count
            $input.val(count)
            $input.trigger('change')
        })
        .on('click', '[data-bb-toggle="increase-qty"]', (e) => {
            const $input = $(e.currentTarget).parent().find('input')

            const max = $input.prop('max')

            if (max && parseInt($input.val()) >= parseInt(max)) {
                return
            }

            $input.val(parseInt($input.val()) + 1)
            $input.trigger('change')
        })
        .on('change', '[data-bb-toggle="update-cart"]', (e) => {
            handleUpdateCart(e.currentTarget)
        })
        .on('click', '[data-bb-toggle="change-product-filter-layout"]', (e) => {
            e.preventDefault()

            const currentTarget = $(e.currentTarget)

            currentTarget.addClass('active')
            currentTarget.closest('li').siblings().find('button').removeClass('active')

            $('.bb-product-form-filter').find('[name="layout"]').val(currentTarget.data('value')).trigger('change')
        })
        .on('click', '[data-bb-toggle="copy-coupon"]', async (e) => {
            e.preventDefault()

            const currentTarget = $(e.currentTarget)
            const value = currentTarget.data('value')
            const previousText = currentTarget.find('span').text()

            if (navigator.clipboard) {
                await navigator.clipboard.writeText(value)
            } else {
                const tempInput = document.createElement('input')
                tempInput.value = value
                document.body.appendChild(tempInput)
                tempInput.select()
                document.execCommand('copy')
                document.body.removeChild(tempInput)
            }

            currentTarget.find('span').text(currentTarget.data('copied-message'))

            setTimeout(() => currentTarget.find('span').text(previousText), 2000)
        })
        .on('click', '[data-bb-toggle="scroll-to-review"]', (e) => {
            if ($('.nav-tabs button#nav-review-tab').length) {
                e.preventDefault()

                const $tab = $('.nav-tabs button#nav-review-tab')
                const $container = $('.product-review-container')

                if ($tab.length && $container.length) {
                    $tab.tab('show')

                    $('html, body').animate({
                        scrollTop: $container.offset().top - 100,
                    })
                }
            }
        })
        .on('show.bs.modal', '#product-quick-view-modal', (e) => {
            const modal = $(e.currentTarget)
            const trigger = $(e.relatedTarget)

            $.ajax({
                url: trigger.data('url') || trigger.prop('href'),
                type: 'GET',
                beforeSend: () => {
                    trigger.addClass('btn-loading')
                    modal.find('.modal-content').css('min-height', '40rem').html('<div class="loading-spinner"></div>')
                },
                success: ({error, data}) => {
                    if (error) {
                        return
                    }

                    modal.find('.modal-content').css('min-height', '0').html(data)

                    if (typeof Theme.lazyLoadInstance !== 'undefined') {
                        Theme.lazyLoadInstance.update()
                    }

                    setTimeout(() => {
                        EcommerceApp.initProductGallery(true)
                    }, 100)

                    document.dispatchEvent(new CustomEvent('ecommerce.quick-view.initialized'))
                },
                complete: () => trigger.removeClass('btn-loading'),
            })
        })
        .on('submit', 'form#coupon-form', (e) => {
            e.preventDefault()

            const currentTarget = $(e.currentTarget)
            const button = currentTarget.find('button[type="submit"]')

            $.ajax({
                url: currentTarget.prop('action'),
                type: 'POST',
                data: currentTarget.serialize(),
                beforeSend: () => button.prop('disabled', true).addClass('btn-loading'),
                success: ({error, message}) => {
                    if (error) {
                        Theme.showError(message)

                        return
                    }

                    Theme.showSuccess(message)

                    handleUpdateCart()
                },
                error: (error) => Theme.handleError(error),
                complete: () => button.prop('disabled', false).removeClass('btn-loading'),
            })
        })
        .on('keyup', 'form#coupon-form input', (e) => {
            const currentTarget = $(e.currentTarget)

            currentTarget.closest('form').find('button[type="submit"]').prop('disabled', !currentTarget.val())
        })
        .on('click', '.product-form button[type="submit"]', (e) => {
            e.preventDefault()

            const currentTarget = $(e.currentTarget)

            // Prevent multiple submissions
            if (currentTarget.prop('disabled') || currentTarget.hasClass('btn-loading')) {
                return
            }

            const form = currentTarget.closest('form')
            const data = form.serializeArray()

            if (form.find('input[name="id"]').val() === '') {
                return
            }

            data.push({name: 'checkout', value: currentTarget.prop('name') === 'checkout' ? 1 : 0})

            $.ajax({
                type: 'POST',
                url: form.prop('action'),
                data: data,
                beforeSend: () => {
                    // Only disable and add loading to the clicked button
                    currentTarget.prop('disabled', true).addClass('btn-loading')
                    // Disable other submit buttons without loading animation
                    form.find('button[type="submit"]').not(currentTarget).prop('disabled', true)
                },
                success: ({error, message, data}) => {
                    if (error) {
                        Theme.showError(message)

                        if (data?.next_url !== undefined) {
                            setTimeout(() => {
                                window.location.href = data.next_url
                            }, 500);
                        }

                        return
                    }

                    form.find('input[name="qty"]').val(1)

                    if (data?.next_url !== undefined) {
                        window.location.href = data.next_url
                    } else {
                        loadAjaxCart(data)

                        // Close quick shop modal if it's open
                        const quickShopModal = $('#quick-shop-modal')
                        if (quickShopModal.length && quickShopModal.hasClass('show')) {
                            const modalInstance = bootstrap.Modal.getInstance(quickShopModal[0])
                            if (modalInstance) {
                                modalInstance.hide()
                            }
                        }

                        // Check if auto open mini cart is enabled
                        if (window.themeOptions && window.themeOptions.ecommerce_auto_open_mini_cart === 'yes') {
                            $('.cartmini__area').addClass('cartmini-opened')
                            $('.body-overlay').addClass('opened')
                        }

                        // Dispatch cart added event for other handlers (up-sale refresh, etc.)
                        document.dispatchEvent(
                            new CustomEvent('ecommerce.cart.added', {
                                detail: {
                                    data,
                                    element: currentTarget,
                                    message,
                                    extraData: data.extra_data
                                },
                            })
                        )
                    }
                },
                error: (error) => Theme.handleError(error),
                complete: () => {
                    // Re-enable all submit buttons in the form
                    form.find('button[type="submit"]').prop('disabled', false).removeClass('btn-loading')
                },
            })
        })
        .on('click', '.js-sale-popup-quick-view-button', (e) => {
            e.preventDefault()

            $('#product-quick-view-modal').modal('show', e.currentTarget)
        })
        .on('change', '.tp-shop-top-select select', (e) => {
            const currentTarget = $(e.currentTarget)

            const form = $('.bb-product-form-filter')

            form.find(`input[name="${currentTarget.prop('name')}"]`)
                .val(currentTarget.val())
                .trigger('submit')
        })
        .on('click', '.bb-product-items-wrapper .pagination a', (e) => {
            e.preventDefault()

            const currentTarget = $(e.currentTarget)

            const url = new URL(currentTarget.prop('href'))
            const page = url.searchParams.get('page')

            $('.bb-product-form-filter').find('[name="page"]').val(page).trigger('change')
        })
        .on('submit', 'form.subscribe-form', (e) => {
            e.preventDefault()

            const $form = $(e.currentTarget)
            const $button = $form.find('button[type=submit]')

            $.ajax({
                type: 'POST',
                cache: false,
                url: $form.prop('action'),
                data: new FormData($form[0]),
                contentType: false,
                processData: false,
                beforeSend: () => $button.prop('disabled', true).addClass('btn-loading'),
                success: ({error, message}) => {
                    if (error) {
                        Theme.showError(message)

                        return
                    }

                    const email = $form.find('input[name="email"]').val()

                    $form.find('input').val('')

                    Theme.showSuccess(message)

                    document.dispatchEvent(new CustomEvent('newsletter.subscribed', {
                        detail: { email: email }
                    }))
                },
                error: (error) => {
                    if (typeof refreshRecaptcha !== 'undefined') {
                        refreshRecaptcha()
                    }

                    Theme.handleError(error)
                },
                complete: () => {
                    if (typeof refreshRecaptcha !== 'undefined') {
                        refreshRecaptcha()
                    }

                    $button.prop('disabled', false).removeClass('btn-loading')
                },
            })
        })
        .on('click', '[data-bb-toggle="product-tab"]', (e) => {
            e.preventDefault()

            const currentTarget = $(e.currentTarget)

            const tabPane = currentTarget.closest('.tp-product-area').find('#productTabContent .tab-pane')
            const wrapper = tabPane.closest('.tp-product-area')
            const tooltip = currentTarget.find('span.tp-product-tab-tooltip')

            // Assuming currentTarget, tooltip, wrapper, and tabPane are already defined
            const url = `${currentTarget.closest('#productTab').data('ajax-url')}&type=${currentTarget.data('bb-value')}`;

            fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json', // Requesting JSON response
                    'Accept': 'application/json', // Requesting JSON response
                },
            })
                .then(response => {
                    // Check if the response is okay and parse it as JSON
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(({data}) => {
                    // Update tooltip text and tabPane with the fetched data
                    tooltip.text(data.count);
                    tabPane.html(data.html);

                    // Update lazyLoadInstance if it exists
                    if (typeof Theme.lazyLoadInstance !== 'undefined') {
                        Theme.lazyLoadInstance.update();
                    }
                })
                .catch(error => {
                    // Handle any errors during the fetch
                    Theme.handleError(error);
                })
                .finally(() => {
                    // Remove the loading spinner
                    $('.loading-spinner').remove();
                });

            // Before sending the request, update the tooltip and show a loading spinner
            tooltip.text('...');
            wrapper.append('<div class="loading-spinner"></div>');
        })
        .on('submit', '.contact-form', (e) => {
            e.preventDefault()

            const $form = $(e.currentTarget)
            const $button = $form.find('button[type=submit]')

            $.ajax({
                type: 'POST',
                cache: false,
                url: $form.prop('action'),
                data: new FormData($form[0]),
                contentType: false,
                processData: false,
                beforeSend: () => $button.addClass('button-loading'),
                success: ({error, message}) => {
                    if (!error) {
                        $form[0].reset()
                        Theme.showSuccess(message)
                    } else {
                        Theme.showError(message)
                    }
                },
                error: (error) => Theme.handleError(error),
                complete: () => {
                    if (typeof refreshRecaptcha !== 'undefined') {
                        refreshRecaptcha()
                    }

                    $button.removeClass('button-loading')
                },
            })
        })
        .on('click', '.sticky-actions-button button', (e) => {
            e.preventDefault()

            const currentTarget = $(e.currentTarget)
            const form = $('form.product-form')

            if (currentTarget.prop('name') === 'add-to-cart') {
                form.find('button[type="submit"][name="add-to-cart"]').trigger('click')
            }

            if (currentTarget.prop('name') === 'checkout') {
                form.find('button[type="submit"][name="checkout"]').trigger('click')
            }
        })
        .on('click', '[data-bb-toggle="open-mini-cart"]', (e) => {
            $('[data-bb-toggle="mini-cart-content-slot"]').html('<div class="loading-spinner"></div>')

            $.ajax({
                url: $(e.currentTarget).data('url'),
                type: 'GET',
                success: ({data}) => {
                    $('[data-bb-toggle="mini-cart-content-slot"]').html(data.content)
                    $('[data-bb-toggle="mini-cart-footer-slot"]').html(data.footer)

                    if (typeof Theme.lazyLoadInstance !== 'undefined') {
                        Theme.lazyLoadInstance.update()
                    }
                },
                error: (error) => Theme.handleError(error),
            })
        })

    document.addEventListener('ecommerce.quick-view.initialized', () => {
        const $countDown = $(document).find('[data-countdown]')

        if (! $($countDown).length || ! $.fn.countdown) {
            return
        }

        $countDown.countdown()
    })

    document.addEventListener('ecommerce.cart.added', (e) => {
        const {data, element, message} = e.detail

        loadAjaxCart(data)

        // Check if auto open mini cart is enabled
        if (window.themeOptions && window.themeOptions.ecommerce_auto_open_mini_cart === 'yes') {
            $('.cartmini__area').addClass('cartmini-opened')
            $('.body-overlay').addClass('opened')
        } else {
            Theme.showSuccess(message)
        }
    })

    document.addEventListener('ecommerce.cart.removed', (e) => {
        const {data} = e.detail
        if (data.count === 0) {
            $('.cartmini__area').removeClass('cartmini-opened')
            $('.body-overlay').removeClass('opened')
        }

        loadAjaxCart(data)
    })

    document.addEventListener('ecommerce.wishlist.removed', (e) => {
        const {data, element} = e.detail

        element.closest('tr').remove()

        if (data.count === 0) {
            window.location.reload()
        }
    })

    document.addEventListener('ecommerce.compare.added', (e) => {
        const {element} = e.detail

        if (element.find('span')) {
            element
                .find('span')
                .text(
                    element.hasClass('active')
                        ? element.data('remove-text')
                        : element.data('add-text')
                )
        }
    })

    document.addEventListener('ecommerce.wishlist.added', (e) => {
        const {data, element} = e.detail

        data.added ? element.addClass('active') : element.removeClass('active')

        if (element.find('span')) {
            element
                .find('span')
                .text(data.added ? element.data('remove-text') : element.data('add-text'))
        }
    })

    document.addEventListener('ecommerce.compare.removed', (e) => {
        const {element} = e.detail

        if (element.find('span')) {
            element
                .find('span')
                .text(
                    element.hasClass('active')
                        ? element.data('remove-text')
                        : element.data('add-text')
                )
        }
    })

    document.addEventListener('ecommerce.product-filter.before', () => {
        $('.tp-shop-area > .container, .bb-shop-detail > .container > .bb-shop-tab-content').append(
            '<div class="loading-spinner"></div>'
        )
    })

    document.addEventListener('ecommerce.product-filter.success', (e) => {
        const {data} = e.detail

        // Auto-close filter offcanvas on mobile after filter applied
        $('.bb-filter-offcanvas-area').removeClass('offcanvas-opened')
        $('.body-overlay').removeClass('opened')

        $('.bb-product-items-wrapper').html(data.data)

        if (data.additional) {
            $('.bb-shop-sidebar').replaceWith(data.additional.filters_html)
        }

        $('.tp-shop-top-result p').text(data.message)

        $('html, body').animate({
            scrollTop: $('.tp-shop-main-wrapper').offset().top - 120,
        })
    })

    document.addEventListener('ecommerce.product-filter.completed', () => {
        $('.tp-shop-area > .container, .bb-shop-detail > .container > .bb-shop-tab-content').find('.loading-spinner').remove()
    })

    document.addEventListener('ecommerce.quick-shop.before-send', (e) => {
        const {element, modal} = e.detail

        element.addClass('btn-loading')
        modal.find('.modal-body').css('min-height', '16rem').html('<div class="loading-spinner"></div>')
    })

    document.addEventListener('ecommerce.quick-shop.completed', (e) => {
        const {element, modal} = e.detail

        element.removeClass('btn-loading')
        modal.find('.modal-body').css('min-height', '0')
    })

    if (window.location.hash === '#product-review') {
        $(document).find('[data-bb-toggle="scroll-to-review"]').trigger('click')
    }

    document.addEventListener('shortcode.loaded', () => {
        const $countDown = $(document).find('[data-countdown]')

        if (! $($countDown).length || ! $.fn.countdown) {
            return
        }

        $countDown.countdown()
    })

    // Infinite scroll for products listing
    const initInfiniteScroll = () => {
        const $trigger = $('.bb-infinite-scroll-trigger')

        if (!$trigger.length) {
            return
        }

        let isLoading = false
        let currentPage = parseInt($trigger.data('current-page')) || 1
        let lastPage = parseInt($trigger.data('last-page')) || 1

        const loadMoreProducts = () => {
            if (isLoading || currentPage >= lastPage) {
                return
            }

            const nextPageUrl = $trigger.data('next-page-url')

            if (!nextPageUrl) {
                return
            }

            isLoading = true
            const $loadMoreBtn = $trigger.find('.bb-load-more-btn')
            $loadMoreBtn.addClass('loading').prop('disabled', true)

            $.ajax({
                url: nextPageUrl,
                type: 'GET',
                data: { _: Date.now() },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: (response) => {
                    const $tempContainer = $('<div>').html(response.data || response)
                    const $newProducts = $tempContainer.find('.bb-infinite-products-grid > .col, .bb-infinite-products-list .col-xl-12 > *')

                    if ($newProducts.length) {
                        const $gridContainer = $('.bb-infinite-products-grid')
                        const $listContainer = $('.bb-infinite-products-list .col-xl-12')

                        if ($gridContainer.length) {
                            $gridContainer.append($newProducts)
                        } else if ($listContainer.length) {
                            $listContainer.append($newProducts)
                        }

                        currentPage++
                        $trigger.data('current-page', currentPage)

                        // Update next page URL
                        const $newTrigger = $tempContainer.find('.bb-infinite-scroll-trigger')
                        if ($newTrigger.length) {
                            const newNextPageUrl = $newTrigger.data('next-page-url')
                            $trigger.data('next-page-url', newNextPageUrl)

                            if (currentPage >= lastPage || !newNextPageUrl) {
                                $trigger.find('.bb-load-more-btn').remove()
                            }
                        } else {
                            $trigger.find('.bb-load-more-btn').remove()
                        }

                        if (typeof Theme.lazyLoadInstance !== 'undefined') {
                            Theme.lazyLoadInstance.update()
                        }
                    }
                },
                error: (error) => {
                    console.error('Error loading more products:', error)
                },
                complete: () => {
                    isLoading = false
                    $trigger.find('.bb-load-more-btn').removeClass('loading').prop('disabled', false)
                }
            })
        }

        // Load more on button click
        $(document).on('click', '.bb-load-more-btn', (e) => {
            e.preventDefault()
            loadMoreProducts()
        })

        // Auto-load on scroll
        let scrollTimeout
        $(window).on('scroll', () => {
            if (scrollTimeout) {
                clearTimeout(scrollTimeout)
            }

            scrollTimeout = setTimeout(() => {
                const $trigger = $('.bb-infinite-scroll-trigger')
                if (!$trigger.length || isLoading) {
                    return
                }

                const triggerPosition = $trigger.offset().top
                const scrollPosition = $(window).scrollTop() + $(window).height()

                // Load more when user is 200px from the trigger
                if (scrollPosition > triggerPosition - 200) {
                    loadMoreProducts()
                }
            }, 100)
        })
    }

    initInfiniteScroll()

    // Re-initialize infinite scroll after AJAX filter
    document.addEventListener('ecommerce.product-filter.success', () => {
        setTimeout(() => {
            initInfiniteScroll()
        }, 100)
    })
})
