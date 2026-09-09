<?php

return [
    'webhook_secret' => 'Webhook सीक्रेट',
    'webhook_secret_helper' => 'यह आपके Stripe Dashboard में Developers > Webhooks > Signing secret के अंतर्गत मिलेगा।',
    'public_key_helper' => 'यह आपके Stripe Dashboard में Developers > API keys के अंतर्गत मिलेगा।',
    'secret_key_helper' => 'यह आपके Stripe Dashboard में Developers > API keys के अंतर्गत मिलेगा। इस कुंजी को गोपनीय रखें।',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook सेटअप गाइड',
        'description' => 'Stripe webhook सेट अप करने के लिए इन चरणों का पालन करें',
        'step_1_label' => 'Stripe डैशबोर्ड में लॉगिन करें',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'इवेंट चुनें और एंडपॉइंट कॉन्फ़िगर करें',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'एंडपॉइंट जोड़ें',
        'step_3_description' => 'Webhook को सहेजने के लिए "एंडपॉइंट जोड़ें" बटन पर क्लिक करें।',
        'step_4_label' => 'साइनिंग सीक्रेट कॉपी करें',
        'step_4_description' => '"Webhook Details" अनुभाग से "Signing Secret" मान कॉपी करें और इसे "सेटिंग्स" पेज के "पेमेंट" टैब के "Stripe" अनुभाग में "Stripe Webhook Secret" फ़ील्ड में पेस्ट करें।',
    ],
    'no_payment_charge' => 'कोई भुगतान शुल्क नहीं। कृपया पुनः प्रयास करें!',
    'payment_failed' => 'भुगतान विफल रहा!',
    'payment_type' => 'भुगतान प्रकार',
];
