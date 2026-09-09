<?php

return [
    'name' => 'Cronjob',
    'description' => 'Thiết lập các tác vụ nền tự động để giữ cho trang web của bạn hoạt động trơn tru.',
    'is_not_ready' => 'Cronjob chưa được cấu hình',
    'is_not_ready_description' => 'Vui lòng làm theo hướng dẫn bên dưới để thiết lập cronjob. Điều này cần thiết cho các tính năng như nhắc nhở giỏ hàng bị bỏ, lên lịch email và các tác vụ tự động khác.',
    'is_working' => 'Cronjob đang hoạt động bình thường!',
    'is_not_working' => 'Cronjob đã ngừng hoạt động',
    'is_not_working_description' => 'Cronjob đã không chạy trong 10 phút qua. Vui lòng kiểm tra cấu hình máy chủ của bạn hoặc liên hệ với nhà cung cấp dịch vụ lưu trữ.',
    'last_checked' => 'Hoạt động lần cuối: :time',
    'copy_button' => 'Sao chép lệnh',
    'what_is' => [
        'title' => 'Cronjob là gì?',
        'description' => 'Cronjob là một tác vụ tự động chạy ở nền trên máy chủ của bạn. Nó cho phép trang web của bạn thực hiện các tác vụ quan trọng tự động mà không cần bạn phải làm gì thủ công.',
        'examples' => 'Ví dụ',
        'features' => 'Gửi nhắc nhở giỏ hàng bị bỏ, xử lý email theo lịch, dọn dẹp dữ liệu cũ, tạo báo cáo và nhiều hơn nữa.',
    ],
    'command' => [
        'title' => 'Lệnh Cronjob của bạn',
        'description' => 'Sao chép lệnh này và thêm vào bảng điều khiển hosting của bạn. Lệnh này cần chạy mỗi phút để các tác vụ tự động hoạt động.',
    ],
    'setup' => [
        'name' => 'Cách thiết lập',
        'copied' => 'Đã sao chép vào clipboard!',
        'choose_hosting' => 'Chọn bảng điều khiển hosting của bạn bên dưới và làm theo hướng dẫn từng bước:',
    ],
    'cpanel' => [
        'step1' => 'Đăng nhập vào tài khoản <strong>cPanel</strong> của bạn',
        'step2' => 'Tìm và nhấp vào <strong>"Cron Jobs"</strong> trong phần "Advanced"',
        'step3' => 'Trong "Add New Cron Job", chọn <strong>"Once Per Minute (* * * * *)"</strong> từ dropdown thời gian',
        'step4' => '<strong>Dán lệnh</strong> bạn đã sao chép ở trên vào trường "Command"',
        'step5' => 'Nhấp <strong>"Add New Cron Job"</strong> để lưu',
    ],
    'plesk' => [
        'step1' => 'Đăng nhập vào bảng điều khiển <strong>Plesk</strong> của bạn',
        'step2' => 'Đi đến <strong>"Scheduled Tasks"</strong> (hoặc "Cron Jobs")',
        'step3' => 'Nhấp <strong>"Add Task"</strong> hoặc <strong>"Schedule a Task"</strong>',
        'step4' => 'Đặt lịch chạy <strong>mỗi phút</strong> và dán lệnh',
        'step5' => 'Nhấp <strong>"OK"</strong> hoặc <strong>"Apply"</strong> để lưu',
    ],
    'directadmin' => [
        'step1' => 'Đăng nhập vào bảng điều khiển <strong>DirectAdmin</strong> của bạn',
        'step2' => 'Điều hướng đến <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Nhấp <strong>"Add Cron Job"</strong>',
        'step4' => 'Đặt tất cả các trường thời gian thành <strong>*</strong> (mỗi phút) và dán lệnh',
        'step5' => 'Nhấp <strong>"Add"</strong> để lưu cronjob',
    ],
    'ssh' => [
        'step1' => 'Kết nối với máy chủ của bạn qua <strong>SSH</strong> bằng Terminal hoặc PuTTY',
        'step2' => 'Gõ <code>crontab -e</code> và nhấn Enter để chỉnh sửa tệp crontab',
        'step3' => 'Thêm một dòng mới ở cuối và <strong>dán lệnh</strong>',
        'step4' => 'Nhấn <strong>Ctrl+X</strong>, sau đó <strong>Y</strong>, sau đó <strong>Enter</strong> để lưu (cho trình soạn thảo nano)',
        'step5' => 'Cronjob bây giờ đã hoạt động và sẽ chạy mỗi phút',
    ],
    'need_help' => 'Cần trợ giúp? Liên hệ với nhà cung cấp hosting của bạn và yêu cầu họ thiết lập cronjob chạy mỗi phút với lệnh hiển thị ở trên.',
];
