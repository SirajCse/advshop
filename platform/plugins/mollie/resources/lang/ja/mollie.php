<?php

return [
    'payment_description' => '顧客は:nameを通じてVisa、クレジットカードを使用して商品を購入し、直接支払うことができます',
    'api_key' => 'APIキー',
    'api_key_helper' => 'MollieダッシュボードからAPIキーを取得してください',
    'webhook_secret' => 'Webhook Secret(オプション)',
    'webhook_secret_helper' => 'オプション:セキュリティ強化のためにwebhook secretを追加します。Mollieダッシュボードの開発者>Webhooksで設定してください',
    'register_account' => ':nameでアカウントを登録',
    'after_registration' => ':nameに登録後、APIキーが発行されます',
    'enter_api_key' => '右側のボックスにAPIキーを入力してください',
    'webhook_configuration' => 'Webhook設定:',
    'webhook_url_instruction' => 'MollieダッシュボードでwebhookのURLを次のように設定します:',
    'webhook_note' => '注意:{token}を実際の支払いトークンに置き換えてください。webhookは支払いステータスを更新するためにMollieによって自動的に呼び出されます。',
    'security_optional' => 'セキュリティ(オプション):',
    'security_instruction' => 'セキュリティ強化のため、Mollieダッシュボードの開発者>Webhooksでwebhook secretを設定し、Webhook Secretフィールドに入力できます。',
];
