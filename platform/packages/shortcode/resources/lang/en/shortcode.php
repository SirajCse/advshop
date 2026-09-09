<?php

return [
    'shortcode' => 'Shortcode',
    'use' => 'Use',
    'ui-blocks' => 'UI Blocks',
    'search' => 'Search...',
    'no_shortcode_found' => 'No shortcode found.',
    'shortcode_not_available' => 'Shortcode ":name" is not available or has been removed.',

    'cache_suggestion' => [
        'title' => 'Performance Suggestion',
        'description' => 'You can improve your site performance by enabling shortcode caching.',
        'benefits' => 'This can significantly reduce page load times by caching rendered shortcodes.',
        'enable_button' => 'Enable shortcode caching',
        'dismiss_button' => 'Dismiss for a week',
    ],
    'form' => [
        'enable_lazy_loading' => 'Enable lazy loading',
        'no' => 'No',
        'yes' => 'Yes',
        'lazy_loading_helper' => 'When enabled, shortcode content will be loaded sequentially as the page loads, rather than all at once. This can help improve page load times.',
        'enable_caching' => 'Enable caching',
        'caching_helper' => 'When enabled, this shortcode content will be cached to improve performance. Disable for dynamic content that changes frequently.',
        'cache_disabled_notice' => 'Due to UI issues, cache for this UI block is disabled via code. This shortcode will not be cached even if caching is enabled.',
        'lazy_loading_disabled_notice' => 'Lazy loading for this UI block is disabled via code. This shortcode will not use lazy loading even if enabled.',
        'custom_css' => 'Custom CSS (optional)',
        'custom_css_helper' => 'Please enter your CSS code on a single line. It won\'t work if it has break line. Some special characters may be escaped.',
        'background_color' => 'Background color (optional)',
        'text_color' => 'Text color (optional)',
        'text_color_helper' => 'This color may be overridden by the theme. If it doesn\'t work, please add your CSS in Appearance -> Custom CSS.',
        'background_image' => 'Background image (optional)',
        'quantity' => 'Quantity',
        'tab_number' => 'Tab #:number',
    ],
];
