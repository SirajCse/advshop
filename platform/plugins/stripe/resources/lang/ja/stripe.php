<?php

return [
    'webhook_secret' => 'Webhook シークレット',
    'webhook_secret_helper' => 'Stripe Dashboard の Developers > Webhooks > Signing secret から確認できます。',
    'public_key_helper' => 'Stripe Dashboard の Developers > API keys から確認できます。',
    'secret_key_helper' => 'Stripe Dashboard の Developers > API keys から確認できます。このキーは機密情報として取り扱ってください。',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook セットアップガイド',
        'description' => 'Stripe Webhook を設定するには、次の手順に従います。',
        'step_1_label' => 'Stripe ダッシュボードにログインする',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'イベントを選択してエンドポイントを構成する',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'エンドポイントの追加',
        'step_3_description' => '「エンドポイントの追加」ボタンをクリックして Webhook を保存します。',
        'step_4_label' => '署名シークレットをコピーする',
        'step_4_description' => '「Webhook の詳細」セクションから「Signing Secret」の値をコピーし、「設定」ページの「Payment」タブの「Stripe」セクションにある「Stripe Webhook Secret」フィールドに貼り付けます。',
    ],
    'no_payment_charge' => '支払い料金がかかりません。もう一度お試しください！',
    'payment_failed' => '支払いに失敗しました！',
    'payment_type' => '支払いタイプ',
];
