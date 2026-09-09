@php
    PageTitle::setTitle(trans('core/base::errors.500_internal_server_error'));
@endphp

@extends('core/base::errors.master')

@section('content')
    <div class="empty">
        <div class="empty-header">500</div>
        <p class="empty-title">{{ trans('core/base::errors.500_internal_server_error_description') }}</p>
        <p class="empty-subtitle text-secondary">
            {{ trans('core/base::errors.500_description') }}
        </p>
        <div class="empty-action">
            <x-core::button
                tag="a"
                href="{{ route('dashboard.index') }}"
                color="primary"
                icon="ti ti-arrow-left"
            >
                {{ trans('core/base::errors.take_me_home') }}
            </x-core::button>
        </div>
    </div>
@endsection
