@php
    Theme::set('hasSlider', theme_option('header_style', 1) == 3);
@endphp

@if($sliders->first())
    @php
        $firstSlider = $sliders->first();
        $firstTabletImage = $firstSlider->getMetaData('tablet_image', true) ?: $firstSlider->image;
        $firstMobileImage = $firstSlider->getMetaData('mobile_image', true) ?: $firstTabletImage;
    @endphp

    {{-- Preload first slider image for better LCP --}}
    <link rel="preload" as="image"
          href="{{ RvMedia::getImageUrl($firstSlider->image) }}"
          media="(min-width: 1200px)"
          fetchpriority="high">
    <link rel="preload" as="image"
          href="{{ RvMedia::getImageUrl($firstTabletImage) }}"
          media="(min-width: 768px) and (max-width: 1199px)"
          fetchpriority="high">
    <link rel="preload" as="image"
          href="{{ RvMedia::getImageUrl($firstMobileImage) }}"
          media="(max-width: 767px)"
          fetchpriority="high">
@endif

<section {!! $shortcode->htmlAttributes() !!} class="tp-slider-area p-relative z-index-1">
    <div class="tp-slider-active-3 owl-carousel @if ($shortcode->animation_enabled == 'no') tp-slider-no-animation @endif"
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
                    $description = $slider->description;

                    $tabletImage = $slider->getMetaData('tablet_image', true) ?: $slider->image;
                    $mobileImage = $slider->getMetaData('mobile_image', true) ?: $tabletImage;
                @endphp

                <div class="tp-slider-item-3 tp-slider-height-3 p-relative grey-bg d-flex align-items-center">
                    <div
                        class="tp-slider-thumb-3 include-bg"
                        @if($slider->image)
                            data-background="{{ RvMedia::getImageUrl($slider->image, $title) }}"
                        @endif
                        @if ($tabletImage) data-tablet-background="{{ RvMedia::getImageUrl($tabletImage) }}" @endif
                        @if ($mobileImage) data-mobile-background="{{ RvMedia::getImageUrl($mobileImage) }}" @endif
                    ></div>
                    @if($title || $description)
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-xl-6 col-lg-6 col-md-8">
                                    <div class="tp-slider-content-3">
                                        @if($description)
                                            <span @if($fontFamily = $shortcode->font_family_of_description) style="--tp-ff-charm: '{{ $fontFamily }}'" @endif>
                                                {!! BaseHelper::clean($description) !!}
                                            </span>
                                        @endif
                                        @if ($title)
                                            <h3 class="tp-slider-title-3" @style(["font-size: {$shortcode->title_font_size}px" => $shortcode->title_font_size])>{!! BaseHelper::clean($title) !!}</h3>
                                        @endif
                                        @if($buttonLabel = $slider->getMetaData('button_label', true))
                                            <div class="tp-slider-btn-3">
                                                <a href="{{ $slider->link }}" class="tp-btn tp-btn-border tp-btn-border-white">
                                                    {!! BaseHelper::clean($buttonLabel) !!}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
    </div>
</section>
