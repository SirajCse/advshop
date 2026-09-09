/**
 * Owl Carousel Initialization for Simple Sliders
 */

$(() => {
    'use strict'

    const initOwlCarousel = () => {
        // Helper function to initialize Owl Carousel with data attributes
        const initOwlSlider = (selector) => {
            $(selector).each(function() {
                const $slider = $(this)
                
                if (!$slider.length || $slider.hasClass('owl-loaded')) {
                    return
                }

                // Read settings from data attributes
                const autoplay = $slider.data('owl-auto') === 'true' || $slider.data('owl-auto') === true
                const loop = $slider.data('owl-loop') === 'true' || $slider.data('owl-loop') === true
                const autoplaySpeed = parseInt($slider.data('owl-speed')) || 5000
                const gap = parseInt($slider.data('owl-gap')) || 0
                const nav = $slider.data('owl-nav') === 'true' || $slider.data('owl-nav') === true
                const dots = $slider.data('owl-dots') === 'true' || $slider.data('owl-dots') === true
                const items = parseInt($slider.data('owl-item')) || 1
                const itemsXs = parseInt($slider.data('owl-item-xs')) || items
                const itemsSm = parseInt($slider.data('owl-item-sm')) || items
                const itemsMd = parseInt($slider.data('owl-item-md')) || items
                const itemsLg = parseInt($slider.data('owl-item-lg')) || items
                const duration = parseInt($slider.data('owl-duration')) || 1000
                const mouseDrag = $slider.data('owl-mousedrag') === 'on' || $slider.data('owl-mousedrag') === true
                const animateOut = $slider.data('owl-animate-out') || 'fadeOut'
                const animateIn = $slider.data('owl-animate-in') || 'fadeIn'
                const rtl = $('body').attr('dir') === 'rtl'

                // Initialize Owl Carousel
                $slider.owlCarousel({
                    items: items,
                    loop: loop,
                    autoplay: autoplay,
                    autoplayTimeout: autoplaySpeed,
                    autoplayHoverPause: true,
                    margin: gap,
                    nav: nav,
                    dots: dots,
                    mouseDrag: mouseDrag,
                    touchDrag: true,
                    smartSpeed: duration,
                    animateOut: animateOut,
                    animateIn: animateIn,
                    rtl: rtl,
                    navText: [
                        '<svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 13L1 7L7 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                        '<svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 13L7 7L1 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                    ],
                    responsive: {
                        0: {
                            items: itemsXs
                        },
                        576: {
                            items: itemsSm
                        },
                        768: {
                            items: itemsMd
                        },
                        992: {
                            items: itemsLg
                        },
                        1200: {
                            items: items
                        }
                    },
                    onInitialized: function(event) {
                        // Add custom classes for styling
                        const $owl = $(event.target)
                        
                        // Handle light/dark variations
                        if ($owl.find('.is-light').length > 0) {
                            $owl.addClass('is-light')
                        }

                        // Style customizations for different slider types
                        if ($owl.hasClass('tp-slider-active')) {
                            $owl.find('.owl-nav button.owl-prev').addClass('tp-slider-button-prev')
                            $owl.find('.owl-nav button.owl-next').addClass('tp-slider-button-next')
                            $owl.find('.owl-nav').addClass('tp-slider-arrow tp-swiper-arrow d-none d-lg-block')
                            $owl.find('.owl-dots').addClass('tp-slider-dot tp-swiper-dot')
                        }

                        if ($owl.hasClass('tp-slider-active-2')) {
                            $owl.find('.owl-dots').addClass('tp-swiper-dot tp-slider-2-dot')
                        }

                        if ($owl.hasClass('tp-slider-active-3')) {
                            $owl.find('.owl-nav button.owl-prev').addClass('tp-slider-3-button-prev')
                            $owl.find('.owl-nav button.owl-next').addClass('tp-slider-3-button-next')
                            $owl.find('.owl-nav').addClass('tp-slider-arrow-3 d-none d-sm-block')
                            $owl.find('.owl-dots').addClass('tp-swiper-dot tp-slider-3-dot d-sm-none')
                        }

                        // Accessibility: Owl renders dots and nav arrows as
                        // <button> elements with no text content, which fails the
                        // "Buttons must have discernible text" audit. Add an
                        // aria-label to each so screen readers and AI agents can
                        // identify them.
                        $owl.find('.owl-dots .owl-dot').each(function(index) {
                            $(this).attr('aria-label', 'Go to slide ' + (index + 1))
                        })
                        $owl.find('.owl-nav .owl-prev').attr('aria-label', 'Previous slide')
                        $owl.find('.owl-nav .owl-next').attr('aria-label', 'Next slide')
                    },
                    onChanged: function(event) {
                        const $owl = $(event.target)
                        
                        // Handle light variation class toggle
                        if ($owl.hasClass('tp-slider-variation')) {
                            const $activeSlide = $owl.find('.owl-item.active .tp-slider-item')
                            if ($activeSlide.hasClass('is-light')) {
                                $owl.addClass('is-light')
                            } else {
                                $owl.removeClass('is-light')
                            }
                        }
                    }
                })
            })
        }

        // Initialize only simple slider variations (these use Owl Carousel)
        // Other sliders continue to use Swiper
        initOwlSlider('.tp-slider-active')
        initOwlSlider('.tp-slider-active-2')
        initOwlSlider('.tp-slider-active-3')
        initOwlSlider('.tp-slider-active-4')
        initOwlSlider('.tp-slider-active-5')
    }

    // Initialize on document ready
    initOwlCarousel()

    // Reinitialize on AJAX content load
    $(document).on('ajaxSuccess', function() {
        setTimeout(() => {
            initOwlCarousel()
        }, 100)
    })

    // Expose for external use
    window.Theme = window.Theme || {}
    window.Theme.initOwlCarousel = initOwlCarousel
})