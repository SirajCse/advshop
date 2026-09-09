import sanitizeHTML from 'sanitize-html'
import Toastify from '../../../../../../core/base/resources/js/base/toast'

const showNotice = (type, message) => {
    const icons = {
        success: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg>',
        error: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>',
    }

    const colors = {
        success: '#437a43',
        error: '#bd362f',
    }

    Toastify({
        text: message,
        icon: icons[type] || '',
        duration: 5000,
        close: true,
        gravity: 'bottom',
        position: 'right',
        offset: { x: 15, y: 15 },
        stopOnFocus: true,
        style: { background: colors[type] || '#437a43' },
        escapeMarkup: false,
        className: 'toastify-' + type,
    }).showToast()
}

export class DiscountManagement {
    init() {
        $(document).on('click', '.btn-open-coupon-form', (event) => {
            event.preventDefault()
            $(document).find('.coupon-wrapper').toggle()
        })

        $('.coupon-wrapper .coupon-code').keypress((event) => {
            if (event.keyCode === 13) {
                $('.apply-coupon-code').trigger('click')
                event.preventDefault()
                event.stopPropagation()
                return false
            }
        })

        // Define targets for both desktop and mobile layouts
        let target = '.checkout-order-info'
        let mobileTarget = '.cart-item-wrapper'
        let couponWrapperTarget = '.coupon-wrapper.mt-2'
        let couponItemTarget = '.checkout__coupon-item'
        let couponSectionTarget = '.checkout__coupon-section'
        let mobileCouponSheetTarget = '#mobile-coupon-sheet .offcanvas-body'
        let mobileCouponButtonTarget = '.mobile-checkout-footer__coupon-btn'

        $(document).on('click', '.apply-coupon-code', (event) => {
            event.preventDefault()

            const currentTarget = $(event.currentTarget)

            $.ajax({
                url: currentTarget.data('url'),
                type: 'POST',
                data: {
                    coupon_code: currentTarget.closest('.coupon-wrapper').find('.coupon-code').val(),
                    token: $('#checkout-token').val(),
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                beforeSend: () => {
                    currentTarget.find('.btn-spinner').remove()
                    currentTarget.html(`<span class="btn-spinner"></span>${currentTarget.html()}`)
                },
                success: ({ error, message }) => {
                    if (!error) {
                        const successMessage = message;
                        // Use a more reliable approach for both desktop and mobile
                        $.ajax({
                            url: window.location.href + '?applied_coupon=1',
                            type: 'GET',
                            success: (response) => {
                                // Extract the target content from the response
                                const tempDiv = document.createElement('div');
                                tempDiv.innerHTML = response;

                                // Update desktop layout if it exists
                                const desktopContent = $(tempDiv).find(target);
                                if (desktopContent.length && $(target).length) {
                                    $(target).html(desktopContent.html());
                                }

                                // Update mobile layout
                                const mobileContent = $(tempDiv).find(mobileTarget);
                                if (mobileContent.length && $(mobileTarget).length) {
                                    $(mobileTarget).html(mobileContent.html());
                                }

                                // Update coupon wrapper section for mobile
                                const couponWrapperContent = $(tempDiv).find(couponWrapperTarget);
                                if (couponWrapperContent.length && $(couponWrapperTarget).length) {
                                    $(couponWrapperTarget).html(couponWrapperContent.html());
                                } else if (couponWrapperContent.length) {
                                    // If coupon wrapper doesn't exist yet, append it after the cart-item-wrapper
                                    $(mobileTarget).after(couponWrapperContent);
                                }

                                // Update coupon items
                                const couponItemsContent = $(tempDiv).find(couponItemTarget);
                                if (couponItemsContent.length) {
                                    // Update each coupon item or the entire section
                                    const couponSection = $(tempDiv).find(couponSectionTarget);
                                    if (couponSection.length && $(couponSectionTarget).length) {
                                        $(couponSectionTarget).html(couponSection.html());
                                    } else {
                                        // Update individual coupon items
                                        $(couponItemTarget).each(function(index) {
                                            if (couponItemsContent[index]) {
                                                $(this).replaceWith(couponItemsContent[index]);
                                            }
                                        });
                                    }
                                }

                                // Update mobile coupon sheet list
                                const mobileCouponSheetContent = $(tempDiv).find(mobileCouponSheetTarget);
                                if (mobileCouponSheetContent.length && $(mobileCouponSheetTarget).length) {
                                    $(mobileCouponSheetTarget).html(mobileCouponSheetContent.html());
                                }

                                // Update mobile coupon button (shows applied state)
                                const mobileCouponButtonContent = $(tempDiv).find(mobileCouponButtonTarget);
                                if (mobileCouponButtonContent.length && $(mobileCouponButtonTarget).length) {
                                    $(mobileCouponButtonTarget).replaceWith(mobileCouponButtonContent.clone());
                                }

                                // If nothing was updated, reload the page
                                if ((!desktopContent.length && !mobileContent.length && !couponWrapperContent.length) ||
                                    ($(target).length === 0 && $(mobileTarget).length === 0)) {
                                    window.location.reload();
                                }

                                // Dispatch event for coupon applied
                                document.dispatchEvent(new CustomEvent('coupon:applied'));

                                // Show success notification
                                if (successMessage) {
                                    showNotice('success', successMessage);
                                }

                                currentTarget.find('.btn-spinner').remove();
                            },
                            error: () => {
                                // Fallback to page reload if AJAX extraction fails
                                window.location.reload();
                            }
                        });
                    } else {
                        $('.coupon-error-msg .text-danger').html(sanitizeHTML(message))
                        showNotice('error', message);
                        currentTarget.find('.btn-spinner').remove()
                    }

                    $('html, body').animate({
                        scrollTop: $('.coupon-wrapper').offset().top
                    });
                },
                error: (data) => {
                    if (typeof data.responseJSON !== 'undefined') {
                        if (data.responseJSON.errors !== 'undefined') {
                            $.each(data.responseJSON.errors, (index, el) => {
                                $.each(el, (key, item) => {
                                    $('.coupon-error-msg .text-danger').text(item)
                                })
                            })
                        } else if (typeof data.responseJSON.message !== 'undefined') {
                            $('.coupon-error-msg .text-danger').text(data.responseJSON.message)
                        }
                    } else {
                        $('.coupon-error-msg .text-danger').text(data.status.text)
                    }
                    currentTarget.find('i').remove()
                },
            })
        })

        $(document).on('click', '.remove-coupon-code', (event) => {
            event.preventDefault()
            let _self = $(event.currentTarget)
            _self.find('.btn-spinner').remove()
            _self.html('<span class="btn-spinner"></span>' + _self.html())

            $.ajax({
                url: _self.data('url'),
                type: 'POST',
                data: {
                    token: $('#checkout-token').val(),
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: (res) => {
                    if (!res.error) {
                        const successMessage = res.message;
                        // Fetch updated page content and update specific parts
                        $.ajax({
                            url: window.location.href,
                            type: 'GET',
                            success: (response) => {
                                const tempDiv = document.createElement('div');
                                tempDiv.innerHTML = response;

                                // Update desktop order info
                                const desktopContent = $(tempDiv).find(target);
                                if (desktopContent.length && $(target).length) {
                                    $(target).html(desktopContent.html());
                                }

                                // Update mobile cart wrapper
                                const mobileContent = $(tempDiv).find(mobileTarget);
                                if (mobileContent.length && $(mobileTarget).length) {
                                    $(mobileTarget).html(mobileContent.html());
                                }

                                // Update coupon wrapper section
                                const couponWrapperContent = $(tempDiv).find(couponWrapperTarget);
                                if (couponWrapperContent.length && $(couponWrapperTarget).length) {
                                    $(couponWrapperTarget).html(couponWrapperContent.html());
                                }

                                // Update coupon section (desktop)
                                const couponSection = $(tempDiv).find(couponSectionTarget);
                                if (couponSection.length && $(couponSectionTarget).length) {
                                    $(couponSectionTarget).replaceWith(couponSection.clone());
                                }

                                // Update mobile coupon sheet content
                                const mobileCouponSheetContent = $(tempDiv).find(mobileCouponSheetTarget);
                                if (mobileCouponSheetContent.length && $(mobileCouponSheetTarget).length) {
                                    $(mobileCouponSheetTarget).html(mobileCouponSheetContent.html());
                                }

                                // Update mobile coupon button in footer
                                const mobileCouponButtonContent = $(tempDiv).find(mobileCouponButtonTarget);
                                if (mobileCouponButtonContent.length && $(mobileCouponButtonTarget).length) {
                                    $(mobileCouponButtonTarget).replaceWith(mobileCouponButtonContent.clone());
                                }

                                // Dispatch event for coupon removed
                                document.dispatchEvent(new CustomEvent('coupon:removed'));

                                // Show success notification
                                if (successMessage) {
                                    showNotice('success', successMessage);
                                }

                                _self.find('.btn-spinner').remove();
                            },
                            error: () => {
                                window.location.reload();
                            }
                        });
                    } else {
                        $('.coupon-error-msg .text-danger').text(res.message)
                        showNotice('error', res.message);
                        _self.find('.btn-spinner').remove()
                    }
                },
                error: (data) => {
                    if (data.responseJSON?.message) {
                        showNotice('error', data.responseJSON.message);
                    }
                    _self.find('.btn-spinner').remove()
                },
            })
        })


        $(document).on('click', '[data-bb-toggle="apply-coupon-code"]', function (e) {
            e.preventDefault();

            const button = $(this);
            const discountCode = button.data('discount-code');
            const originalText = button.text();

            // Show loading state
            button.find('.btn-spinner').remove();
            button.html(`<span class="btn-spinner"></span>${originalText}`);

            // Check if desktop coupon wrapper exists
            const couponWrapper = $(document).find('.coupon-wrapper');
            const hasDesktopCoupon = couponWrapper.length && couponWrapper.find('.coupon-code').length;

            if (hasDesktopCoupon) {
                // Desktop: use existing coupon wrapper
                const applyNewCoupon = () => {
                    couponWrapper.show();
                    couponWrapper.find('.coupon-code').val(discountCode);
                    couponWrapper.find('.apply-coupon-code').trigger('click');
                };

                const hasAppliedCoupon = couponWrapper.find('.remove-coupon-code').length > 0;

                if (hasAppliedCoupon) {
                    $(document).one('coupon:removed', () => {
                        setTimeout(applyNewCoupon, 200);
                    });
                    couponWrapper.find('.remove-coupon-code').trigger('click');
                } else {
                    applyNewCoupon();
                }
            } else {
                // Mobile: make direct AJAX call and update specific parts
                $.ajax({
                    url: $('[data-coupon-apply-url]').data('coupon-apply-url') || '/coupon/apply',
                    type: 'POST',
                    data: {
                        coupon_code: discountCode,
                        token: $('#checkout-token').val(),
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: ({ error, message }) => {
                        if (!error) {
                            const successMessage = message;
                            // Fetch updated page content and update specific parts
                            $.ajax({
                                url: window.location.href + '?applied_coupon=1',
                                type: 'GET',
                                success: (response) => {
                                    const tempDiv = document.createElement('div');
                                    tempDiv.innerHTML = response;

                                    // Update desktop order info
                                    const desktopContent = $(tempDiv).find(target);
                                    if (desktopContent.length && $(target).length) {
                                        $(target).html(desktopContent.html());
                                    }

                                    // Update mobile cart wrapper
                                    const mobileContent = $(tempDiv).find(mobileTarget);
                                    if (mobileContent.length && $(mobileTarget).length) {
                                        $(mobileTarget).html(mobileContent.html());
                                    }

                                    // Update coupon section (desktop)
                                    const couponSection = $(tempDiv).find(couponSectionTarget);
                                    if (couponSection.length && $(couponSectionTarget).length) {
                                        $(couponSectionTarget).replaceWith(couponSection.clone());
                                    }

                                    // Update mobile coupon sheet content
                                    const mobileCouponSheetContent = $(tempDiv).find(mobileCouponSheetTarget);
                                    if (mobileCouponSheetContent.length && $(mobileCouponSheetTarget).length) {
                                        $(mobileCouponSheetTarget).html(mobileCouponSheetContent.html());
                                    }

                                    // Update mobile coupon button in footer
                                    const mobileCouponButtonContent = $(tempDiv).find(mobileCouponButtonTarget);
                                    if (mobileCouponButtonContent.length && $(mobileCouponButtonTarget).length) {
                                        $(mobileCouponButtonTarget).replaceWith(mobileCouponButtonContent.clone());
                                    }

                                    // Dispatch event for coupon applied
                                    document.dispatchEvent(new CustomEvent('coupon:applied'));

                                    // Show success notification
                                    if (successMessage) {
                                        showNotice('success', successMessage);
                                    }

                                    button.find('.btn-spinner').remove();
                                },
                                error: () => {
                                    window.location.reload();
                                }
                            });
                        } else {
                            showNotice('error', message);
                            button.find('.btn-spinner').remove();
                            button.html(originalText);
                        }
                    },
                    error: (data) => {
                        if (data.responseJSON?.message) {
                            showNotice('error', data.responseJSON.message);
                        }
                        button.find('.btn-spinner').remove();
                        button.html(originalText);
                    },
                });
            }
        });

        // Mobile coupon bottom sheet functionality
        this.initMobileCouponSheet();
        this.initBottomSheetGestures();
        this.initTapToSelect();
    }

    initMobileCouponSheet() {
        const sheet = document.getElementById('mobile-coupon-sheet');
        if (!sheet) return;

        // Handle manual coupon entry from mobile sheet
        $(document).on('click', '.mobile-apply-coupon-btn', (event) => {
            event.preventDefault();
            const btn = $(event.currentTarget);
            const input = btn.closest('.mobile-coupon-entry__card').find('.mobile-coupon-input');
            const couponCode = input.val().trim();
            const originalText = btn.text();

            if (!couponCode) {
                input.focus();
                return;
            }

            btn.find('.btn-spinner').remove();
            btn.html(`<span class="btn-spinner"></span>${originalText}`);

            $.ajax({
                url: btn.data('url'),
                type: 'POST',
                data: {
                    coupon_code: couponCode,
                    token: $('#checkout-token').val(),
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: ({ error, message }) => {
                    if (!error) {
                        const successMessage = message;
                        // Fetch updated page content and update specific parts
                        $.ajax({
                            url: window.location.href + '?applied_coupon=1',
                            type: 'GET',
                            success: (response) => {
                                const tempDiv = document.createElement('div');
                                tempDiv.innerHTML = response;

                                // Update order info
                                const orderInfo = $(tempDiv).find('.checkout-order-info');
                                if (orderInfo.length && $('.checkout-order-info').length) {
                                    $('.checkout-order-info').html(orderInfo.html());
                                }

                                // Update mobile cart wrapper
                                const cartWrapper = $(tempDiv).find('.cart-item-wrapper');
                                if (cartWrapper.length && $('.cart-item-wrapper').length) {
                                    $('.cart-item-wrapper').html(cartWrapper.html());
                                }

                                // Update mobile coupon sheet content
                                const sheetContent = $(tempDiv).find('#mobile-coupon-sheet .offcanvas-body');
                                if (sheetContent.length && $('#mobile-coupon-sheet .offcanvas-body').length) {
                                    $('#mobile-coupon-sheet .offcanvas-body').html(sheetContent.html());
                                }

                                // Update mobile coupon button in footer
                                const couponBtn = $(tempDiv).find('.mobile-checkout-footer__coupon-btn');
                                if (couponBtn.length && $('.mobile-checkout-footer__coupon-btn').length) {
                                    $('.mobile-checkout-footer__coupon-btn').replaceWith(couponBtn.clone());
                                }

                                // Dispatch event
                                document.dispatchEvent(new CustomEvent('coupon:applied'));

                                if (successMessage) {
                                    showNotice('success', successMessage);
                                }

                                btn.find('.btn-spinner').remove();
                            },
                            error: () => {
                                window.location.reload();
                            }
                        });
                    } else {
                        showNotice('error', message);
                        btn.find('.btn-spinner').remove();
                        btn.html(originalText);
                    }
                },
                error: (data) => {
                    if (data.responseJSON?.message) {
                        showNotice('error', data.responseJSON.message);
                    }
                    btn.find('.btn-spinner').remove();
                    btn.html(originalText);
                },
            });
        });

        // Handle enter key in mobile coupon input
        $(document).on('keypress', '.mobile-coupon-input', (event) => {
            if (event.keyCode === 13) {
                event.preventDefault();
                $(event.currentTarget).closest('.input-group').find('.mobile-apply-coupon-btn').trigger('click');
            }
        });

        // Handle coupon application from mobile sheet
        $(document).on('click', '.mobile-coupon-item [data-bb-toggle="apply-coupon-code"]', function() {
            // Close sheet after delay for coupon switching
            setTimeout(() => {
                if (window.bootstrap?.Offcanvas) {
                    const offcanvas = window.bootstrap.Offcanvas.getInstance(sheet);
                    if (offcanvas) offcanvas.hide();
                }
            }, 1000);
        });

        // Handle coupon removal from mobile sheet
        $(document).on('click', '.mobile-coupon-item .remove-coupon-code', function() {
            setTimeout(() => {
                if (window.bootstrap?.Offcanvas) {
                    const offcanvas = window.bootstrap.Offcanvas.getInstance(sheet);
                    if (offcanvas) offcanvas.hide();
                }
            }, 800);
        });

        // Close sheet after coupon operations complete
        $(document).on('coupon:applied coupon:removed', () => {
            if (sheet.classList.contains('show')) {
                setTimeout(() => {
                    if (window.bootstrap?.Offcanvas) {
                        const offcanvas = window.bootstrap.Offcanvas.getInstance(sheet);
                        if (offcanvas) offcanvas.hide();
                    }
                }, 300);
            }
        });

        // Prevent body scroll when sheet is open
        sheet.addEventListener('shown.bs.offcanvas', () => {
            document.body.style.overflow = 'hidden';
        });

        sheet.addEventListener('hidden.bs.offcanvas', () => {
            document.body.style.overflow = '';
        });
    }

    initBottomSheetGestures() {
        const sheet = document.getElementById('mobile-coupon-sheet');
        if (!sheet) return;

        const dragHandle = sheet.querySelector('.mobile-coupon-sheet__drag-handle');
        const content = sheet.querySelector('.offcanvas-body');

        let startY = 0;
        let currentY = 0;
        let startTime = 0;
        let isDragging = false;

        const DISMISS_THRESHOLD = 150; // px
        const VELOCITY_THRESHOLD = 500; // px/s

        const onStart = (e) => {
            const isHandle = dragHandle && dragHandle.contains(e.target);
            const isAtTop = content && content.scrollTop === 0;

            if (!isHandle && !isAtTop) return;

            isDragging = true;
            startY = e.touches ? e.touches[0].clientY : e.clientY;
            startTime = Date.now();
            sheet.classList.add('dragging');
            sheet.style.transition = 'none';
        };

        const onMove = (e) => {
            if (!isDragging) return;

            currentY = e.touches ? e.touches[0].clientY : e.clientY;
            const deltaY = currentY - startY;

            if (deltaY > 0) {
                sheet.style.transform = `translateY(${deltaY}px)`;
                e.preventDefault();
            }
        };

        const onEnd = () => {
            if (!isDragging) return;

            isDragging = false;
            sheet.classList.remove('dragging');
            sheet.style.transition = '';

            const deltaY = currentY - startY;
            const deltaTime = Date.now() - startTime;
            const velocity = Math.abs(deltaY / deltaTime) * 1000;

            if (deltaY > DISMISS_THRESHOLD || velocity > VELOCITY_THRESHOLD) {
                if (window.bootstrap?.Offcanvas) {
                    const offcanvas = window.bootstrap.Offcanvas.getInstance(sheet);
                    if (offcanvas) offcanvas.hide();
                }
            } else {
                sheet.style.transform = '';
            }
        };

        sheet.addEventListener('touchstart', onStart, { passive: true });
        sheet.addEventListener('touchmove', onMove, { passive: false });
        sheet.addEventListener('touchend', onEnd);
        sheet.addEventListener('touchcancel', onEnd);

        sheet.addEventListener('hidden.bs.offcanvas', () => {
            sheet.style.transform = '';
        });
    }

    initTapToSelect() {
        $(document).on('click', '.mobile-coupon-item', function(e) {
            if ($(e.target).closest('button').length) return;

            const item = $(this);
            const applyBtn = item.find('[data-bb-toggle="apply-coupon-code"]');
            const removeBtn = item.find('.remove-coupon-code');

            if (item.hasClass('active') && removeBtn.length) {
                removeBtn.trigger('click');
            } else if (applyBtn.length) {
                applyBtn.trigger('click');
            }
        });

        $(document).on('touchstart', '.mobile-coupon-item', function() {
            $(this).addClass('touch-active');
        });

        $(document).on('touchend touchcancel', '.mobile-coupon-item', function() {
            $(this).removeClass('touch-active');
        });
    }
}
