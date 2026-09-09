<?php

return [
    'title' => 'การติดตั้ง',
    'next' => 'ขั้นตอนถัดไป',
    'forms' => [
        'errorTitle' => 'เกิดข้อผิดพลาดต่อไปนี้:',
    ],
    'welcome' => [
        'title' => 'ยินดีต้อนรับ',
        'message' => 'ก่อนที่เราจะเริ่มต้น เราต้องการข้อมูลเกี่ยวกับฐานข้อมูล คุณจะต้องรู้รายการต่อไปนี้ก่อนที่จะดำเนินการต่อ',
        'language' => 'ภาษา',
        'next' => 'มาเริ่มกันเลย',
    ],
    'requirements' => [
        'title' => 'ข้อกำหนดของเซิร์ฟเวอร์',
    ],
    'permissions' => [
        'next' => 'กำหนดค่าสภาพแวดล้อม',
    ],
    'environment' => [
        'wizard' => [
            'title' => 'การตั้งค่าสภาพแวดล้อม',
            'form' => [
                'name_required' => 'จำเป็นต้องมีชื่อสภาพแวดล้อม',
                'app_name_label' => 'ชื่อเว็บไซต์',
                'app_url_label' => 'URL',
                'db_connection_label' => 'การเชื่อมต่อฐานข้อมูล',
                'db_connection_label_mysql' => 'MySQL',
                'db_host_label' => 'โฮสต์ฐานข้อมูล',
                'db_port_label' => 'พอร์ตฐานข้อมูล',
                'db_name_label' => 'ชื่อฐานข้อมูล',
                'db_name_placeholder' => 'ชื่อฐานข้อมูล',
                'db_username_label' => 'ชื่อผู้ใช้ฐานข้อมูล',
                'db_username_placeholder' => 'ชื่อผู้ใช้ฐานข้อมูล',
                'db_password_label' => 'รหัสผ่านฐานข้อมูล',
                'db_password_placeholder' => 'รหัสผ่านฐานข้อมูล',
                'buttons' => [
                    'install' => 'ติดตั้ง',
                ],
                'db_host_helper' => 'หากคุณใช้ Laravel Sail เพียงแค่เปลี่ยน DB_HOST เป็น DB_HOST=mysql ในโฮสติ้งบางแห่ง DB_HOST อาจเป็น localhost แทน 127.0.0.1',
                'db_connections' => [
                    'mysql' => 'MySQL',
                    'sqlite' => 'SQLite',
                    'pgsql' => 'PostgreSQL',
                ],
            ],
        ],
        'success' => 'การตั้งค่าไฟล์ .env ของคุณถูกบันทึกแล้ว',
        'errors' => 'ไม่สามารถบันทึกไฟล์ .env โปรดสร้างด้วยตนเอง',
    ],
    'theme' => [
        'title' => 'เลือกธีม',
        'message' => 'เลือกธีมเพื่อปรับแต่งรูปลักษณ์ของเว็บไซต์ของคุณ การเลือกนี้จะนำเข้าข้อมูลตัวอย่างที่ปรับแต่งตามธีมที่เลือกด้วย',
    ],
    'theme_preset' => [
        'title' => 'เลือกพรีเซ็ตธีม',
        'message' => 'เลือกพรีเซ็ตธีมเพื่อปรับแต่งรูปลักษณ์ของเว็บไซต์ของคุณ การเลือกนี้จะนำเข้าข้อมูลตัวอย่างที่ปรับแต่งตามธีมที่เลือกด้วย',
    ],
    'createAccount' => [
        'title' => 'สร้างบัญชี',
        'form' => [
            'first_name' => 'ชื่อ',
            'last_name' => 'นามสกุล',
            'username' => 'ชื่อผู้ใช้',
            'email' => 'อีเมล',
            'password' => 'รหัสผ่าน',
            'password_confirmation' => 'ยืนยันรหัสผ่าน',
            'create' => 'สร้าง',
        ],
    ],
    'license' => [
        'title' => 'เปิดใช้งานไลเซนส์',
        'skip' => 'ข้ามไปก่อน',
    ],
    'final' => [
        'pageTitle' => 'การติดตั้งเสร็จสมบูรณ์',
        'title' => 'เสร็จสิ้น',
        'message' => 'แอปพลิเคชันได้รับการติดตั้งเรียบร้อยแล้ว',
        'exit' => 'ไปที่แดชบอร์ดผู้ดูแลระบบ',
    ],
    'install_step_title' => 'การติดตั้ง - ขั้นตอน :step: :title',
];
