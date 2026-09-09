<?php

return [
    'title' => 'Cài đặt',
    'next' => 'Bước tiếp theo',
    'forms' => [
        'errorTitle' => 'Có lỗi sau đây:',
    ],
    'welcome' => [
        'title' => 'Chào mừng',
        'message' => 'Trước khi bắt đầu, chúng ta cần một số thông tin về cơ sở dữ liệu. Bạn cần biết các mục sau trước khi tiếp tục.',
        'language' => 'Ngôn ngữ',
        'next' => 'Bắt đầu',
    ],
    'requirements' => [
        'title' => 'Yêu cầu máy chủ',
        'php_version_required' => 'Yêu cầu PHP phiên bản :version trở lên',
    ],
    'permissions' => [
        'next' => 'Cấu hình môi trường',
    ],
    'environment' => [
        'wizard' => [
            'title' => 'Cài đặt môi trường',
            'form' => [
                'name_required' => 'Cần có tên môi trường.',
                'app_name_label' => 'Tiêu đề trang',
                'app_url_label' => 'URL',
                'db_connection_label' => 'Kết nối cơ sở dữ liệu',
                'db_connection_label_mysql' => 'MySQL',
                'db_host_label' => 'Máy chủ cơ sở dữ liệu',
                'db_port_label' => 'Cổng cơ sở dữ liệu',
                'db_name_label' => 'Tên cơ sở dữ liệu',
                'db_name_placeholder' => 'Tên cơ sở dữ liệu',
                'db_username_label' => 'Tên người dùng cơ sở dữ liệu',
                'db_username_placeholder' => 'Tên người dùng cơ sở dữ liệu',
                'db_password_label' => 'Mật khẩu cơ sở dữ liệu',
                'db_password_placeholder' => 'Mật khẩu cơ sở dữ liệu',
                'buttons' => [
                    'install' => 'Cài đặt',
                ],
                'db_host_helper' => 'Nếu bạn sử dụng Laravel Sail, chỉ cần thay đổi DB_HOST thành DB_HOST = mysql. Trên một số hosting, DB_HOST có thể là localhost thay vì 127.0.0.1',
                'db_connections' => [
                    'mysql' => 'MySQL',
                    'sqlite' => 'SQLite',
                    'pgsql' => 'PostgreSQL',
                ],
            ],
        ],
        'success' => 'Cài đặt file .env của bạn đã được lưu.',
        'errors' => 'Không thể lưu file .env, Vui lòng tạo nó thủ công.',
    ],
    'theme' => [
        'title' => 'Chọn chủ đề',
        'message' => 'Chọn một chủ đề để cá nhân hóa giao diện trang web của bạn. Lựa chọn này cũng sẽ nhập dữ liệu mẫu phù hợp với chủ đề đã chọn.',
    ],
    'createAccount' => [
        'title' => 'Tạo tài khoản',
        'form' => [
            'first_name' => 'Tên',
            'last_name' => 'Họ',
            'username' => 'Tên người dùng',
            'email' => 'E-mail',
            'password' => 'Mật khẩu',
            'password_confirmation' => 'Xác nhận mật khẩu',
            'create' => 'Tạo',
        ],
    ],
    'license' => [
        'title' => 'Kích hoạt giấy phép',
        'skip' => 'Bỏ qua tạm thời',
    ],
    'final' => [
        'pageTitle' => 'Cài đặt đã hoàn thành',
        'title' => 'Hoàn tất',
        'message' => 'Ứng dụng đã được cài đặt thành công.',
        'exit' => 'Điều hướng đến bảng điều khiển quản trị',
    ],
    'install_step_title' => 'Cài đặt - Bước :step: :title',
    'theme_preset' => [
        'title' => 'Mẫu giao diện',
        'message' => 'Chọn mẫu giao diện bạn muốn sử dụng',
    ],
];
