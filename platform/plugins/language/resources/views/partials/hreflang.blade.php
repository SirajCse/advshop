@php
    $defaultLocale = Language::getDefaultLocale();
    $xDefaultUrl = collect($hreflangUrls)->first(function ($url, $code) use ($defaultLocale) {
        return str_starts_with($code, $defaultLocale);
    }) ?? rtrim(Language::getLocalizedURL($defaultLocale, url()->current(), [], false), '/');
@endphp

<link
    href="{{ rtrim($xDefaultUrl, '/') }}"
    hreflang="x-default"
    rel="alternate"
/>

@foreach ($hreflangUrls as $hreflangCode => $url)
    <link
        href="{{ $url }}"
        hreflang="{{ $hreflangCode }}"
        rel="alternate"
    />
@endforeach
