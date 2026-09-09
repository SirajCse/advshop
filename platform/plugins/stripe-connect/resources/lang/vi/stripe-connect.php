<?php

return [
    'settings' => [],
    'stripe_account_id' => 'ID tài khoản Stripe',
    'go_to_dashboard' => 'Đến bảng điều khiển Express',
    'connect' => [
        'label' => 'Kết nối với Stripe',
        'description' => 'Kết nối tài khoản Stripe của bạn để nhận thanh toán.',
    ],
    'disconnect' => [
        'label' => 'Ngắt kết nối Stripe',
        'confirm' => 'Bạn có chắc chắn muốn ngắt kết nối tài khoản Stripe của mình không?',
    ],
    'notifications' => [
        'connected' => 'Tài khoản Stripe của bạn đã được kết nối.',
        'disconnected' => 'Tài khoản Stripe của bạn đã bị ngắt kết nối.',
        'now_active' => 'Tài khoản Stripe của bạn hiện đang hoạt động.',
    ],
    'withdrawal' => [
        'payout_info' => 'Khoản thanh toán của bạn sẽ được chuyển tự động đến tài khoản Stripe có ID: :stripe_account_id.',
    ],
];
