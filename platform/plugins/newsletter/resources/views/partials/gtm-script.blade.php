@if(function_exists('get_ecommerce_setting') && get_ecommerce_setting('google_tag_manager_enabled', false))
<script>
    document.addEventListener('newsletter.subscribed', function(e) {
        var email = (e.detail && e.detail.email) ? e.detail.email : '';

        if (typeof gtag === 'function') {
            gtag('event', 'generate_lead', {
                lead_type: 'newsletter',
                currency: '{{ function_exists('get_application_currency') ? get_application_currency()->title : 'USD' }}',
                value: 0
            });
        }

        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            event: 'lead',
            lead_type: 'newsletter',
            email: email
        });
    });
</script>
@endif
