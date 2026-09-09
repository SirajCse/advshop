<?php

return [
    'payment_description' => '고객은 :name을 통해 Visa, 신용카드로 제품을 구매하고 직접 결제할 수 있습니다',
    'api_key' => 'API 키',
    'api_key_helper' => 'Mollie 대시보드에서 API 키를 받으세요',
    'webhook_secret' => 'Webhook Secret (선택사항)',
    'webhook_secret_helper' => '선택사항: 보안 강화를 위해 webhook secret을 추가하세요. Mollie 대시보드의 Developers > Webhooks에서 설정하세요',
    'register_account' => ':name에 계정 등록',
    'after_registration' => ':name에 등록 후 API 키가 발급됩니다',
    'enter_api_key' => '오른쪽 상자에 API 키를 입력하세요',
    'webhook_configuration' => 'Webhook 설정:',
    'webhook_url_instruction' => 'Mollie 대시보드에서 webhook URL을 다음과 같이 설정하세요:',
    'webhook_note' => '참고: {token}을 실제 결제 토큰으로 교체하세요. webhook은 결제 상태를 업데이트하기 위해 Mollie에 의해 자동으로 호출됩니다.',
    'security_optional' => '보안 (선택사항):',
    'security_instruction' => '보안 강화를 위해 Mollie 대시보드의 Developers > Webhooks에서 webhook secret을 설정한 후 Webhook Secret 필드에 입력할 수 있습니다.',
];
