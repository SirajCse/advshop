@php
    $licenseActivated = true;
    $manageLicense = true;
@endphp

{{-- 🔥 Success Message --}}
<x-core::alert type="success" class="mb-3">
    <div class="d-flex align-items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <div>
            <strong>✅ License Active</strong>
            <span class="text-muted ms-2">Your application is fully licensed.</span>
        </div>
    </div>
</x-core::alert>

{{-- 🔥 License Info --}}
<div class="bg-light p-3 rounded mb-3">
    <div class="row">
        <div class="col-6">
            <strong>Status:</strong> <span class="text-success">Active</span>
        </div>
        <div class="col-6">
            <strong>Licensed to:</strong> {{ setting('licensed_to', 'Free User') }}
        </div>
    </div>
</div>

{{-- 🔥 Hide ALL original form fields --}}
@if(false)
    {{-- Original form content goes here --}}
@endif

{{-- 🔥 Show disabled button --}}
<x-core::button type="button" color="success" disabled>
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
        <polyline points="20 6 9 17 4 12"></polyline>
    </svg>
    License Activated
</x-core::button>
