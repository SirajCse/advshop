<div
    class="vb-shortcode-type"
    data-key="{{ $shortcode['key'] }}"
>
    <div class="vb-type-icon">
        <x-core::icon name="ti ti-code" />
    </div>
    <div class="vb-type-name">{{ $shortcode['name'] }}</div>
    @if (!empty($shortcode['description']))
        <div class="vb-type-description">{{ $shortcode['description'] }}</div>
    @endif
</div>
