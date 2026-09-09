<?php

return [
    'stripe_account_id' => 'Stripe खाता ID',
    'go_to_dashboard' => 'एक्सप्रेस डैशबोर्ड पर जाएं',
    'connect' => [
        'label' => 'Stripe के साथ कनेक्ट करें',
        'description' => 'भुगतान एकत्र करने के लिए अपने Stripe खाते को कनेक्ट करें।',
    ],
    'disconnect' => [
        'label' => 'Stripe को डिस्कनेक्ट करें',
        'confirm' => 'क्या आप वाकई अपने Stripe खाते को डिस्कनेक्ट करना चाहते हैं?',
    ],
    'notifications' => [
        'connected' => 'आपका Stripe खाता कनेक्ट हो गया है।',
        'disconnected' => 'आपका Stripe खाता डिस्कनेक्ट हो गया है।',
        'now_active' => 'आपका Stripe खाता अब सक्रिय है।',
    ],
    'withdrawal' => [
        'payout_info' => 'आपका भुगतान स्वचालित रूप से आपके Stripe खाते में स्थानांतरित किया जाएगा जिसकी ID है: :stripe_account_id।',
    ],
];
