<?php

return [
    'webhook_secret' => 'ความลับของ Webhook',
    'webhook_secret_helper' => 'ค้นหาสิ่งนี้ใน Stripe Dashboard ของคุณภายใต้ Developers > Webhooks > Signing secret',
    'public_key_helper' => 'ค้นหาสิ่งนี้ใน Stripe Dashboard ของคุณภายใต้ Developers > API keys',
    'secret_key_helper' => 'ค้นหาสิ่งนี้ใน Stripe Dashboard ของคุณภายใต้ Developers > API keys โปรดเก็บรักษาคีย์นี้เป็นความลับ',
    'webhook_setup_guide' => [
        'title' => 'คู่มือการตั้งค่า Stripe Webhook',
        'description' => 'ทำตามขั้นตอนเหล่านี้เพื่อตั้งค่า Stripe webhook',
        'step_1_label' => 'เข้าสู่ระบบแดชบอร์ด Stripe',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'เลือกเหตุการณ์และกำหนดค่าปลายทาง',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'เพิ่มปลายทาง',
        'step_3_description' => 'คลิกปุ่ม "เพิ่มปลายทาง" เพื่อบันทึก webhook',
        'step_4_label' => 'คัดลอกความลับการลงนาม',
        'step_4_description' => 'คัดลอกค่า "Signing Secret" จากส่วน "Webhook Details" และวางลงในฟิลด์ "Stripe Webhook Secret" ในส่วน "Stripe" ของแท็บ "การชำระเงิน" ในหน้า "การตั้งค่า"',
    ],
    'no_payment_charge' => 'ไม่มีค่าธรรมเนียมการชำระเงิน โปรดลองอีกครั้ง!',
    'payment_failed' => 'การชำระเงินล้มเหลว!',
    'payment_type' => 'ประเภทการชำระเงิน',
];
