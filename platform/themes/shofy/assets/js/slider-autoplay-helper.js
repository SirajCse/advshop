/**
 * Slider Autoplay Helper
 * Provides reusable functions for handling slider autoplay functionality
 */
window.SliderAutoplayHelper = {
    /**
     * Read autoplay settings from data attributes
     * @param {jQuery} $element - The jQuery element
     * @returns {Object} Configuration object with autoplay settings
     */
    getAutoplayConfig: function($element) {
        return {
            isAutoplay: $element.data('autoplay') === true || $element.data('autoplay') === 'true',
            autoplaySpeed: parseInt($element.data('autoplay-speed')) || 3000,
            isLoop: $element.data('loop') !== false && $element.data('loop') !== 'false'
        };
    },

    /**
     * Apply autoplay configuration to Swiper config object
     * @param {Object} swiperConfig - The Swiper configuration object
     * @param {Object} autoplayConfig - The autoplay configuration from getAutoplayConfig()
     * @returns {Object} Updated Swiper configuration
     */
    applySwiperAutoplayConfig: function(swiperConfig, autoplayConfig) {
        // Set loop configuration
        swiperConfig.loop = autoplayConfig.isLoop;

        // Add autoplay configuration if enabled
        if (autoplayConfig.isAutoplay) {
            swiperConfig.autoplay = {
                delay: autoplayConfig.autoplaySpeed,
                disableOnInteraction: false,
            };
        }

        return swiperConfig;
    },

    /**
     * Initialize a slider with autoplay support
     * @param {string} selector - CSS selector for the slider
     * @param {Object} baseSwiperConfig - Base Swiper configuration
     * @param {Function} initSwiperSlider - The initSwiperSlider function
     */
    initSliderWithAutoplay: function(selector, baseSwiperConfig, initSwiperSlider) {
        $(selector).each(function(index, element) {
            const $element = $(element);
            const autoplayConfig = SliderAutoplayHelper.getAutoplayConfig($element);
            
            // Clone the base config to avoid modifying the original
            const swiperConfig = Object.assign({}, baseSwiperConfig);
            
            // Apply autoplay configuration
            SliderAutoplayHelper.applySwiperAutoplayConfig(swiperConfig, autoplayConfig);
            
            // Initialize the slider
            initSwiperSlider(element, swiperConfig);
        });
    },

    /**
     * Initialize a slider with autoplay support and custom items per view
     * @param {string} selector - CSS selector for the slider
     * @param {Object} baseSwiperConfig - Base Swiper configuration
     * @param {Function} initSwiperSlider - The initSwiperSlider function
     * @param {string} itemsDataAttribute - Data attribute name for items per view (default: 'items-per-view')
     */
    initSliderWithAutoplayAndItems: function(selector, baseSwiperConfig, initSwiperSlider, itemsDataAttribute = 'items-per-view') {
        $(selector).each(function(index, element) {
            const $element = $(element);
            const autoplayConfig = SliderAutoplayHelper.getAutoplayConfig($element);
            
            // Clone the base config to avoid modifying the original
            const swiperConfig = Object.assign({}, baseSwiperConfig);
            
            // Set items per view if available
            const itemsPerView = $element.data(itemsDataAttribute);
            if (itemsPerView) {
                swiperConfig.slidesPerView = itemsPerView;
                
                // Update breakpoints if they exist
                if (swiperConfig.breakpoints && swiperConfig.breakpoints[1200]) {
                    swiperConfig.breakpoints[1200].slidesPerView = itemsPerView;
                }
            }
            
            // Apply autoplay configuration
            SliderAutoplayHelper.applySwiperAutoplayConfig(swiperConfig, autoplayConfig);
            
            // Initialize the slider
            initSwiperSlider(element, swiperConfig);
        });
    }
};
