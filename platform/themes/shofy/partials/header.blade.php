@php
    $style = theme_option('header_style', 1);
    $style = in_array($style, [1, 2, 3, 4, 5]) ? $style : 1;
@endphp

{!! apply_filters('ads_render', null, 'header_before') !!}

{!! Theme::partial("header.styles.header-$style") !!}

{!! apply_filters('ads_render', null, 'header_after') !!}
