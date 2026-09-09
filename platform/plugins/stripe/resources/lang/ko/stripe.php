<?php

return [
    'webhook_secret' => 'Webhook 시크릿',
    'webhook_secret_helper' => 'Stripe Dashboard의 Developers > Webhooks > Signing secret에서 확인할 수 있습니다.',
    'public_key_helper' => 'Stripe Dashboard의 Developers > API keys에서 확인할 수 있습니다.',
    'secret_key_helper' => 'Stripe Dashboard의 Developers > API keys에서 확인할 수 있습니다. 이 키는 기밀로 유지하세요.',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook 설정 가이드',
        'description' => 'Stripe webhook을 설정하려면 다음 단계를 따르십시오',
        'step_1_label' => 'Stripe 대시보드에 로그인',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => '이벤트 선택 및 엔드포인트 구성',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => '엔드포인트 추가',
        'step_3_description' => '"엔드포인트 추가" 버튼을 클릭하여 webhook을 저장하십시오.',
        'step_4_label' => '서명 시크릿 복사',
        'step_4_description' => '"Webhook Details" 섹션에서 "Signing Secret" 값을 복사하여 "설정" 페이지의 "결제" 탭에 있는 "Stripe" 섹션의 "Stripe Webhook Secret" 필드에 붙여넣으십시오.',
    ],
    'no_payment_charge' => '결제 수수료가 없습니다. 다시 시도해 주세요!',
    'payment_failed' => '결제 실패!',
    'payment_type' => '결제 유형',
];
