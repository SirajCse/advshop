@php
    PageTitle::setTitle(trans('core/base::errors.503_service_unavailable'));
@endphp

@extends('core/base::errors.master')

@section('content')
    <div class="empty">
        <div class="empty-img">
            <img
                src="{{ asset('vendor/core/core/base/images/503.svg') }}"
                alt="503"
                height="128"
            >
        </div>
        <p class="empty-title">{{ trans('core/base::errors.503_temporarily_down') }}</p>
        <p class="empty-subtitle text-secondary">
            {!! BaseHelper::clean(trans('core/base::errors.503_admin_instruction')) !!}
        </p>
    </div>
@endsection
