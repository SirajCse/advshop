@extends('packages/theme::errors.master')

@section('title', trans('packages/theme::theme.errors.503_service_unavailable'))

@section('content')
    <div class="empty">
        <div class="empty-img">
            <img
                src="{{ asset('vendor/core/core/base/images/503.svg') }}"
                alt="503"
                height="128"
            >
        </div>
        <p class="empty-title">{{ trans('packages/theme::theme.errors.temporarily_down') }}</p>
        <p class="empty-subtitle text-secondary">{{ trans('packages/theme::theme.errors.maintenance_description') }}</p>
        <p class="empty-subtitle text-secondary">
            <i>{!! BaseHelper::clean(trans('packages/theme::theme.errors.maintenance_admin_note')) !!}</i></p>
        @if ($email = get_admin_email()->first())
            <p class="empty-subtitle text-secondary">{!! BaseHelper::clean(trans('packages/theme::theme.errors.need_help_contact', ['mail' => Html::mailto($email)])) !!}</p>
        @endif
    </div>
@endsection
