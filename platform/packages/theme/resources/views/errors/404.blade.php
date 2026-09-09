@extends('packages/theme::errors.master')

@section('title', trans('packages/theme::theme.errors.404_page_not_found'))

@section('content')
    <div class="empty">
        <div class="empty-header">404</div>
        <p class="empty-title">{{ trans('packages/theme::theme.errors.page_not_found') }}</p>
        <p class="empty-subtitle text-secondary">
            {{ trans('packages/theme::theme.errors.page_not_found_description') }}
        </p>
        <p class="empty-subtitle text-secondary">{!! BaseHelper::clean(
            trans('packages/theme::theme.errors.page_not_found_back_home', ['link' => BaseHelper::getHomepageUrl()]),
        ) !!}</p>
        <div class="empty-action">
            <x-core::button
                tag="a"
                href="{{ BaseHelper::getHomepageUrl() }}"
                color="primary"
                icon="ti ti-arrow-left"
            >
                {{ trans('packages/theme::theme.common.take_me_home') }}
            </x-core::button>
        </div>
    </div>
@endsection
