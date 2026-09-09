@php
    $style = in_array($shortcode->style, [1, 2, 3, 4, 5, 'full-width']) ? $shortcode->style : 1;
    $sliders->loadMissing('metadata');

    $shortcode->font_family_of_description = $shortcode->font_family_of_description ?: theme_option('tp_cursive_font');

    // Owl Carousel powers this slider only. Load it on demand here instead of
    // globally so every other page (product detail, cart, etc.) skips the extra
    // CSS/JS. Theme's owl init runs on jQuery ready, after all deferred footer
    // scripts execute, so registration order does not matter.
    Theme::asset()->usePath()->add('owl-carousel', 'libraries/owl-carousel/owl.carousel.css');
    Theme::asset()->container('footer')->usePath()->add('owl-carousel', 'libraries/owl-carousel/owl.carousel.js', attributes: ['defer']);
@endphp

@if($shortcode->customize_font_family_of_description && $shortcode->font_family_of_description !== theme_option('tp_primary_font'))
    {!! BaseHelper::googleFonts('https://fonts.googleapis.com/' . sprintf('css2?family=%s:wght@400&display=swap', urlencode($shortcode->font_family_of_description))) !!}
@endif

{!! Theme::partial("shortcodes.simple-slider.style-$style", compact('sliders', 'shortcode')) !!}
