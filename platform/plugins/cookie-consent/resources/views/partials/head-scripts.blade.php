@php
    $storedCategories = $storedCategories ?? [];
    $marketingGranted = ! empty($storedCategories['marketing']);
    $analyticsGranted = ! empty($storedCategories['analytics']);
    $hasStoredConsent = ! empty($storedCategories);
@endphp
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('consent', 'default', {
        'ad_storage': 'denied',
        'analytics_storage': 'denied',
        'ad_user_data': 'denied',
        'ad_personalization': 'denied',
        'functionality_storage': 'denied',
        'personalization_storage': 'denied',
        'security_storage': 'granted',
        'wait_for_update': 500
    });
    @if ($hasStoredConsent)
    gtag('consent', 'update', {
        'ad_storage': '{{ $marketingGranted ? 'granted' : 'denied' }}',
        'analytics_storage': '{{ $analyticsGranted ? 'granted' : 'denied' }}',
        'ad_user_data': '{{ $marketingGranted ? 'granted' : 'denied' }}',
        'ad_personalization': '{{ $marketingGranted ? 'granted' : 'denied' }}'
    });
    @endif
</script>
