<?php

return [
    'payment_description' => '客户可以购买产品并通过 :name 使用 Visa、信用卡直接付款',
    'api_key' => 'API 密钥',
    'api_key_helper' => '从您的 Mollie 仪表板获取 API 密钥',
    'webhook_secret' => 'Webhook Secret(可选)',
    'webhook_secret_helper' => '可选:添加 webhook secret 以增强安全性。在 Mollie 仪表板的 Developers > Webhooks 下配置此项',
    'register_account' => '在 :name 注册账户',
    'after_registration' => '在 :name 注册后,您将拥有 API 密钥',
    'enter_api_key' => '在右侧框中输入 API 密钥',
    'webhook_configuration' => 'Webhook 配置:',
    'webhook_url_instruction' => '在您的 Mollie 仪表板中,将 webhook URL 配置为:',
    'webhook_note' => '注意:将 {token} 替换为实际的支付令牌。Mollie 将自动调用 webhook 以更新支付状态。',
    'security_optional' => '安全性(可选):',
    'security_instruction' => '为了增强安全性,您可以在 Mollie 仪表板的 Developers > Webhooks 下配置 webhook secret,然后将其输入到 Webhook Secret 字段中。',
];
