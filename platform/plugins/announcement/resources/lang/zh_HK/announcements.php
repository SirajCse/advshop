<?php

return [
    'name' => '公告',

    'enums' => [
        'announce_placement' => [
            'top' => '頂部',
            'bottom' => '底部固定',
            'popup' => '彈窗',
            'theme' => '主題內置',
        ],

        'text_alignment' => [
            'start' => '開始',
            'center' => '居中',
        ],
    ],

    'validation' => [
        'font_size' => '字體大小必須是有效的CSS字體大小值。',
        'text_color' => '文本顏色必須是有效的十六進制顏色值。',
    ],

    'create' => '創建新公告',
    'add_new' => '添加新項',
    'settings' => [
        'name' => '公告',
        'description' => '管理公告設置',
    ],

    'background_color' => '背景顏色',
    'font_size' => '字體大小',
    'font_size_help' => '留空以使用默認值。示例：1rem、1em、12px等',
    'text_color' => '文本顏色',
    'start_date' => '開始日期',
    'end_date' => '結束日期',
    'has_action' => '有操作',
    'action_label' => '操作標籤',
    'action_url' => '操作URL',
    'action_open_new_tab' => '在新標籤頁中打開',
    'dismissible_label' => '允許用戶關閉公告',
    'placement' => '位置',
    'text_alignment' => '文本對齊',
    'is_active' => '啟用',
    'max_width' => '最大寬度',
    'max_width_help' => '留空以使用默認值。示例：100%、500px等',
    'max_width_unit' => '最大寬度單位',
    'font_size_unit' => '字體大小單位',
    'autoplay_label' => '自動播放',
    'autoplay_delay_label' => '自動播放延遲',
    'autoplay_delay_help' => '每個公告之間的延遲（毫秒）。留空以使用默認值（5000）。',
    'lazy_loading' => '延遲加載',
    'lazy_loading_description' => '啟用此選項以提高頁面加載速度',
    'hide_on_mobile' => '在移動設備上隱藏',
    'dismiss' => '關閉',

    // Placeholders and help texts
    'name_placeholder' => '輸入公告名稱',
    'name_help' => '名稱僅供內部參考，用戶不可見',
    'content_placeholder' => '在此輸入您的公告消息...',
    'content_help' => '將顯示給用戶的消息。支持HTML格式。',
    'start_date_placeholder' => '選擇開始日期和時間',
    'start_date_help' => '公告將從此日期開始顯示。留空以立即開始。',
    'end_date_placeholder' => '選擇結束日期和時間',
    'end_date_help' => '公告將在此日期後隱藏。留空表示無過期時間。',
    'has_action_help' => '向您的公告添加號召性用語按鈕',
    'action_label_placeholder' => '例如：了解更多、立即購買',
    'action_label_help' => '在操作按鈕上顯示的文本',
    'action_url_placeholder' => 'https://example.com/page',
    'action_url_help' => '點擊操作按鈕時用戶將被重定向到的URL',
    'action_open_new_tab_help' => '在新瀏覽器標籤頁中打開操作鏈接',
    'is_active_help' => '啟用或禁用此公告而不刪除它',
];
