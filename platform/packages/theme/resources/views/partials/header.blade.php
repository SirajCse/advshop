{!! SeoHelper::render() !!}
<link
    rel="sitemap"
    title="Sitemap"
    href="{{ rescue(fn() => route('public.sitemap'), report: false) }}"
    type="application/xml"
>

@if ($favicon = theme_option('favicon'))
    {{ Html::favicon(RvMedia::getImageUrl($favicon), ['type' => theme_option('favicon_type', 'image/x-icon')]) }}
@endif

@if (Theme::has('headerMeta'))
    {!! Theme::get('headerMeta') !!}
@endif

{!! apply_filters('theme_front_meta', null) !!}

{!! Theme::typography()->renderCssVariables() !!}

{!! Theme::asset()->container('before_header')->styles() !!}
{!! Theme::asset()->styles() !!}
{!! Theme::asset()->container('after_header')->styles() !!}

{{-- Responsive-image reset scoped to images tagged by ImageDimensionsInjector.
     Wrapped in `@layer base` so any author CSS — including utility classes like
     Tailwind `.h-8` / `.w-auto` in `@layer utilities` — wins via cascade layer
     priority. The `[data-dims-auto]` attribute gate still means static theme
     markup with intentional HTML width/height (e.g. `<img src="icon.png"
     width="24" height="24">` on a larger source file) is never touched — only
     auto-injected images reset. --}}
<style>@layer base{img[data-dims-auto]{max-width:100%;width:auto;height:auto}}</style>

{!! Theme::asset()->container('header')->scripts() !!}

{!! apply_filters(THEME_FRONT_HEADER, null) !!}

{!! SeoHelper::meta()->getAnalytics()->render() !!}

<script>
    window.siteUrl = "{{ rescue(fn() => BaseHelper::getHomepageUrl()) }}";
</script>
