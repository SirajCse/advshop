@php
    $title = $title ?? trans('plugins/ecommerce::ecommerce.your_cart_is_empty');
    $description = $description ?? trans('plugins/ecommerce::review.explore_and_add_items_to_get_started');
    $route = $route ?? route('public.products');
    $label = $label ?? trans('plugins/ecommerce::review.start_shopping');
    // Optional Tabler icon name (e.g. 'ti ti-arrows-left-right'); themes
    // can style .bb-empty-state-icon to render this above the heading.
    $icon = $icon ?? null;
@endphp

<div class="text-center pt-50 bb-empty-state">
    @if ($icon)
        <div class="bb-empty-state-icon mb-4">
            <x-core::icon :name="$icon" />
        </div>
    @endif
    <h3 class="mb-3">{!! BaseHelper::clean($title) !!}</h3>
    <p class="mb-3">{!! BaseHelper::clean($description) !!}</p>
    <a href="{{ $route }}" class="btn btn-outline-primary">{!! BaseHelper::clean($label) !!}</a>
</div>
