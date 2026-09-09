<section {!! $shortcode->htmlAttributes() !!} class="tp-slider-area p-relative z-index-1">
    <div
        class="tp-slider-full-width tp-slider-active tp-slider-variation owl-carousel @if ($shortcode->animation_enabled == 'no') tp-slider-no-animation @endif"
        data-owl-auto="{{ $shortcode->is_autoplay == 'yes' ? 'true' : 'false' }}"
        data-owl-loop="{{ $shortcode->is_loop == 'no' ? 'false' : 'true' }}"
        data-owl-speed="{{ in_array($shortcode->autoplay_speed, [2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000]) ? $shortcode->autoplay_speed : 5000 }}"
        data-owl-gap="0"
        data-owl-nav="true"
        data-owl-dots="true"
        data-owl-item="1"
        data-owl-item-xs="1"
        data-owl-item-sm="1"
        data-owl-item-md="1"
        data-owl-item-lg="1"
        data-owl-duration="1000"
        data-owl-mousedrag="on"
        data-owl-animate-out="fadeOut"
        data-owl-animate-in="fadeIn"
    >
            @foreach ($sliders as $slider)
                @php
                    $title = $slider->title;
                    $subtitle = $slider->getMetaData('subtitle', true);
                    $description = $slider->description;
                @endphp

                <div
                    @class(['tp-slider-item', 'is-light' => $slider->getMetaData('is_light', true)])
                    style="background-color: {{ $slider->getMetaData('background_color', true) }}"
                >
                    @if($slider->link)
                        <a class="tp-slider-thumb text-end" href="{{ $slider->link }}">
                            @include(Theme::getThemeNamespace('partials.shortcodes.simple-slider.includes.image', ['slider' => $slider]))
                        </a>
                    @else
                        <div class="tp-slider-thumb text-end">
                            @include(Theme::getThemeNamespace('partials.shortcodes.simple-slider.includes.image', ['slider' => $slider]))
                        </div>
                    @endif
                </div>
            @endforeach
    </div>
</section>
