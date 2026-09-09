/***************************************************
==================== JS INDEX ======================
****************************************************
01. PreLoader Js
02. Mobile Menu Js
03. Common Js
04. Menu Controls JS
05. Offcanvas Js
06. Search Js
07. cartmini Js
08. filter
09. Body overlay Js
10. Sticky Header Js
11. Theme Settings Js
12. Nice Select Js
13. Smooth Scroll Js
14. Slider Activation Area Start
15. Masonary Js
16. Wow Js
17. Counter Js
18. InHover Active Js
19. Line Animation Js
20. Video Play Js
21. Password Toggle Js
****************************************************/

// Import slider autoplay helper
import './slider-autoplay-helper.js'
// Import Owl Carousel initialization
import './owl-carousel-init.js'

$(() => {
    'use strict'

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').prop('content'),
        },
    })

    window.Theme = window.Theme || {}

    window.Theme.isRtl = () => {
        return document.body.getAttribute('dir') === 'rtl'
    }

    const windowOn = $(window)
    ////////////////////////////////////////////////////
    windowOn.on('load', function () {
        $('#loading').fadeOut(200)
    })

    ////////////////////////////////////////////////////
    // 02. Mobile Menu Js
    $('#mobile-menu').meanmenu({
        meanMenuContainer: '.mobile-menu',
        meanScreenWidth: '991',
        meanExpand: [
            `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6" /></svg>`,
        ],
    })

    ////////////////////////////////////////////////////
    // 03. Common Js

    let initCommonJS = () => {
        const windowWidth = $(window).width()

        $('[data-background]').each(function () {
            const $element = $(this)

            let backgroundImage = $element.data('background')

            if (windowWidth > 768 && windowWidth < 1200) {
                if ($element.data('tablet-background')) {
                    backgroundImage = $element.data('tablet-background')
                }
            } else if (windowWidth <= 768) {
                if ($element.data('mobile-background')) {
                    backgroundImage = $element.data('mobile-background')
                }
            }

            if (backgroundImage) {
                $element.css({ 'background-image': 'url("' + encodeURI(backgroundImage) + '")' })
            }
        })

        $('[data-width]').each(function () {
            $(this).css('width', $(this).data('width'))
        })

        $('[data-bg-color]').each(function () {
            $(this).css('background-color', $(this).data('bg-color'))
        })

        $('[data-text-color]').each(function () {
            $(this).css('color', $(this).data('text-color'))
        })
    }

    initCommonJS()

    const $menuStyle3 = $('.main-menu.menu-style-3 > nav > ul')

    if ($menuStyle3.length > 0) {
        $menuStyle3.append(`<li id="marker" class="tp-menu-line"></li>`)
    }

    if ($('#tp-offcanvas-lang-toggle').length > 0) {
        window.addEventListener('click', function (e) {
            if (document.getElementById('tp-offcanvas-lang-toggle').contains(e.target)) {
                $('.tp-lang-list').toggleClass('tp-lang-list-open')
            } else {
                $('.tp-lang-list').removeClass('tp-lang-list-open')
            }
        })
    }

    if ($('#tp-offcanvas-currency-toggle').length > 0) {
        window.addEventListener('click', function (e) {
            if (document.getElementById('tp-offcanvas-currency-toggle').contains(e.target)) {
                $('.tp-currency-list').toggleClass('tp-currency-list-open')
            } else {
                $('.tp-currency-list').removeClass('tp-currency-list-open')
            }
        })
    }

    // for header language
    if ($('#tp-header-lang-toggle').length > 0) {
        window.addEventListener('click', function (e) {
            if (document.getElementById('tp-header-lang-toggle').contains(e.target)) {
                $('.tp-header-lang ul').toggleClass('tp-lang-list-open')
            } else {
                $('.tp-header-lang ul').removeClass('tp-lang-list-open')
            }
        })
    }

    // for header currency
    if ($('#tp-header-currency-toggle').length > 0) {
        window.addEventListener('click', function (e) {
            if (document.getElementById('tp-header-currency-toggle').contains(e.target)) {
                $('.tp-header-currency ul').toggleClass('tp-currency-list-open')
            } else {
                $('.tp-header-currency ul').removeClass('tp-currency-list-open')
            }
        })
    }

    // for header setting
    if ($('#tp-header-setting-toggle').length > 0) {
        window.addEventListener('click', function (e) {
            if (document.getElementById('tp-header-setting-toggle').contains(e.target)) {
                $('.tp-header-setting ul').toggleClass('tp-setting-list-open')
            } else {
                $('.tp-header-setting ul').removeClass('tp-setting-list-open')
            }
        })
    }

    $('.tp-hamburger-toggle').on('click', function () {
        $('.tp-header-side-menu').slideToggle('tp-header-side-menu')
    })

    const initMobileCategoriesDropdown = () => {
        if ($('.tp-category-menu-content').length && $('.tp-category-mobile-menu').length) {
            let navContent = document.querySelector('.tp-category-menu-content').outerHTML
            let mobileNavContainer = document.querySelector('.tp-category-mobile-menu')
            mobileNavContainer.innerHTML = navContent

            $('.tp-offcanvas-category-toggle').off('click').on('click', function () {
                $(this).siblings().find('nav').slideToggle()
            })

            let arrow = $('.tp-category-mobile-menu .has-dropdown > a')

            arrow.each(function () {
                let self = $(this)
                let arrowBtn = document.createElement('button')
                arrowBtn.title = 'toggle button'
                arrowBtn.classList.add('dropdown-toggle-btn')
                arrowBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6" /></svg>`

                self.append(function () {
                    return arrowBtn
                })

                self.find('button').off('click').on('click', function (e) {
                    e.preventDefault()
                    let self = $(this)
                    self.toggleClass('dropdown-opened')
                    self.parent().toggleClass('expanded')
                    self.parent().parent().addClass('dropdown-opened').siblings().removeClass('dropdown-opened')
                    self.parent().parent().children('.tp-submenu').slideToggle()
                })
            })
        }
    }

    ////////////////////////////////////////////////////
    // 04. Menu Controls JS
    document.addEventListener('ecommerce.categories-dropdown.success', () => {
        initMobileCategoriesDropdown()
    })

    initMobileCategoriesDropdown()

    if ($('.tp-main-menu-content').length && $('.tp-main-menu-mobile').length) {
        let navContent = document.querySelector('.tp-main-menu-content').outerHTML
        let mobileNavContainer = document.querySelector('.tp-main-menu-mobile')
        mobileNavContainer.innerHTML = navContent

        let arrow = $('.tp-main-menu-mobile .has-dropdown > a')

        arrow.each(function () {
            let self = $(this)
            let arrowBtn = document.createElement('button')
            arrowBtn.title = 'toggle button'
            arrowBtn.classList.add('dropdown-toggle-btn')
            arrowBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6" /></svg>`

            self.append(function () {
                return arrowBtn
            })

            self.find('button').on('click', function (e) {
                e.preventDefault()
                let self = $(this)
                self.toggleClass('dropdown-opened')
                self.parent().toggleClass('expanded')
                self.parent().parent().addClass('dropdown-opened').siblings().removeClass('dropdown-opened')
                self.parent().parent().children('.tp-submenu').slideToggle()
            })
        })
    }

    ////////////////////////////////////////////////////
    // 05. Offcanvas Js
    $('.tp-offcanvas-open-btn').on('click', function () {
        $('.offcanvas__area').addClass('offcanvas-opened')
        $('.body-overlay').addClass('opened')
    })
    $('.offcanvas-close-btn').on('click', function () {
        $('.offcanvas__area').removeClass('offcanvas-opened')
        $('.body-overlay').removeClass('opened')
    })

    // Profile Menu Panel Js
    $('.profile-menu-toggle').on('click', function () {
        $('.profile-menu-panel').addClass('profile-menu-panel-opened')
        $('.body-overlay').addClass('opened')
    })
    $('.profile-menu-panel__close-btn').on('click', function () {
        $('.profile-menu-panel').removeClass('profile-menu-panel-opened')
        $('.body-overlay').removeClass('opened')
    })

    ////////////////////////////////////////////////////
    // 06. Search Js
    $('.tp-search-open-btn').on('click', function () {
        $('.tp-search-area').addClass('opened')
        $('.body-overlay').addClass('opened')
    })
    $('.tp-search-close-btn').on('click', function () {
        $('.tp-search-area').removeClass('opened')
        $('.body-overlay').removeClass('opened')
    })

    ////////////////////////////////////////////////////
    $(document)
        .on('click', '.cartmini-open-btn', function () {
            $('.cartmini__area').addClass('cartmini-opened')
            $('.body-overlay').addClass('opened')
        })
        .on('click', '.cartmini-close-btn', function () {
            $('.cartmini__area').removeClass('cartmini-opened')
            $('.body-overlay').removeClass('opened')
        })
        .on('click', '.body-overlay', function () {
            $('.offcanvas__area').removeClass('offcanvas-opened')
            $('.tp-search-area').removeClass('opened')
            $('.cartmini__area').removeClass('cartmini-opened')
            $('.profile-menu-panel').removeClass('profile-menu-panel-opened')
            $('.body-overlay').removeClass('opened')
        })

    ////////////////////////////////////////////////////
    // 10. Sticky Header Js
    windowOn.on('scroll', function () {
        canStickyHeader('#header-sticky')
        canStickyHeader('#header-sticky-2')
    })

    const canStickyHeader = (element) => {
        const width = window.innerWidth
        const scroll = $(window).scrollTop()
        const header = $(element)

        if (
            !!(
                (width > 991 && header.data('sticky') !== undefined) ||
                (width < 992 && header.data('mobile-sticky') !== undefined)
            )
        ) {
            if (scroll < 100) {
                header.removeClass(header.prop('id'))
            } else {
                header.addClass(header.prop('id'))
            }
        }
    }

    windowOn.on('scroll', function () {
        const scroll = $(window).scrollTop()
        if (scroll < 100) {
            $('.tp-side-menu-5').removeClass('sticky-active')
        } else {
            $('.tp-side-menu-5').addClass('sticky-active')
        }
    })

    if ($.fn.niceSelect) {
        $('[data-nice-select]').niceSelect()
    }

    function back_to_top() {
        const btn = $('#back_to_top')
        const btn_wrapper = $('.back-to-top-wrapper')

        windowOn.scroll(function () {
            if (windowOn.scrollTop() > 300) {
                btn_wrapper.addClass('back-to-top-btn-show')
            } else {
                btn_wrapper.removeClass('back-to-top-btn-show')
            }
        })

        btn.on('click', function (e) {
            e.preventDefault()
            $('html, body').animate({ scrollTop: 0 }, '300')
        })
    }
    back_to_top()

    ////////////////////////////////////////////////////

    const initSlick = () => {
        const $sliderActive4 = $('.tp-slider-active-4')

        if (! $sliderActive4.length) {
            return
        }

        $sliderActive4.slick({
            infinite: true,
            autoplay: !!$sliderActive4.data('autoplay'),
            autoplaySpeed: $sliderActive4.data('autoplay-speed') || 5000,
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            fade: true,
            centerMode: true,
            prevArrow: `<button type="button" class="tp-slider-3-button-prev"><svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
		   <path d="M1.00073 6.99989L15 6.99989" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		   <path d="M6.64648 1.5L1.00011 6.99954L6.64648 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>`,
            nextArrow: `<button type="button" class="tp-slider-3-button-next"><svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M14.9993 6.99989L1 6.99989" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		<path d="M9.35352 1.5L14.9999 6.99954L9.35352 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg></button>`,
            asNavFor: '.tp-slider-nav-active',
            appendArrows: $('.tp-slider-arrow-4'),
        })

        $('.tp-slider-nav-active').slick({
            infinite: true,
            slidesToShow: 3,
            slidesToScroll: 1,
            vertical: true,
            asNavFor: '.tp-slider-active-4',
            dots: false,
            arrows: false,
            centerMode: false,
            focusOnSelect: true,
        })
    }

    const isRTL = Theme.isRtl()

    const initSwiperSlider = (element, options) => {
        const $element = $(element)

        if (! $element.length) {
            return
        }

        options = options || {}
        options.rtl = isRTL

        if ($element.data('autoplay')) {
            options.autoplay = {
                delay: $element.data('autoplay-speed') || 5000,
            }
        }

        options.loop = false

        if ($element.data('loop')) {
            options.loop = $element.data('loop')
        }

        if ($element.data('items')) {
            options.slidesPerView = $element.data('items')
        }

        return new Swiper(element, options)
    }

    const initSwiper = () => {
        // Simple sliders are now handled by Owl Carousel, skip these
        // initSwiperSlider('.tp-slider-active', { ... })
        // initSwiperSlider('.tp-slider-active-2', { ... })
        // initSwiperSlider('.tp-slider-active-3', { ... })
        // initSwiperSlider('.tp-slider-active-5', { ... })

        initSwiperSlider('.tp-gallery-slider', {
            slidesPerView: 5,
            spaceBetween: 20,
            loop: false,
            breakpoints: {
                1200: {
                    slidesPerView: 5,
                },
                992: {
                    slidesPerView: 4,
                },
                768: {
                    slidesPerView: 3,
                },
                0: {
                    slidesPerView: 2,
                },
            },
        })

        SliderAutoplayHelper.initSliderWithAutoplayAndItems('.tp-product-categories-slider', {
            slidesPerView: 5,
            loop: false,
            observer: true,
            spaceBetween: 20,
            breakpoints: {
                1200: {
                    slidesPerView: 5,
                },
                992: {
                    slidesPerView: 4,
                },
                768: {
                    slidesPerView: 3,
                },
                576: {
                    slidesPerView: 2,
                },
                0: {
                    slidesPerView: 2,
                },
            },
        }, initSwiperSlider, 'items')

        initSwiperSlider('.tp-blog-main-slider-active', {
            slidesPerView: 3,
            spaceBetween: 20,
            loop: false,
            autoplay: {
                delay: 4000,
            },
            // Navigation arrows
            navigation: {
                nextEl: '.tp-blog-main-slider-button-next',
                prevEl: '.tp-blog-main-slider-button-prev',
            },
            pagination: {
                el: '.tp-blog-main-slider-dot',
                clickable: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                },
            },
            breakpoints: {
                1200: {
                    slidesPerView: 3,
                },
                992: {
                    slidesPerView: 2,
                },
                768: {
                    slidesPerView: 2,
                },
                576: {
                    slidesPerView: 1,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        })




        initSwiperSlider('.tp-slider-nav-actives', {
            slidesPerView: 3,
            spaceBetween: 20,
            loop: false,
            direction: 'vertical',
        })

        initSwiperSlider('.tp-slider-active-4s', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: false,
            effect: 'fade',
            // Navigation arrows
            navigation: {
                nextEl: '.tp-slider-3-button-next',
                prevEl: '.tp-slider-3-button-prev',
            },
            pagination: {
                el: '.tp-slider-3-dot',
                clickable: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                },
            },
        })

        initSwiperSlider('.tp-slider-nav-actives', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: false,
            effect: 'fade',
            // Navigation arrows
            navigation: {
                nextEl: '.tp-slider-3-button-next',
                prevEl: '.tp-slider-3-button-prev',
            },
            pagination: {
                el: '.tp-slider-3-dot',
                clickable: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                },
            },
        })

        $('.tp-product-offer-slider-active').each(function (index, element) {
            const $element = $(element)

            initSwiperSlider(element, {
                slidesPerView: 4,
                spaceBetween: 20,
                loop: false,
                pagination: {
                    el: '.tp-deals-slider-dot',
                    clickable: true,
                    renderBullet: function(index, className) {
                        return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                    },
                },
                breakpoints: {
                    1200: {
                        slidesPerView: $element.data('items-per-view') || 4,
                    },
                    992: {
                        slidesPerView: 3,
                    },
                    420: {
                        slidesPerView: 2,
                    },
                    375: {
                        slidesPerView: 2,
                    },
                    0: {
                        slidesPerView: 1,
                    },
                },
            })
        })

        $('.tp-product-arrival-active').each(function (index, element) {
            const $element = $(element)
            const sliderId = $element.data('slider-id') || $element.attr('id')

            if (!sliderId) {
                console.warn('Slider element missing unique ID:', element)
                return
            }

            // Read autoplay settings from data attributes
            const isAutoplay = $element.data('autoplay') === true || $element.data('autoplay') === 'true'
            const autoplaySpeed = parseInt($element.data('autoplay-speed')) || 3000
            const isLoop = $element.data('loop') !== false && $element.data('loop') !== 'false'

            const swiperConfig = {
                slidesPerView: 4,
                spaceBetween: 30,
                loop: isLoop,
                pagination: {
                    el: `.tp-arrival-dot-${sliderId}`,
                    clickable: true,
                    renderBullet: function (index, className) {
                        return `<span class="${className}"><button>${index + 1}</button></span>`
                    },
                },
                // Navigation arrows
                navigation: {
                    nextEl: `.tp-arrival-next-${sliderId}`,
                    prevEl: `.tp-arrival-prev-${sliderId}`,
                },
                breakpoints: {
                    1200: {
                        slidesPerView: $element.data('items-per-view') || 4,
                    },
                    992: {
                        slidesPerView: 3,
                    },
                    768: {
                        slidesPerView: 2,
                    },
                    420: {
                        slidesPerView: 2,
                    },
                    375: {
                        slidesPerView: 2,
                    },
                    0: {
                        slidesPerView: 1,
                    },
                },
            }

            // Add autoplay configuration if enabled
            if (isAutoplay) {
                swiperConfig.autoplay = {
                    delay: autoplaySpeed,
                    disableOnInteraction: false,
                }
            }

            initSwiperSlider(element, swiperConfig)
        })

        SliderAutoplayHelper.initSliderWithAutoplay('.tp-product-banner-slider-active', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: false,
            effect: 'fade',
            pagination: {
                el: '.tp-product-banner-slider-dot',
                clickable: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                },
            },
        }, initSwiperSlider)

        initSwiperSlider('.tp-product-gadget-banner-slider-active', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: false,
            effect: 'fade',
            pagination: {
                el: '.tp-product-gadget-banner-slider-dot',
                clickable: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                },
            },
        })

        initSwiperSlider('.tp-category-slider-active-2', {
            slidesPerView: 5,
            spaceBetween: 20,
            loop: false,
            rtl: Theme.isRtl(),
            enteredSlides: false,
            pagination: {
                el: '.tp-category-slider-dot',
                clickable: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                },
            },
            // Navigation arrows
            navigation: {
                nextEl: '.tp-category-slider-button-next',
                prevEl: '.tp-category-slider-button-prev',
            },
            scrollbar: {
                el: '.swiper-scrollbar',
                draggable: true,
                dragClass: 'tp-swiper-scrollbar-drag',
                snapOnRelease: true,
            },
            breakpoints: {
                1200: {
                    slidesPerView: 5,
                },
                992: {
                    slidesPerView: 4,
                },
                768: {
                    slidesPerView: 3,
                },
                576: {
                    slidesPerView: 2,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        })

        initSwiperSlider('.tp-featured-slider-active', {
            slidesPerView: $('.tp-featured-slider-active').data('item-per-row') || 3,
            spaceBetween: 10,
            loop: false,
            enteredSlides: false,
            pagination: {
                el: '.tp-featured-slider-dot',
                clickable: true,
                renderBullet: function (index, className) {
                    return `<span class="${className}"><button>${index + 1}</button></span>`
                },
            },
            // Navigation arrows
            navigation: {
                nextEl: '.tp-featured-slider-button-next',
                prevEl: '.tp-featured-slider-button-prev',
            },

            breakpoints: {
                1200: {
                    slidesPerView: 3,
                },
                992: {
                    slidesPerView: 3,
                },
                768: {
                    slidesPerView: 2,
                },
                576: {
                    slidesPerView: 1,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        })

        $('.tp-product-related-slider-active').each(function(index, element) {
            const itemsPerView = $(element).data('items-per-view') || 4

            initSwiperSlider(element, {
                slidesPerView: itemsPerView,
                spaceBetween: 24,
                loop: false,
                enteredSlides: false,
                pagination: {
                    el: '.tp-related-slider-dot',
                    clickable: true,
                    renderBullet: function (index, className) {
                        return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                    },
                },
                // Navigation arrows
                navigation: {
                    nextEl: '.tp-related-slider-button-next',
                    prevEl: '.tp-related-slider-button-prev',
                },

                scrollbar: {
                    el: '.tp-related-swiper-scrollbar',
                    draggable: true,
                    dragClass: 'tp-swiper-scrollbar-drag',
                    snapOnRelease: true,
                },

                breakpoints: {
                    1200: {
                        slidesPerView: itemsPerView,
                    },
                    992: {
                        slidesPerView: itemsPerView - 1,
                    },
                    768: {
                        slidesPerView: 2,
                    },
                    576: {
                        slidesPerView: 2,
                    },
                    0: {
                        slidesPerView: 2,
                        spaceBetween: 10,
                    },
                },
            })
        })

        initSwiperSlider('.tp-product-cross-sale-slider-active', {
            slidesPerView: 4,
            spaceBetween: 24,
            loop: false,
            enteredSlides: false,
            scrollbar: {
                el: '.tp-cross-sale-swiper-scrollbar',
                draggable: true,
                dragClass: 'tp-swiper-scrollbar-drag',
                snapOnRelease: true,
            },

            breakpoints: {
                0: {
                    slidesPerView: 2,
                },
                576: {
                    slidesPerView: 3,
                },
                992: {
                    slidesPerView: 4,
                },
                1200: {
                    slidesPerView: 5,
                },
                1400: {
                    slidesPerView: 6,
                },
            },
        })

        initSwiperSlider('.tp-product-up-sale-slider-active', {
            slidesPerView: 4,
            spaceBetween: 24,
            loop: false,
            enteredSlides: false,
            scrollbar: {
                el: '.tp-up-sale-swiper-scrollbar',
                draggable: true,
                dragClass: 'tp-swiper-scrollbar-drag',
                snapOnRelease: true,
            },

            breakpoints: {
                0: {
                    slidesPerView: 2,
                },
                576: {
                    slidesPerView: 3,
                },
                992: {
                    slidesPerView: 4,
                },
                1200: {
                    slidesPerView: 5,
                },
                1400: {
                    slidesPerView: 6,
                },
            },
        })

        $('.tp-testimoinal-slider-active-3').each(function (index, element) {
            const $element = $(element)

            // Read autoplay settings from data attributes
            const isAutoplay = $element.data('autoplay') === true || $element.data('autoplay') === 'true'
            const autoplaySpeed = parseInt($element.data('autoplay-speed')) || 3000
            const isLoop = $element.data('loop') !== false && $element.data('loop') !== 'false'

            const swiperConfig = {
                slidesPerView: 2,
                spaceBetween: 24,
                loop: isLoop,
                enteredSlides: false,
                pagination: {
                    el: '.tp-testimoinal-slider-dot-3',
                    clickable: true,
                    renderBullet: function (index, className) {
                        return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                    },
                },
                // Navigation arrows
                navigation: {
                    nextEl: '.tp-testimoinal-slider-button-next-3',
                    prevEl: '.tp-testimoinal-slider-button-prev-3',
                },

                breakpoints: {
                    1200: {
                        slidesPerView: 2,
                    },
                    992: {
                        slidesPerView: 2,
                    },
                    768: {
                        slidesPerView: 1,
                    },
                    576: {
                        slidesPerView: 1,
                    },
                    0: {
                        slidesPerView: 1,
                    },
                },
            }

            // Add autoplay configuration if enabled
            if (isAutoplay) {
                swiperConfig.autoplay = {
                    delay: autoplaySpeed,
                    disableOnInteraction: false,
                }
            }

            initSwiperSlider(element, swiperConfig)
        })

        $('.tp-category-slider-active-4').each(function (index, element) {
            const $element = $(element)

            // Read autoplay settings from data attributes
            const isAutoplay = $element.data('autoplay') === true || $element.data('autoplay') === 'true'
            const autoplaySpeed = parseInt($element.data('autoplay-speed')) || 3000
            const isLoop = $element.data('loop') !== false && $element.data('loop') !== 'false'
            const itemsPerView = $element.data('items-per-view') || 5

            const swiperConfig = {
                slidesPerView: itemsPerView,
                spaceBetween: 25,
                loop: isLoop,
                enteredSlides: false,
                pagination: {
                    el: '.tp-category-slider-dot-4',
                    clickable: true,
                    renderBullet: function (index, className) {
                        return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                    },
                },
                // Navigation arrows
                navigation: {
                    nextEl: '.tp-category-slider-button-next-4',
                    prevEl: '.tp-category-slider-button-prev-4',
                },

                scrollbar: {
                    el: '.tp-category-swiper-scrollbar',
                    draggable: true,
                    dragClass: 'tp-swiper-scrollbar-drag',
                    snapOnRelease: true,
                },

                breakpoints: {
                    1400: {
                        slidesPerView: 5,
                    },
                    1200: {
                        slidesPerView: 4,
                    },
                    992: {
                        slidesPerView: 3,
                    },
                    768: {
                        slidesPerView: 2,
                    },
                    576: {
                        slidesPerView: 2,
                    },
                    0: {
                        slidesPerView: 1,
                    },
                },
            }

            // Add autoplay configuration if enabled
            if (isAutoplay) {
                swiperConfig.autoplay = {
                    delay: autoplaySpeed,
                    disableOnInteraction: false,
                }
            }

            initSwiperSlider(element, swiperConfig)
        })

        initSwiperSlider('.tp-brand-slider-active', {
            slidesPerView: 5,
            spaceBetween: 0,
            loop: false,
            enteredSlides: false,
            pagination: {
                el: '.tp-brand-slider-dot',
                clickable: true,
                renderBullet: function (index, className) {
                    return `<span class="${className}"><button>${index + 1}</button></span>`
                },
            },
            // Navigation arrows
            navigation: {
                nextEl: '.tp-brand-slider-button-next',
                prevEl: '.tp-brand-slider-button-prev',
            },

            breakpoints: {
                1200: {
                    slidesPerView: 5,
                },
                992: {
                    slidesPerView: 5,
                },
                768: {
                    slidesPerView: 4,
                },
                576: {
                    slidesPerView: 3,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        })

        initSwiperSlider('.tp-best-slider-active', {
            slidesPerView: 4,
            spaceBetween: 24,
            loop: false,
            enteredSlides: false,
            pagination: {
                el: '.tp-best-slider-dot',
                clickable: true,
                renderBullet: function (index, className) {
                    return `<span class="${className}"><button>${index + 1}</button></span>`
                },
            },
            // Navigation arrows
            navigation: {
                nextEl: '.tp-best-slider-button-next',
                prevEl: '.tp-best-slider-button-prev',
            },

            scrollbar: {
                el: '.tp-best-swiper-scrollbar',
                draggable: true,
                dragClass: 'tp-swiper-scrollbar-drag',
                snapOnRelease: true,
            },

            breakpoints: {
                1200: {
                    slidesPerView: 4,
                },
                992: {
                    slidesPerView: 4,
                },
                768: {
                    slidesPerView: 2,
                },
                576: {
                    slidesPerView: 2,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        })

        initSwiperSlider('.tp-slider-active-5s', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: false,
            effect: 'fade',
        })

        initSwiperSlider('.tp-category-slider-active-5', {
            slidesPerView: 6,
            spaceBetween: 12,
            loop: false,
            enteredSlides: false,
            pagination: {
                el: '.tp-category-slider-dot-4',
                clickable: true,
                renderBullet: function (index, className) {
                    return `<span class="${className}"><button>${index + 1}</button></span>`
                },
            },
            // Navigation arrows
            navigation: {
                nextEl: '.tp-category-slider-button-next-5',
                prevEl: '.tp-category-slider-button-prev-5',
            },

            scrollbar: {
                el: '.tp-category-5-swiper-scrollbar',
                draggable: true,
                dragClass: 'tp-swiper-scrollbar-drag',
                snapOnRelease: true,
            },

            breakpoints: {
                1400: {
                    slidesPerView: 6,
                },
                1200: {
                    slidesPerView: 5,
                },
                992: {
                    slidesPerView: 4,
                },
                768: {
                    slidesPerView: 3,
                },
                576: {
                    slidesPerView: 2,
                },
                400: {
                    slidesPerView: 2,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        })

        const $bestSliderActive5 = $('.tp-best-slider-active-5')

        if ($bestSliderActive5.length) {
            const itemPerRow = $bestSliderActive5.data('item-per-row')

            initSwiperSlider('.tp-best-slider-active-5', {
                slidesPerView: itemPerRow,
                spaceBetween: 24,
                loop: false,
                enteredSlides: false,
                pagination: {
                    el: '.tp-best-slider-dot-5',
                    clickable: true,
                    renderBullet: function (index, className) {
                        return `<span class="${className}"><button>${index + 1}</button></span>`
                    },
                },
                // Navigation arrows
                navigation: {
                    nextEl: '.tp-best-slider-5-button-next',
                    prevEl: '.tp-best-slider-5-button-prev',
                },

                scrollbar: {
                    el: '.tp-best-5-swiper-scrollbar',
                    draggable: true,
                    dragClass: 'tp-swiper-scrollbar-drag',
                    snapOnRelease: true,
                },

                breakpoints: {
                    1200: {
                        slidesPerView: itemPerRow,
                    },
                    992: {
                        slidesPerView: itemPerRow - 1,
                    },
                    768: {
                        slidesPerView: itemPerRow - 1,
                    },
                    576: {
                        slidesPerView: itemPerRow - 2,
                    },
                },
            })
        }

        initSwiperSlider('.tp-product-details-thumb-slider-active', {
            slidesPerView: 2,
            spaceBetween: 13,
            loop: false,
            enteredSlides: false,
            pagination: {
                el: '.tp-product-details-thumb-slider-dot',
                clickable: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                },
            },
            // Navigation arrows
            navigation: {
                nextEl: '.tp-product-details-thumb-slider-5-button-next',
                prevEl: '.tp-product-details-thumb-slider-5-button-prev',
            },

            breakpoints: {
                1200: {
                    slidesPerView: 2,
                },
                992: {
                    slidesPerView: 2,
                },
                768: {
                    slidesPerView: 2,
                },
                576: {
                    slidesPerView: 2,
                },
                0: {
                    slidesPerView: 1,
                },
            },
        })

        const $trendingSliderActive = $('.tp-trending-slider-active')

        if ($trendingSliderActive.length > 0) {
            $trendingSliderActive.each(function (index, el) {
                const itemsPerView = $(el).data('items-per-view') || 2

                initSwiperSlider(el, {
                    slidesPerView: itemsPerView,
                    spaceBetween: 20,
                    loop: false,
                    enteredSlides: false,
                    pagination: {
                        el: '.tp-trending-slider-dot',
                        clickable: true,
                        renderBullet: function (index, className) {
                            return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                        },
                    },
                    // Navigation arrows
                    navigation: {
                        nextEl: '.tp-trending-slider-button-next',
                        prevEl: '.tp-trending-slider-button-prev',
                    },

                    breakpoints: {
                        1200: {
                            slidesPerView: itemsPerView,
                        },
                        992: {
                            slidesPerView: itemsPerView - 1,
                        },
                        768: {
                            slidesPerView: 2,
                        },
                        420: {
                            slidesPerView: 2,
                        },
                        375: {
                            slidesPerView: 2,
                        },
                        0: {
                            slidesPerView: 1,
                        },
                    },
                })
            })
        }

        $('.tp-testimonial-slider-active').each(function (index, element) {
            const $element = $(element)

            // Read autoplay settings from data attributes
            const isAutoplay = $element.data('autoplay') === true || $element.data('autoplay') === 'true'
            const autoplaySpeed = parseInt($element.data('autoplay-speed')) || 3000
            const isLoop = $element.data('loop') !== false && $element.data('loop') !== 'false'

            const swiperConfig = {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: isLoop,
                pagination: {
                    el: '.tp-testimonial-slider-dot',
                    clickable: true,
                    renderBullet: function (index, className) {
                        return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                    },
                },
                // Navigation arrows
                navigation: {
                    nextEl: '.tp-testimonial-slider-button-next',
                    prevEl: '.tp-testimonial-slider-button-prev',
                },
            }

            // Add autoplay configuration if enabled
            if (isAutoplay) {
                swiperConfig.autoplay = {
                    delay: autoplaySpeed,
                    disableOnInteraction: false,
                }
            }

            initSwiperSlider(element, swiperConfig)
        })

        initSwiperSlider('.tp-testimonial-slider-active-5', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: false,
            enteredSlides: false,
            pagination: {
                el: '.tp-testimonial-slider-dot-5',
                clickable: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                },
            },
            // Navigation arrows
            navigation: {
                nextEl: '.tp-testimonial-slider-5-button-next',
                prevEl: '.tp-testimonial-slider-5-button-prev',
            },
        })

        initSwiperSlider('.tp-best-banner-slider-active-5', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: false,
            enteredSlides: false,
            effect: 'fade',
            pagination: {
                el: '.tp-best-banner-slider-dot-5',
                clickable: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + '</span>'
                },
            },
            // Navigation arrows
            navigation: {
                nextEl: '.tp-best-banner-slider-5-button-next',
                prevEl: '.tp-best-banner-slider-5-button-prev',
            },
        })

        initSwiperSlider('.tp-postbox-slider', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: false,
            autoplay: {
                delay: 3000,
            },
            // Navigation arrows
            navigation: {
                nextEl: '.tp-postbox-slider-button-next',
                prevEl: '.tp-postbox-slider-button-prev',
            },
            breakpoints: {
                1200: {
                    slidesPerView: 1,
                },
            },
        })

        if (typeof Theme.lazyLoadInstance !== 'undefined') {
            Theme.lazyLoadInstance.update()
        }
    }

    initSwiper()
    initSlick()

    function tp_ecommerce() {
        $('.tp-checkout-payment-item label').on('click', function () {
            $(this).siblings('.tp-checkout-payment-desc').slideToggle(400)
        })

        $('.tp-color-variation-btn').on('click', function () {
            $(this).addClass('active').siblings().removeClass('active')
        })

        $('.tp-size-variation-btn').on('click', function () {
            $(this).addClass('active').siblings().removeClass('active')
        })

        ////////////////////////////////////////////////////
        // 17. Show Login Toggle Js
        $('.tp-checkout-login-form-reveal-btn').on('click', function () {
            $('#tpReturnCustomerLoginForm').slideToggle(400)
        })

        ////////////////////////////////////////////////////
        // 18. Show Coupon Toggle Js
        $('.tp-checkout-coupon-form-reveal-btn').on('click', function () {
            $('#tpCheckoutCouponForm').slideToggle(400)
        })

        ////////////////////////////////////////////////////
        // 19. Create An Account Toggle Js
        $('#cbox').on('click', function () {
            $('#cbox_info').slideToggle(900)
        })

        ////////////////////////////////////////////////////
        // 20. Shipping Box Toggle Js
        $('#ship-box').on('click', function () {
            $('#ship-box-info').slideToggle(1000)
        })
    }
    tp_ecommerce()

    ////////////////////////////////////////////////////
    // 18. InHover Active Js
    $('.hover__active').on('mouseenter', function () {
        $(this).addClass('active').parent().siblings().find('.hover__active').removeClass('active')
    })

    $('.activebsba').on('click', function () {
        $('#services-item-thumb').removeClass().addClass($(this).attr('rel'))
        $(this).addClass('active').siblings().removeClass('active')
    })

    ////////////////////////////////////////////////////
    // 19. Line Animation Js
    if ($('#marker').length > 0) {
        function tp_tab_line() {
            const marker = document.querySelector('#marker')
            const item = document.querySelectorAll('.menu-style-3  > nav > ul > li')
            const itemActive = document.querySelector('.menu-style-3  > nav > ul > li.active')

            function indicator(e) {
                marker.style.left = e.offsetLeft + 'px'
                marker.style.width = e.offsetWidth + 'px'
            }

            item.forEach((link) => {
                link.addEventListener('mouseenter', (e) => {
                    indicator(e.target)
                })
            })

            const activeNav = $('.menu-style-3 > nav > ul > li.active')
            const activeWidth = $(activeNav).width()
            const activePadLeft = parseFloat($(activeNav).css('padding-left'))
            const activePadRight = parseFloat($(activeNav).css('padding-right'))
            const totalWidth = activeWidth + activePadLeft + activePadRight

            anchorWidthCounter()

            $(marker).css('display', 'block')

            $(marker).css('width', totalWidth)

            function anchorWidthCounter() {
                let anchorWidths = 0
                let a
                let aWidth
                let aPadLeft
                let aPadRight
                let aTotalWidth
                $('.menu-style-3 > nav > ul > li').each(function (index, elem) {
                    const activeTest = $(elem).hasClass('active')
                    marker.style.left = elem.offsetLeft + 'px'
                    if (activeTest) {
                        // Break out of the each function.
                        return false
                    }

                    a = $(elem).find('li')
                    aWidth = a.width()
                    aPadLeft = parseFloat(a.css('padding-left'))
                    aPadRight = parseFloat(a.css('padding-right'))
                    aTotalWidth = aWidth + aPadLeft + aPadRight

                    anchorWidths = anchorWidths + aTotalWidth
                })

                return anchorWidths
            }
        }
        tp_tab_line()
    }

    function tp_tab_line_2() {
        const marker = document.querySelector('#productTabMarker')
        const item = document.querySelectorAll('.tp-product-tab button')

        function indicator(e) {
            marker.style.left = e.offsetLeft + 'px'
            marker.style.width = e.offsetWidth + 'px'
        }

        item.forEach((link) => {
            link.addEventListener('click', (e) => {
                indicator(e.target)
            })
        })

        const activeNav = $('.nav-link.active')
        const activeWidth = $(activeNav).width()
        const activePadLeft = parseFloat($(activeNav).css('padding-left'))
        const activePadRight = parseFloat($(activeNav).css('padding-right'))
        const totalWidth = activeWidth + activePadLeft + activePadRight

        anchorWidthCounter()

        $(marker).css('display', 'block')

        $(marker).css('width', totalWidth)

        function anchorWidthCounter() {
            let anchorWidths = 0
            let a
            let aWidth
            let aPadLeft
            let aPadRight
            let aTotalWidth
            $('.tp-product-tab button').each(function (index, elem) {
                const activeTest = $(elem).hasClass('active')
                marker.style.left = elem.offsetLeft + 'px'
                if (activeTest) {
                    // Break out of the each function.
                    return false
                }

                a = $(elem).find('button')
                aWidth = a.width()
                aPadLeft = parseFloat(a.css('padding-left'))
                aPadRight = parseFloat(a.css('padding-right'))
                aTotalWidth = aWidth + aPadLeft + aPadRight

                anchorWidths = anchorWidths + aTotalWidth
            })

            return anchorWidths
        }
    }

    if ($('#productTabMarker').length > 0) {
        tp_tab_line_2()
    }

    if ($('.tp-header-height').length > 0) {
        const headerHeight = document.querySelector('.tp-header-height')
        const setHeaderHeight = headerHeight.offsetHeight

        $('.tp-header-height').each(function () {
            $(this).css({
                height: setHeaderHeight + 'px',
            })
        })
    }

    const $categoryLabel = $('.product-category-label span')

    $(document).on('change', '.tp-header-search-category select', function () {
        $categoryLabel.text($.trim($(this).find('option:selected').text()))
    })

    $categoryLabel.text($.trim($('.tp-header-search-category select').find('option:selected').text()))

    const collapseBreadcrumb = () => {
        const breadcrumbList = document.querySelector('.js_breadcrumb_reduce_length_on_mobile')
        if (breadcrumbList) {
            const breadcrumbItems = breadcrumbList.querySelectorAll('span')
            const totalItems = breadcrumbItems.length

            if (totalItems > 2) {
                for (let i = 1; i < totalItems - 1; i++) {
                    breadcrumbItems[i].style.display = 'none'
                }

                const ellipsis = document.createElement('span')
                ellipsis.textContent = '...'
                breadcrumbList.insertBefore(ellipsis, breadcrumbItems[totalItems - 1])
            }
        }
    }

    if ($(window).width() < 768) {
        collapseBreadcrumb()
    }

    if (typeof Waypoint !== 'undefined' && $('.tp-product-details-sticky-actions').length > 0 && $('.tp-product-details-action-wrapper').length > 0) {
        new Waypoint({
            element: $('.tp-product-details-action-wrapper'),
            handler: function (direction) {
                if (direction === 'down') {
                    $('.tp-product-details-sticky-actions').addClass('active')
                } else {
                    $('.tp-product-details-sticky-actions').removeClass('active')
                }
            },
        })
    }

    document.addEventListener('shortcode.loaded', () => {
        initCommonJS()

        initSwiper()

        if (window.Theme && window.Theme.initOwlCarousel) {
            window.Theme.initOwlCarousel()
        }

        if ($('#productTabMarker').length > 0) {
            tp_tab_line_2()
        }
    })

    // Lazy loading for theme elements (related products, etc.)
    // Note: Up-sale and cross-sale lazy loading is handled by the plugin's front-upsale-crosssale.js
    const $lazyElements = $(document).find('[data-bb-toggle="block-lazy-loading"]')

    $lazyElements.each((index, element) => {
        const $element = $(element)
        const url = $element.data('url')

        // Skip up-sale and cross-sale - handled by plugin
        if (url && (url.includes('up-sale-products') || url.includes('cross-sale-products'))) {
            return
        }

        $.ajax({
            url: url,
            type: 'GET',
            success: ({ data }) => {
                $element.replaceWith(data)

                if (typeof Theme.lazyLoadInstance !== 'undefined') {
                    Theme.lazyLoadInstance.update()
                }

                initSwiper()
            },
            error: (error) => Theme.handleError(error),
        })
    })

    const initProductTabNavigation = () => {
        const $tabButtons = $('.tp-product-details-tab-nav .nav-tabs button[data-bs-toggle="tab"]')

        if ($tabButtons.length === 0) {
            return
        }

        const tabHashMap = {
            'nav-description-tab': 'description',
            'nav-specification-tab': 'specification',
            'nav-review-tab': 'reviews',
            'nav-vendor-tab': 'vendor',
            'nav-faq-tab': 'faq'
        }

        const hashToTabMap = Object.fromEntries(
            Object.entries(tabHashMap).map(([key, value]) => [value, key])
        )

        const updateTabMarker = () => {
            const marker = document.querySelector('#productTabMarker')
            if (!marker) return

            const activeTab = document.querySelector('.tp-product-tab button.active')
            if (!activeTab) return

            marker.style.left = activeTab.offsetLeft + 'px'
            marker.style.width = activeTab.offsetWidth + 'px'
            marker.style.display = 'block'
        }

        const scrollTabIntoView = (tabElement) => {
            const container = tabElement.closest('.nav-tabs')
            if (!container) return

            tabElement.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' })
        }

        const $navTabs = $('.tp-product-details-tab-nav .nav-tabs')
        if ($navTabs.length) {
            const navEl = $navTabs[0]
            const navWrapper = navEl.closest('nav')

            const updateFadeHint = () => {
                if (!navWrapper) return
                const atEnd = navEl.scrollLeft + navEl.clientWidth >= navEl.scrollWidth - 2
                navWrapper.classList.toggle('tp-tab-scrolled-end', atEnd)
            }

            navEl.addEventListener('scroll', updateFadeHint, { passive: true })
            updateFadeHint()
        }

        $tabButtons.on('shown.bs.tab', function(e) {
            const tabId = $(this).attr('id')
            const hash = tabHashMap[tabId]

            if (hash) {
                history.replaceState(null, null, '#' + hash)
            }

            scrollTabIntoView(this)
            setTimeout(updateTabMarker, 50)
        })

        const activateTabFromHash = () => {
            const hash = window.location.hash.substring(1)

            if (hash && hashToTabMap[hash]) {
                const $targetTab = $('#' + hashToTabMap[hash])

                if ($targetTab.length && !$targetTab.hasClass('active')) {
                    const tabTrigger = new bootstrap.Tab($targetTab[0])
                    tabTrigger.show()

                    setTimeout(updateTabMarker, 100)

                    setTimeout(() => {
                        const $tabNav = $('.tp-product-details-tab-nav')
                        if ($tabNav.length) {
                            const headerHeight = $('.header-sticky').outerHeight() || 0
                            const scrollPosition = $tabNav.offset().top - headerHeight - 20

                            $('html, body').animate({
                                scrollTop: scrollPosition
                            }, 500)
                        }
                    }, 100)
                }
            }
        }

        activateTabFromHash()

        $(window).on('hashchange', activateTabFromHash)
    }

    initProductTabNavigation()
})
