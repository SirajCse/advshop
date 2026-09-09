<section {!! $shortcode->htmlAttributes() !!} class="tp-slider-area p-relative z-index-1 fix">
    <div class="tp-slider-active-5 owl-carousel @if ($shortcode->animation_enabled == 'no') tp-slider-no-animation @endif"
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
            @foreach($sliders as $slider)
                @php
                    $title = $slider->title;
                    $buttonLabel = $slider->getMetaData('button_label', true);
                @endphp

                <div class="tp-slider-item-5 scene tp-slider-height-5 d-flex align-items-center" style="background-color: #F3F3F3">
                    <div class="tp-slider-shape-5">
                        @foreach(range(1, 4) as $i)
                            @if($shape = $shortcode->{"shape_$i"})
                                <div class="tp-slider-shape-5-{{ $i }}">
                                    {{ RvMedia::image($shape, $slider->title, attributes: ['class' => 'layer', 'data-depth' => '0.2']) }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @if($title || $buttonLabel)
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-xxl-7 col-xl-7 col-lg-6">
                                    <div class="tp-slider-content-5 p-relative z-index-1">
                                        @if($title)
                                            <h3 class="tp-slider-title-5" @style(["font-size: {$shortcode->title_font_size}px" => $shortcode->title_font_size])>
                                                {!! BaseHelper::clean($title) !!}
                                            </h3>
                                        @endif

                                        @if($buttonLabel)
                                            <div class="tp-slider-btn-5">
                                                <a href="{{ $slider->link }}" class="tp-btn-green">
                                                    {!! BaseHelper::clean($buttonLabel) !!}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-xxl-5 col-xl-5 col-lg-6">
                                    <div class="tp-slider-thumb-wrapper-5 p-relative">
                                        @if($shape = $shortcode->shape_5)
                                            <div class="tp-slider-thumb-shape-5 one d-none d-sm-block">
                                                {{ RvMedia::image($shape, $slider->title, attributes: ['data-depth' => '0.1', 'class' => 'layer offer']) }}
                                            </div>
                                        @endif
                                        <div class="tp-slider-thumb-5 main-img">
                                            @include(Theme::getThemeNamespace('partials.shortcodes.simple-slider.includes.image', compact('slider')))
                                            <span class="tp-slider-thumb-5-gradient"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="tp-slider-thumb-5 main-img">
                            @include(Theme::getThemeNamespace('partials.shortcodes.simple-slider.includes.image', compact('slider')))
                            <span class="tp-slider-thumb-5-gradient"></span>
                        </div>
                    @endif
                </div>
            @endforeach
    </div>
</section>
