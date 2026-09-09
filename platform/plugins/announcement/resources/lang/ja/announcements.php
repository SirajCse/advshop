<?php

return [
    'name' => 'お知らせ',

    'enums' => [
        'announce_placement' => [
            'top' => '上部',
            'bottom' => '下部に固定',
            'popup' => 'ポップアップ',
            'theme' => 'テーマ組み込み',
        ],

        'text_alignment' => [
            'start' => '開始',
            'center' => '中央',
        ],
    ],

    'validation' => [
        'font_size' => 'フォントサイズは有効なCSSフォントサイズ値である必要があります。',
        'text_color' => 'テキストの色は有効な16進数カラー値である必要があります。',
    ],

    'create' => '新しいお知らせを作成',
    'add_new' => '新規追加',
    'settings' => [
        'name' => 'お知らせ',
        'description' => 'お知らせ設定を管理',
    ],

    'background_color' => '背景色',
    'font_size' => 'フォントサイズ',
    'font_size_help' => 'デフォルトを使用する場合は空のままにします。例：1rem、1em、12px、...',
    'text_color' => 'テキストの色',
    'start_date' => '開始日',
    'end_date' => '終了日',
    'has_action' => 'アクションあり',
    'action_label' => 'アクションラベル',
    'action_url' => 'アクションURL',
    'action_open_new_tab' => '新しいタブで開く',
    'dismissible_label' => 'ユーザーがお知らせを閉じることを許可',
    'placement' => '配置',
    'text_alignment' => 'テキスト配置',
    'is_active' => '有効',
    'max_width' => '最大幅',
    'max_width_help' => 'デフォルト値を使用する場合は空のままにします。例：100%、500px、...',
    'max_width_unit' => '最大幅単位',
    'font_size_unit' => 'フォントサイズ単位',
    'autoplay_label' => '自動再生',
    'autoplay_delay_label' => '自動再生の遅延',
    'autoplay_delay_help' => '各お知らせ間の遅延（ミリ秒）。デフォルト値を使用する場合は空のままにします（5000）。',
    'lazy_loading' => '遅延読み込み',
    'lazy_loading_description' => 'ページの読み込み速度を向上させるには、このオプションを有効にします',
    'hide_on_mobile' => 'モバイルで非表示',
    'dismiss' => '閉じる',

    // Placeholders and help texts
    'name_placeholder' => 'お知らせ名を入力',
    'name_help' => '内部参照用の名前のみ、ユーザーには表示されません',
    'content_placeholder' => 'お知らせメッセージをここに入力...',
    'content_help' => 'ユーザーに表示されるメッセージ。HTMLフォーマットをサポートします。',
    'start_date_placeholder' => '開始日時を選択',
    'start_date_help' => 'お知らせはこの日から表示されます。すぐに開始するには空のままにします。',
    'end_date_placeholder' => '終了日時を選択',
    'end_date_help' => 'お知らせはこの日以降非表示になります。有効期限なしの場合は空のままにします。',
    'has_action_help' => 'お知らせにコールトゥアクションボタンを追加',
    'action_label_placeholder' => '例：詳細を見る、今すぐ購入',
    'action_label_help' => 'アクションボタンに表示されるテキスト',
    'action_url_placeholder' => 'https://example.com/page',
    'action_url_help' => 'アクションボタンをクリックしたときにユーザーがリダイレクトされるURL',
    'action_open_new_tab_help' => 'アクションリンクを新しいブラウザタブで開く',
    'is_active_help' => 'このお知らせを削除せずに有効または無効にします',
];
