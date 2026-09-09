@php
    $highlightText = $shortcode->highlight_text ?? ($highlightText ?? null);
    $shapeStyle = theme_option('section_title_shape_decorated', 'style-1');

    if ($highlightText) {
        $title = str_replace($highlightText, '<span>' . $highlightText . '</span>', $title);
    } elseif ($shapeStyle === 'style-3') {
        $title = preg_replace('/([\p{L}\p{N}][\p{L}\p{N}\p{M}]*)/u', '<span>$1</span>', $title, 1);
    }
@endphp

{!! BaseHelper::clean($title) !!}

@if(in_array($shapeStyle, ['style-1', 'style-2'], true))
    {!! Theme::partial('section-title-shape') !!}
@endif
