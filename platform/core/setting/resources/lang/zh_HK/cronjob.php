<?php

return [
    'name' => '定時任務',
    'description' => '設置自動後台任務，保持您的網站順暢運行。',
    'is_not_ready' => '定時任務尚未配置',
    'is_not_ready_description' => '請按照以下說明設置定時任務。這對於購物車放棄提醒、郵件調度和其他自動化任務等功能是必需的。',
    'is_working' => '定時任務運行正常！',
    'is_not_working' => '定時任務已停止工作',
    'is_not_working_description' => '定時任務在過去10分鐘內未運行。請檢查您的伺服器配置或聯繫您的主機提供商。',
    'last_checked' => '最後活動：:time',
    'copy_button' => '複製命令',
    'what_is' => [
        'title' => '什麼是定時任務？',
        'description' => '定時任務是在伺服器後台運行的自動化任務。它允許您的網站自動執行重要任務，無需您手動操作。',
        'examples' => '範例',
        'features' => '發送購物車放棄提醒、處理預定郵件、清理舊數據、生成報告等。',
    ],
    'command' => [
        'title' => '您的定時任務命令',
        'description' => '複製此命令並添加到您的主機控制面板。此命令需要每分鐘運行一次，以保持自動化任務正常工作。',
    ],
    'setup' => [
        'name' => '如何設置',
        'copied' => '已複製到剪貼板！',
        'choose_hosting' => '選擇下面的主機控制面板，按照分步說明操作：',
    ],
    'cpanel' => [
        'step1' => '登入您的 <strong>cPanel</strong> 帳戶',
        'step2' => '在「Advanced」部分找到並點擊 <strong>「Cron Jobs」</strong>',
        'step3' => '在「Add New Cron Job」下，從時間下拉選單中選擇 <strong>「Once Per Minute (* * * * *)」</strong>',
        'step4' => '將您上面複製的<strong>命令貼上</strong>到「Command」欄位中',
        'step5' => '點擊 <strong>「Add New Cron Job」</strong> 儲存',
    ],
    'plesk' => [
        'step1' => '登入您的 <strong>Plesk</strong> 控制面板',
        'step2' => '前往 <strong>「Scheduled Tasks」</strong>（或「Cron Jobs」）',
        'step3' => '點擊 <strong>「Add Task」</strong> 或 <strong>「Schedule a Task」</strong>',
        'step4' => '將計劃設置為<strong>每分鐘</strong>運行並貼上命令',
        'step5' => '點擊 <strong>「OK」</strong> 或 <strong>「Apply」</strong> 儲存',
    ],
    'directadmin' => [
        'step1' => '登入您的 <strong>DirectAdmin</strong> 面板',
        'step2' => '導航到 <strong>「Advanced Features」</strong> → <strong>「Cron Jobs」</strong>',
        'step3' => '點擊 <strong>「Add Cron Job」</strong>',
        'step4' => '將所有時間欄位設置為 <strong>*</strong>（每分鐘）並貼上命令',
        'step5' => '點擊 <strong>「Add」</strong> 儲存定時任務',
    ],
    'ssh' => [
        'step1' => '使用終端機或 PuTTY 通過 <strong>SSH</strong> 連接到您的伺服器',
        'step2' => '輸入 <code>crontab -e</code> 並按 Enter 編輯 crontab 檔案',
        'step3' => '在底部添加新行並<strong>貼上命令</strong>',
        'step4' => '按 <strong>Ctrl+X</strong>，然後按 <strong>Y</strong>，然後按 <strong>Enter</strong> 儲存（對於 nano 編輯器）',
        'step5' => '定時任務現在已啟用，將每分鐘運行一次',
    ],
    'need_help' => '需要幫助？聯繫您的主機提供商，請求他們使用上面顯示的命令設置每分鐘運行的定時任務。',
];
