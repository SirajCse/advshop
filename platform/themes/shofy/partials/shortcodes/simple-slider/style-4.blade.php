<section {!! $shortcode->htmlAttributes() !!} class="tp-slider-area p-relative z-index-1 fix">
    <div class="tp-slider-active-4 khaki-bg owl-carousel @if ($shortcode->animation_enabled == 'no') tp-slider-no-animation @endif"
         data-owl-auto="{{ $shortcode->is_autoplay == 'yes' ? 'true' : 'false' }}"
         data-owl-loop="{{ $shortcode->is_loop == 'no' ? 'false' : 'true' }}"
         data-owl-speed="{{ in_array($shortcode->autoplay_speed, [2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000]) ? $shortcode->autoplay_speed : 5000 }}"
         data-owl-gap="0"
         data-owl-nav="false"
         data-owl-dots="false"
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
                $description = $slider->description;
            @endphp

            <div class="tp-slider-item-4 tp-slider-height-4 p-relative khaki-bg d-flex align-items-center" style="background-color: {{ $slider->getMetaData('background_color', true) }}">
                <div class="tp-slider-thumb-4">
                    @include(Theme::getThemeNamespace('partials.shortcodes.simple-slider.includes.image', ['slider' => $slider]))
                    <div class="tp-slider-thumb-4-shape">
                        <span class="tp-slider-thumb-4-shape-1"></span>
                        <span class="tp-slider-thumb-4-shape-2"></span>
                    </div>
                </div>

                @if ($title || $description || $buttonLabel = $slider->getMetaData('button_label', true))
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-xl-6 col-lg-6 col-md-8">
                                <div class="tp-slider-content-4 p-relative z-index-1">
                                    @if($description)
                                        <span @if($fontFamily = $shortcode->font_family_of_description) style="--tp-ff-charm: '{{ $fontFamily }}'" @endif>
                                            {!! BaseHelper::clean($description) !!}
                                        </span>
                                    @endif
                                    @if ($title)
                                        <h3 class="tp-slider-title-4" @style(["font-size: {$shortcode->title_font_size}px" => $shortcode->title_font_size])>{!! BaseHelper::clean($title) !!}</h3>
                                    @endif
                                    @if($buttonLabel = $slider->getMetaData('button_label', true))
                                        <div class="tp-slider-btn-4">
                                            <a href="{{ $slider->url }}" class="tp-btn tp-btn-border tp-btn-border-white">
                                                {!! BaseHelper::clean($buttonLabel) !!}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>
