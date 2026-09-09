@php
    $style = in_array($shortcode->style, ['grid', 'compact-grid', 'slider', 'list', 'table']) ? $shortcode->style : 'grid';
@endphp

{!! Theme::partial("shortcodes.ecommerce-categories.$style", compact('shortcode', 'categories', 'imageField')) !!}
