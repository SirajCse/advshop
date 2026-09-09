<?php

return [
    'payment_description' => 'ลูกค้าสามารถซื้อสินค้าและชำระเงินโดยตรงโดยใช้ Visa บัตรเครดิตผ่าน :name',
    'api_key' => 'API Key',
    'api_key_helper' => 'รับ API Key ของคุณจากแดชบอร์ด Mollie',
    'webhook_secret' => 'Webhook Secret (ไม่บังคับ)',
    'webhook_secret_helper' => 'ไม่บังคับ: เพิ่ม webhook secret เพื่อความปลอดภัยที่เพิ่มขึ้น กำหนดค่านี้ในแดชบอร์ด Mollie ภายใต้ Developers > Webhooks',
    'register_account' => 'ลงทะเบียนบัญชีที่ :name',
    'after_registration' => 'หลังจากลงทะเบียนที่ :name คุณจะได้รับ API Key',
    'enter_api_key' => 'ป้อน API Key ในช่องด้านขวา',
    'webhook_configuration' => 'การกำหนดค่า Webhook:',
    'webhook_url_instruction' => 'ในแดชบอร์ด Mollie ของคุณ กำหนดค่า URL ของ webhook เป็น:',
    'webhook_note' => 'หมายเหตุ: แทนที่ {token} ด้วยโทเค็นการชำระเงินจริง webhook จะถูกเรียกโดยอัตโนมัติโดย Mollie เพื่ออัปเดตสถานะการชำระเงิน',
    'security_optional' => 'ความปลอดภัย (ไม่บังคับ):',
    'security_instruction' => 'เพื่อความปลอดภัยที่เพิ่มขึ้น คุณสามารถกำหนดค่า webhook secret ในแดชบอร์ด Mollie ภายใต้ Developers > Webhooks จากนั้นป้อนลงในฟิลด์ Webhook Secret',
];
