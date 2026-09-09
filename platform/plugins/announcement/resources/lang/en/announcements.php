<?php

return [
    'name' => 'Announcements',

    'enums' => [
        'announce_placement' => [
            'top' => 'Top',
            'bottom' => 'Fixed at bottom',
            'popup' => 'Popup',
            'theme' => 'Theme built-in',
        ],

        'text_alignment' => [
            'start' => 'Start',
            'center' => 'Center',
        ],
    ],

    'validation' => [
        'font_size' => 'Font size must be a valid CSS font size value.',
        'text_color' => 'Text color must be a valid hex color value.',
    ],

    'create' => 'Create new announcement',
    'add_new' => 'Add new',
    'settings' => [
        'name' => 'Announcement',
        'description' => 'Manage announcement settings',
    ],

    'background_color' => 'Background color',
    'font_size' => 'Font size',
    'font_size_help' => 'Leave empty to use default. Example: 1rem, 1em, 12px, ...',
    'text_color' => 'Text color',
    'start_date' => 'Start date',
    'end_date' => 'End date',
    'has_action' => 'Has action',
    'action_label' => 'Action label',
    'action_url' => 'Action URL',
    'action_open_new_tab' => 'Open in new tab',
    'dismissible_label' => 'Allow user to dismiss announcement',
    'placement' => 'Placement',
    'text_alignment' => 'Text alignment',
    'is_active' => 'Is active',
    'max_width' => 'Max width',
    'max_width_help' => 'Leave empty to use default value. Example: 100%, 500px, ...',
    'max_width_unit' => 'Max width unit',
    'font_size_unit' => 'Font size unit',
    'autoplay_label' => 'Autoplay',
    'autoplay_delay_label' => 'Autoplay delay',
    'autoplay_delay_help' => 'The delay between each announcement in milliseconds. Leave empty to use default value (5000).',
    'lazy_loading' => 'Lazy loading',
    'lazy_loading_description' => 'Enable this option to improve page loading speed',
    'hide_on_mobile' => 'Hide on mobile',
    'dismiss' => 'Dismiss',

    // Placeholders and help texts
    'name_placeholder' => 'Enter announcement name',
    'name_help' => 'Name for internal reference only, not visible to users',
    'content_placeholder' => 'Enter your announcement message here...',
    'content_help' => 'The message that will be displayed to users. Supports HTML formatting.',
    'start_date_placeholder' => 'Select start date and time',
    'start_date_help' => 'Announcement will be visible from this date. Leave empty to start immediately.',
    'end_date_placeholder' => 'Select end date and time',
    'end_date_help' => 'Announcement will be hidden after this date. Leave empty for no expiration.',
    'has_action_help' => 'Add a call-to-action button to your announcement',
    'action_label_placeholder' => 'e.g., Learn More, Shop Now',
    'action_label_help' => 'Text displayed on the action button',
    'action_url_placeholder' => 'https://example.com/page',
    'action_url_help' => 'URL where users will be redirected when clicking the action button',
    'action_open_new_tab_help' => 'Open the action link in a new browser tab',
    'is_active_help' => 'Enable or disable this announcement without deleting it',
];
