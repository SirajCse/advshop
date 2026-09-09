<?php

return [
    'payment_description' => 'Khách hàng có thể mua sản phẩm và thanh toán trực tiếp bằng Visa, thẻ tín dụng qua :name',
    'api_key' => 'Khóa API',
    'api_key_helper' => 'Lấy khóa API của bạn từ Bảng điều khiển Mollie',
    'webhook_secret' => 'Webhook Secret (Tùy chọn)',
    'webhook_secret_helper' => 'Tùy chọn: Thêm webhook secret để tăng cường bảo mật. Cấu hình trong Bảng điều khiển Mollie tại Developers > Webhooks',
    'register_account' => 'Đăng ký tài khoản trên :name',
    'after_registration' => 'Sau khi đăng ký tại :name, bạn sẽ có khóa API',
    'enter_api_key' => 'Nhập khóa API vào ô bên phải',
    'webhook_configuration' => 'Cấu hình Webhook:',
    'webhook_url_instruction' => 'Trong Bảng điều khiển Mollie của bạn, cấu hình URL webhook như sau:',
    'webhook_note' => 'Lưu ý: Thay thế {token} bằng token thanh toán thực tế. Webhook sẽ được Mollie tự động gọi để cập nhật trạng thái thanh toán.',
    'security_optional' => 'Bảo mật (Tùy chọn):',
    'security_instruction' => 'Để tăng cường bảo mật, bạn có thể cấu hình webhook secret trong Bảng điều khiển Mollie tại Developers > Webhooks, sau đó nhập vào trường Webhook Secret.',
];
