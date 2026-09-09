<?php

return [
    'payment_description' => '客戶可以購買產品並透過 :name 使用 Visa、信用卡直接付款',
    'api_key' => 'API 密鑰',
    'api_key_helper' => '從您的 Mollie 儀表板獲取 API 密鑰',
    'webhook_secret' => 'Webhook Secret(可選)',
    'webhook_secret_helper' => '可選:新增 webhook secret 以增強安全性。在 Mollie 儀表板的 Developers > Webhooks 下配置此項',
    'register_account' => '在 :name 註冊帳戶',
    'after_registration' => '在 :name 註冊後,您將擁有 API 密鑰',
    'enter_api_key' => '在右側框中輸入 API 密鑰',
    'webhook_configuration' => 'Webhook 配置:',
    'webhook_url_instruction' => '在您的 Mollie 儀表板中,將 webhook URL 配置為:',
    'webhook_note' => '注意:將 {token} 替換為實際的支付令牌。Mollie 將自動呼叫 webhook 以更新支付狀態。',
    'security_optional' => '安全性(可選):',
    'security_instruction' => '為了增強安全性,您可以在 Mollie 儀表板的 Developers > Webhooks 下配置 webhook secret,然後將其輸入到 Webhook Secret 欄位中。',
];
