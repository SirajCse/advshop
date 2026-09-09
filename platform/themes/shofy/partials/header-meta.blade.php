<style>
    :root {
        --primary-color: {{ $primaryColor = theme_option('primary_color', '#0989ff') }};
        --secondary-color: {{ $secondaryColor = theme_option('secondary_color', '#821f40') }};
        --primary-color-rgb: {{ implode(',', BaseHelper::hexToRgb($primaryColor)) }};
        --tp-theme-secondary: {{ $secondaryColor }};
        --footer-background-color: {{ theme_option('footer_background_color', '#fff') }};
        --footer-text-color: {{ theme_option('footer_text_color', '#010f1c') }};
        --footer-title-color: {{ theme_option('footer_title_color', '#010f1c') }};
        --footer-link-color: {{ theme_option('footer_link_color', '#010f1c') }};
        --footer-link-hover-color: {{ theme_option('footer_link_hover_color', '#0989ff') }};
        --footer-border-color: {{ theme_option('footer_border_color', '#e5e6e8') }};
        --header-menu-text-hover-color: {{ theme_option('header_menu_text_hover_color', '#0989ff') }};
        --header-main-text-hover-color: {{ theme_option('header_main_text_hover_color', '#0989ff') }};
        --header-sticky-background-color: {{ theme_option('header_sticky_background_color', '#fff') }};
        --header-sticky-text-color: {{ theme_option('header_sticky_text_color', '#010f1c') }};
        --header-sticky-text-hover-color: {{ theme_option('header_sticky_text_hover_color', '#0989ff') }};
    }
</style>
