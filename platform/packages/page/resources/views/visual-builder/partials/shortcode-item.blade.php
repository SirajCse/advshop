@php
    $activeClass = isset($activeId) && $shortcode['id'] == $activeId ? 'active' : '';

    $previewText = '';
    if (!empty($shortcode['attributes'])) {
        $attrs = collect($shortcode['attributes'])
            ->take(2)
            ->map(function ($val, $key) {
                // Convert arrays or objects to string representation
                if (is_array($val)) {
                    $val = json_encode($val);
                } elseif (is_object($val)) {
                    $val = (string) $val;
                }
                return "$key: $val";
            })
            ->join(', ');
        $previewText = $attrs ?: $shortcode['name'];
    } else {
        $previewText = $shortcode['name'];
    }
@endphp

<div
    class="vb-shortcode-item {{ $activeClass }}"
    data-id="{{ $shortcode['id'] }}"
>
    <div class="vb-item-drag-handle">
        <x-core::icon name="ti ti-grip-vertical" />
    </div>
    <div class="vb-item-icon">
        <x-core::icon name="ti ti-code" />
    </div>
    <div class="vb-item-content">
        <h4 class="vb-item-name">{{ $shortcode['name'] }}</h4>
        <div class="vb-item-preview">{{ $previewText }}</div>
    </div>
    <div class="vb-item-actions">
        <button
            type="button"
            class="btn btn-sm btn-ghost-secondary vb-delete-btn"
            data-id="{{ $shortcode['id'] }}"
            title="{{ trans('packages/page::pages.delete') }}"
        >
            <x-core::icon name="ti ti-trash" />
        </button>
    </div>
</div>
