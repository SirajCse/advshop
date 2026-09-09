@php
    Theme::set('pageTitle', __('Search result for ":query"', ['query' => BaseHelper::stringify(request()->input('q'))]));
@endphp

@include(Theme::getThemeNamespace('views.ecommerce.products'))
