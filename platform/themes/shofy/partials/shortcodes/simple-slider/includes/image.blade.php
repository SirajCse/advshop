@php
    $slider->loadMissing('metadata');
    $tabletImage = $slider->getMetaData('tablet_image', true) ?: $slider->image;
    $mobileImage = $slider->getMetaData('mobile_image', true) ?: $tabletImage;

    $sliderAttributes = $sliderAttributes ?? [];
    $sliderAttributes['loading'] = 'eager';
    $sliderAttributes['fetchpriority'] = 'high';

    $defaultImage = RvMedia::getDefaultImage();
@endphp

@if($slider->link)
    <a href="{{ url($slider->link) }}">
@endif
    <picture>
        <source
            srcset="{{ RvMedia::getImageUrl($slider->image, null, false, $defaultImage) }}"
            media="(min-width: 1200px)"
        />
        <source
            srcset="{{ RvMedia::getImageUrl($tabletImage, null, false, $defaultImage) }}"
            media="(min-width: 768px)"
        />
        <source
            srcset="{{ RvMedia::getImageUrl($mobileImage, null, false, $defaultImage) }}"
            media="(max-width: 767px)"
        />
        {{ RvMedia::image($slider->image, $slider->title, attributes: $sliderAttributes) }}
    </picture>
@if($slider->link)
    </a>
@endif

@php
    unset($sliderAttributes);
@endphp
