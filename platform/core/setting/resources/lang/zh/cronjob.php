<?php

return [
    'name' => '定时任务',
    'description' => '设置自动后台任务，保持您的网站顺畅运行。',
    'is_not_ready' => '定时任务尚未配置',
    'is_not_ready_description' => '请按照以下说明设置定时任务。这对于购物车放弃提醒、邮件调度和其他自动化任务等功能是必需的。',
    'is_working' => '定时任务运行正常！',
    'is_not_working' => '定时任务已停止工作',
    'is_not_working_description' => '定时任务在过去10分钟内未运行。请检查您的服务器配置或联系您的主机提供商。',
    'last_checked' => '最后活动：:time',
    'copy_button' => '复制命令',
    'what_is' => [
        'title' => '什么是定时任务？',
        'description' => '定时任务是在服务器后台运行的自动化任务。它允许您的网站自动执行重要任务，无需您手动操作。',
        'examples' => '示例',
        'features' => '发送购物车放弃提醒、处理预定邮件、清理旧数据、生成报告等。',
    ],
    'command' => [
        'title' => '您的定时任务命令',
        'description' => '复制此命令并添加到您的主机控制面板。此命令需要每分钟运行一次，以保持自动化任务正常工作。',
    ],
    'setup' => [
        'name' => '如何设置',
        'copied' => '已复制到剪贴板！',
        'choose_hosting' => '选择下面的主机控制面板，按照分步说明操作：',
    ],
    'cpanel' => [
        'step1' => '登录您的 <strong>cPanel</strong> 账户',
        'step2' => '在"Advanced"部分找到并点击 <strong>"Cron Jobs"</strong>',
        'step3' => '在"Add New Cron Job"下，从时间下拉菜单中选择 <strong>"Once Per Minute (* * * * *)"</strong>',
        'step4' => '将您上面复制的<strong>命令粘贴</strong>到"Command"字段中',
        'step5' => '点击 <strong>"Add New Cron Job"</strong> 保存',
    ],
    'plesk' => [
        'step1' => '登录您的 <strong>Plesk</strong> 控制面板',
        'step2' => '转到 <strong>"Scheduled Tasks"</strong>（或"Cron Jobs"）',
        'step3' => '点击 <strong>"Add Task"</strong> 或 <strong>"Schedule a Task"</strong>',
        'step4' => '将计划设置为<strong>每分钟</strong>运行并粘贴命令',
        'step5' => '点击 <strong>"OK"</strong> 或 <strong>"Apply"</strong> 保存',
    ],
    'directadmin' => [
        'step1' => '登录您的 <strong>DirectAdmin</strong> 面板',
        'step2' => '导航到 <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => '点击 <strong>"Add Cron Job"</strong>',
        'step4' => '将所有时间字段设置为 <strong>*</strong>（每分钟）并粘贴命令',
        'step5' => '点击 <strong>"Add"</strong> 保存定时任务',
    ],
    'ssh' => [
        'step1' => '使用终端或 PuTTY 通过 <strong>SSH</strong> 连接到您的服务器',
        'step2' => '输入 <code>crontab -e</code> 并按 Enter 编辑 crontab 文件',
        'step3' => '在底部添加新行并<strong>粘贴命令</strong>',
        'step4' => '按 <strong>Ctrl+X</strong>，然后按 <strong>Y</strong>，然后按 <strong>Enter</strong> 保存（对于 nano 编辑器）',
        'step5' => '定时任务现在已激活，将每分钟运行一次',
    ],
    'need_help' => '需要帮助？联系您的主机提供商，请求他们使用上面显示的命令设置每分钟运行的定时任务。',
];
