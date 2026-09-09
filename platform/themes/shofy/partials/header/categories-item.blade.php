@php
    $iconImage = $category->icon_image;
    $icon = $category->icon;
@endphp

@if ($iconImage || $icon)
    <span class="tp-product-category-icon">
        @if ($iconImage)
            {{ RvMedia::image($iconImage, __('Icon'), attributes: ['loading' => false, 'style' => 'width: 18px; height: 18px']) }}
        @elseif ($icon)
            {!! BaseHelper::renderIcon($icon) !!}
        @endif
    </span>
@endif

{{ $category->name }}
