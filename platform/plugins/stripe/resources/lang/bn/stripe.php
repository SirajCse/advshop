<?php

return [
    'webhook_secret' => 'ওয়েবহুক সিক্রেট',
    'webhook_secret_helper' => 'এটি আপনার Stripe Dashboard-এ Developers > Webhooks > Signing secret-এর অধীনে পাবেন।',
    'public_key_helper' => 'এটি আপনার Stripe Dashboard-এ Developers > API keys-এর অধীনে পাবেন।',
    'secret_key_helper' => 'এটি আপনার Stripe Dashboard-এ Developers > API keys-এর অধীনে পাবেন। এই কীটি গোপন রাখুন।',
    'webhook_setup_guide' => [
        'title' => 'স্ট্রাইপ ওয়েবহুক সেটআপ গাইড',
        'description' => 'একটি স্ট্রাইপ ওয়েবহুক সেট আপ করতে এই পদক্ষেপগুলি অনুসরণ করুন',
        'step_1_label' => 'স্ট্রাইপ ড্যাশবোর্ডে লগইন করুন',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'ইভেন্ট নির্বাচন করুন এবং এন্ডপয়েন্ট কনফিগার করুন',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'এন্ডপয়েন্ট যোগ করুন',
        'step_3_description' => 'ওয়েবহুক সংরক্ষণ করতে "এন্ডপয়েন্ট যোগ করুন" বোতামে ক্লিক করুন।',
        'step_4_label' => 'সাইনিং সিক্রেট কপি করুন',
        'step_4_description' => '"ওয়েবহুক বিবরণ" বিভাগ থেকে "সাইনিং সিক্রেট" মানটি অনুলিপি করুন এবং "সেটিংস" পৃষ্ঠার "পেমেন্ট" ট্যাবের "স্ট্রাইপ" বিভাগে "স্ট্রাইপ ওয়েবহুক সিক্রেট" ক্ষেত্রে পেস্ট করুন।',
    ],
    'no_payment_charge' => 'কোন পেমেন্ট চার্জ নেই। আবার চেষ্টা করুন!',
    'payment_failed' => 'পেমেন্ট ব্যর্থ হয়েছে!',
    'payment_type' => 'পেমেন্ট টাইপ',
];
