<section {!! $shortcode->htmlAttributes() !!} class="tp-slider-area p-relative z-index-1">
    <div
        class="tp-slider-active tp-slider-variation owl-carousel @if ($shortcode->animation_enabled == 'no') tp-slider-no-animation @endif"
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
                    @class(['tp-slider-item tp-slider-height d-flex align-items-center', 'is-light' => $slider->getMetaData('is_light', true)])
                    style="background-color: {{ $slider->getMetaData('background_color', true) }}"
                >
                    <div class="tp-slider-shape d-none d-sm-block">
                        @foreach(range(1, 4) as $i)
                            @if($shape = $shortcode->{"shape_$i"})
                                {{ RvMedia::image($shape, $slider->title, attributes: ['class' => "tp-slider-shape-$i"]) }}
                            @endif
                        @endforeach
                    </div>
                    @if ($title || $description)
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-xl-5 col-lg-6 col-md-6">
                                    <div class="tp-slider-content p-relative z-index-1">
                                        @if($subtitle)
                                            <span>{!! BaseHelper::clean($subtitle) !!}</span>
                                        @endif
                                        @if ($title)
                                            <h3 class="tp-slider-title" @style(["font-size: {$shortcode->title_font_size}px" => $shortcode->title_font_size])>{!! BaseHelper::clean($title) !!}</h3>
                                        @endif
                                        @if($description)
                                            <p @if($fontFamily = $shortcode->font_family_of_description) style="--tp-ff-oregano: '{{ $fontFamily }}'" @endif>
                                                {!! BaseHelper::clean($description) !!}
                                            </p>
                                        @endif
                                        @if($buttonLabel = $slider->getMetaData('button_label', true))
                                            <div class="tp-slider-btn">
                                                <a href="{{ $slider->link }}" class="tp-btn tp-btn-2 tp-btn-white">
                                                    {!! BaseHelper::clean($buttonLabel) !!}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-xl-7 col-lg-6 col-md-6">
                                    <div class="tp-slider-thumb text-end">
                                        @include(Theme::getThemeNamespace('partials.shortcodes.simple-slider.includes.image', ['slider' => $slider]))
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="tp-slider-thumb text-end">
                            @include(Theme::getThemeNamespace('partials.shortcodes.simple-slider.includes.image', ['slider' => $slider]))
                        </div>
                    @endif
                </div>
            @endforeach
    </div>
</section>
