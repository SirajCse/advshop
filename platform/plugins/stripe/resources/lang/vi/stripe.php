<?php

return [
    'settings' => [],
    'webhook_secret' => 'Bí mật Webhook',
    'webhook_secret_helper' => 'Tìm thấy trong Stripe Dashboard của bạn tại Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Tìm thấy trong Stripe Dashboard của bạn tại Developers > API keys.',
    'secret_key_helper' => 'Tìm thấy trong Stripe Dashboard của bạn tại Developers > API keys. Giữ bí mật khóa này.',
    'webhook_setup_guide' => [
        'title' => 'Hướng dẫn thiết lập Webhook Stripe',
        'description' => 'Làm theo các bước sau để thiết lập webhook Stripe',
        'step_1_label' => 'Đăng nhập vào Bảng điều khiển Stripe',
        'step_1_description' => 'Truy cập :link và nhấp vào nút "Add Endpoint" trong phần "Webhooks" của tab "Developers".',
        'step_2_label' => 'Chọn sự kiện và cấu hình Endpoint',
        'step_2_description' => 'Chọn sự kiện "payment_intent.succeeded" và nhập URL sau vào trường "Endpoint URL": :url',
        'step_3_label' => 'Thêm Endpoint',
        'step_3_description' => 'Nhấp vào nút "Add Endpoint" để lưu webhook.',
        'step_4_label' => 'Sao chép Signing Secret',
        'step_4_description' => 'Sao chép giá trị "Signing Secret" từ phần "Webhook Details" và dán vào trường "Stripe Webhook Secret" trong mục "Stripe" của tab "Payment" trong trang "Settings".',
    ],
    'webhook_setup_steps' => [],
    'payment_failed' => 'Thanh toán thất bại hoặc bị hủy bởi người dùng.',
    'stripe_connect' => [],
    'no_payment_charge' => 'Không có phí thanh toán. Vui lòng thử lại!',
    'payment_type' => 'Loại Thanh Toán',
];
