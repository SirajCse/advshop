<section {!! $shortcode->htmlAttributes() !!} class="tp-slider-area p-relative z-index-1">
    <div class="tp-slider-active-2 owl-carousel @if ($shortcode->animation_enabled == 'no') tp-slider-no-animation @endif"
         data-owl-auto="{{ $shortcode->is_autoplay == 'yes' ? 'true' : 'false' }}"
         data-owl-loop="{{ $shortcode->is_loop == 'no' ? 'false' : 'true' }}"
         data-owl-speed="{{ in_array($shortcode->autoplay_speed, [2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000]) ? $shortcode->autoplay_speed : 5000 }}"
         data-owl-gap="0"
         data-owl-nav="false"
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
                    $description = $slider->description;
                @endphp

                <div class="tp-slider-item-2 tp-slider-height-2 p-relative grey-bg-5 d-flex align-items-end">
                    <div class="tp-slider-2-shape">
                        @if($shape = $shortcode->shape_1)
                            {{ RvMedia::image($shape, $slider->title, attributes: ['class' => 'tp-slider-2-shape-1']) }}
                        @endif
                    </div>
                    @if($title || $description)
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-xl-6 col-lg-6 col-md-6">
                                    <div class="tp-slider-content-2">
                                        @if($description)
                                            <span @if($fontFamily = $shortcode->font_family_of_description) style="--tp-ff-oregano: '{{ $fontFamily }}'" @endif>
                                                {!! BaseHelper::clean($description) !!}
                                            </span>
                                        @endif
                                        @if ($title)
                                            <h3 class="tp-slider-title-2" @style(["font-size: {$shortcode->title_font_size}px" => $shortcode->title_font_size])>{!! BaseHelper::clean($title) !!}</h3>
                                        @endif
                                        @if($buttonLabel = $slider->getMetaData('button_label', true))
                                            <div class="tp-slider-btn-2">
                                                <a href="{{ $slider->link }}" class="tp-btn tp-btn-border">
                                                    {!! BaseHelper::clean($buttonLabel) !!}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6">
                                    <div class="tp-slider-thumb-2-wrapper p-relative">
                                        <div class="tp-slider-thumb-2-shape">
                                            @if($shape = $shortcode->shape_2)
                                                {{ RvMedia::image($shape, $slider->title, attributes: ['class' => 'tp-slider-thumb-2-shape-1']) }}
                                            @endif
                                            @if($shape = $shortcode->shape_3)
                                                {{ RvMedia::image($shape, $slider->title, attributes: ['class' => 'tp-slider-thumb-2-shape-1']) }}
                                            @endif
                                        </div>
                                        <div class="tp-slider-thumb-2 text-end">
                                            <span class="tp-slider-thumb-2-gradient"></span>
                                            @php $slider->title = $title; @endphp
                                            @include(Theme::getThemeNamespace('partials.shortcodes.simple-slider.includes.image', ['slider' => $slider]))
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="tp-slider-thumb-2-wrapper p-relative">
                            <div class="tp-slider-thumb-2">
                                <span class="tp-slider-thumb-2-gradient"></span>
                                @include(Theme::getThemeNamespace('partials.shortcodes.simple-slider.includes.image', ['slider' => $slider]))
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
    </div>
</section>
