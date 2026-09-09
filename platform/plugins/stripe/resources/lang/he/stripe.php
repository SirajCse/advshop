<?php

return [
    'webhook_secret' => 'סוד Webhook',
    'webhook_secret_helper' => 'מצא זאת בלוח המחוונים של Stripe תחת Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'מצא זאת בלוח המחוונים של Stripe תחת Developers > API keys.',
    'secret_key_helper' => 'מצא זאת בלוח המחוונים של Stripe תחת Developers > API keys. שמור מפתח זה בסודיות.',
    'webhook_setup_guide' => [
        'title' => 'מדריך התקנת Stripe Webhook',
        'description' => 'עקוב אחר השלבים הבאים כדי להגדיר webhook של Stripe',
        'step_1_label' => 'התחבר ללוח הבקרה של Stripe',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'בחר אירוע והגדר נקודת קצה',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'הוסף נקודת קצה',
        'step_3_description' => 'לחץ על הכפתור "הוסף נקודת קצה" כדי לשמור את ה-webhook.',
        'step_4_label' => 'העתק סוד חתימה',
        'step_4_description' => 'העתק את הערך "Signing Secret" מהקטע "Webhook Details" והדבק אותו בשדה "Stripe Webhook Secret" בקטע "Stripe" של הכרטיסייה "תשלום" בדף "הגדרות".',
    ],
    'no_payment_charge' => 'אין חיוב תשלום. אנא נסה שוב!',
    'payment_failed' => 'התשלום נכשל!',
    'payment_type' => 'סוג תשלום',
];
