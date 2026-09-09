@php
    $hasLogoLight ??= false;
    $defaultIsDark ??= true;

    $logo = Theme::getLogo();
    $logoLight = Theme::getLogo('logo_light');

    $height = theme_option('logo_height', 35);
    $attributes = [
        'style' => sprintf('max-height: %s !important;', is_numeric($height) ? "{$height}px" : $height),
        'loading' => 'eager',
    ];
@endphp

@if ($logo || $logoLight)
    <div class="logo">
        <a href="{{ BaseHelper::getHomepageUrl() }}">
            @if ($hasLogoLight)
                {{ RvMedia::image($logoLight ?: $logo, Theme::getSiteTitle(), attributes: ['class' => 'logo-light', ...$attributes], lazy: false) }}
                {{ RvMedia::image($logo ?: $logoLight, Theme::getSiteTitle(), attributes: ['class' => 'logo-dark', ...$attributes], lazy: false) }}
            @else
                {{ RvMedia::image($defaultIsDark ? $logo : $logoLight, Theme::getSiteTitle(), attributes: $attributes, lazy: false) }}
            @endif
        </a>
    </div>
@endif
