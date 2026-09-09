<?php

return [
    'name' => 'Cronjob',
    'description' => 'ウェブサイトをスムーズに動作させるための自動バックグラウンドタスクを設定します。',
    'is_not_ready' => 'Cronjobはまだ設定されていません',
    'is_not_ready_description' => 'Cronjobを設定するには、以下の手順に従ってください。これは、カート放棄リマインダー、メールスケジューリング、その他の自動タスクなどの機能に必要です。',
    'is_working' => 'Cronjobは正常に動作しています！',
    'is_not_working' => 'Cronjobが停止しました',
    'is_not_working_description' => 'Cronjobは過去10分間実行されていません。サーバー設定を確認するか、ホスティングプロバイダーにお問い合わせください。',
    'last_checked' => '最終アクティビティ: :time',
    'copy_button' => 'コマンドをコピー',
    'what_is' => [
        'title' => 'Cronjobとは？',
        'description' => 'Cronjobは、サーバーのバックグラウンドで実行される自動タスクです。手動で何もしなくても、ウェブサイトが重要なタスクを自動的に実行できるようにします。',
        'examples' => '例',
        'features' => 'カート放棄リマインダーの送信、スケジュールされたメールの処理、古いデータのクリーンアップ、レポートの生成など。',
    ],
    'command' => [
        'title' => 'Cronjobコマンド',
        'description' => 'このコマンドをコピーして、ホスティングコントロールパネルに追加してください。自動タスクを動作させるには、このコマンドを毎分実行する必要があります。',
    ],
    'setup' => [
        'name' => '設定方法',
        'copied' => 'クリップボードにコピーしました！',
        'choose_hosting' => '以下からホスティングコントロールパネルを選択し、ステップバイステップの手順に従ってください：',
    ],
    'cpanel' => [
        'step1' => '<strong>cPanel</strong>アカウントにログインします',
        'step2' => '「Advanced」セクションの<strong>「Cron Jobs」</strong>を見つけてクリックします',
        'step3' => '「Add New Cron Job」で、タイミングドロップダウンから<strong>「Once Per Minute (* * * * *)」</strong>を選択します',
        'step4' => '上でコピーした<strong>コマンドを貼り付け</strong>て「Command」フィールドに入力します',
        'step5' => '<strong>「Add New Cron Job」</strong>をクリックして保存します',
    ],
    'plesk' => [
        'step1' => '<strong>Plesk</strong>コントロールパネルにログインします',
        'step2' => '<strong>「Scheduled Tasks」</strong>（または「Cron Jobs」）に移動します',
        'step3' => '<strong>「Add Task」</strong>または<strong>「Schedule a Task」</strong>をクリックします',
        'step4' => 'スケジュールを<strong>毎分</strong>に設定し、コマンドを貼り付けます',
        'step5' => '<strong>「OK」</strong>または<strong>「Apply」</strong>をクリックして保存します',
    ],
    'directadmin' => [
        'step1' => '<strong>DirectAdmin</strong>パネルにログインします',
        'step2' => '<strong>「Advanced Features」</strong> → <strong>「Cron Jobs」</strong>に移動します',
        'step3' => '<strong>「Add Cron Job」</strong>をクリックします',
        'step4' => 'すべての時間フィールドを<strong>*</strong>（毎分）に設定し、コマンドを貼り付けます',
        'step5' => '<strong>「Add」</strong>をクリックしてcronjobを保存します',
    ],
    'ssh' => [
        'step1' => 'ターミナルまたはPuTTYを使用して<strong>SSH</strong>経由でサーバーに接続します',
        'step2' => '<code>crontab -e</code>と入力してEnterを押し、crontabファイルを編集します',
        'step3' => '下部に新しい行を追加し、<strong>コマンドを貼り付け</strong>ます',
        'step4' => '<strong>Ctrl+X</strong>を押し、次に<strong>Y</strong>、次に<strong>Enter</strong>を押して保存します（nanoエディタの場合）',
        'step5' => 'cronjobがアクティブになり、毎分実行されます',
    ],
    'need_help' => 'ヘルプが必要ですか？ホスティングプロバイダーに連絡し、上記のコマンドで毎分実行されるcronjobを設定するよう依頼してください。',
];
