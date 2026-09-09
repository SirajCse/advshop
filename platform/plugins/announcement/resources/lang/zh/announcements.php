<?php

return [
    'name' => '公告',

    'enums' => [
        'announce_placement' => [
            'top' => '顶部',
            'bottom' => '底部固定',
            'popup' => '弹窗',
            'theme' => '主题内置',
        ],

        'text_alignment' => [
            'start' => '开始',
            'center' => '居中',
        ],
    ],

    'validation' => [
        'font_size' => '字体大小必须是有效的CSS字体大小值。',
        'text_color' => '文本颜色必须是有效的十六进制颜色值。',
    ],

    'create' => '创建新公告',
    'add_new' => '添加新项',
    'settings' => [
        'name' => '公告',
        'description' => '管理公告设置',
    ],

    'background_color' => '背景颜色',
    'font_size' => '字体大小',
    'font_size_help' => '留空以使用默认值。示例：1rem、1em、12px等',
    'text_color' => '文本颜色',
    'start_date' => '开始日期',
    'end_date' => '结束日期',
    'has_action' => '有操作',
    'action_label' => '操作标签',
    'action_url' => '操作URL',
    'action_open_new_tab' => '在新标签页中打开',
    'dismissible_label' => '允许用户关闭公告',
    'placement' => '位置',
    'text_alignment' => '文本对齐',
    'is_active' => '激活',
    'max_width' => '最大宽度',
    'max_width_help' => '留空以使用默认值。示例：100%、500px等',
    'max_width_unit' => '最大宽度单位',
    'font_size_unit' => '字体大小单位',
    'autoplay_label' => '自动播放',
    'autoplay_delay_label' => '自动播放延迟',
    'autoplay_delay_help' => '每个公告之间的延迟（毫秒）。留空以使用默认值（5000）。',
    'lazy_loading' => '延迟加载',
    'lazy_loading_description' => '启用此选项以提高页面加载速度',
    'hide_on_mobile' => '在移动设备上隐藏',
    'dismiss' => '关闭',

    // Placeholders and help texts
    'name_placeholder' => '输入公告名称',
    'name_help' => '名称仅供内部参考，用户不可见',
    'content_placeholder' => '在此输入您的公告消息...',
    'content_help' => '将显示给用户的消息。支持HTML格式。',
    'start_date_placeholder' => '选择开始日期和时间',
    'start_date_help' => '公告将从此日期开始显示。留空以立即开始。',
    'end_date_placeholder' => '选择结束日期和时间',
    'end_date_help' => '公告将在此日期后隐藏。留空表示无过期时间。',
    'has_action_help' => '向您的公告添加号召性用语按钮',
    'action_label_placeholder' => '例如：了解更多、立即购买',
    'action_label_help' => '在操作按钮上显示的文本',
    'action_url_placeholder' => 'https://example.com/page',
    'action_url_help' => '点击操作按钮时用户将被重定向到的URL',
    'action_open_new_tab_help' => '在新浏览器标签页中打开操作链接',
    'is_active_help' => '启用或禁用此公告而不删除它',
];
