<?php

return [
    'name' => 'Cronjob',
    'description' => 'ตั้งค่างานพื้นหลังอัตโนมัติเพื่อให้เว็บไซต์ของคุณทำงานได้อย่างราบรื่น',
    'is_not_ready' => 'ยังไม่ได้กำหนดค่า Cronjob',
    'is_not_ready_description' => 'กรุณาทำตามคำแนะนำด้านล่างเพื่อตั้งค่า cronjob ซึ่งจำเป็นสำหรับฟีเจอร์ต่างๆ เช่น การแจ้งเตือนตะกร้าสินค้าที่ถูกทิ้ง การกำหนดเวลาอีเมล และงานอัตโนมัติอื่นๆ',
    'is_working' => 'Cronjob ทำงานถูกต้อง!',
    'is_not_working' => 'Cronjob หยุดทำงาน',
    'is_not_working_description' => 'Cronjob ไม่ได้ทำงานใน 10 นาทีที่ผ่านมา กรุณาตรวจสอบการกำหนดค่าเซิร์ฟเวอร์หรือติดต่อผู้ให้บริการโฮสติ้งของคุณ',
    'last_checked' => 'กิจกรรมล่าสุด: :time',
    'copy_button' => 'คัดลอกคำสั่ง',
    'what_is' => [
        'title' => 'Cronjob คืออะไร?',
        'description' => 'Cronjob คืองานอัตโนมัติที่ทำงานเบื้องหลังบนเซิร์ฟเวอร์ของคุณ ช่วยให้เว็บไซต์ของคุณทำงานสำคัญโดยอัตโนมัติโดยไม่ต้องทำอะไรด้วยตนเอง',
        'examples' => 'ตัวอย่าง',
        'features' => 'ส่งการแจ้งเตือนตะกร้าสินค้าที่ถูกทิ้ง ประมวลผลอีเมลที่กำหนดเวลา ล้างข้อมูลเก่า สร้างรายงาน และอื่นๆ',
    ],
    'command' => [
        'title' => 'คำสั่ง Cronjob ของคุณ',
        'description' => 'คัดลอกคำสั่งนี้และเพิ่มลงในแผงควบคุมโฮสติ้งของคุณ คำสั่งนี้ต้องทำงานทุกนาทีเพื่อให้งานอัตโนมัติทำงาน',
    ],
    'setup' => [
        'name' => 'วิธีการตั้งค่า',
        'copied' => 'คัดลอกไปยังคลิปบอร์ดแล้ว!',
        'choose_hosting' => 'เลือกแผงควบคุมโฮสติ้งของคุณด้านล่างและทำตามคำแนะนำทีละขั้นตอน:',
    ],
    'cpanel' => [
        'step1' => 'เข้าสู่ระบบบัญชี <strong>cPanel</strong> ของคุณ',
        'step2' => 'ค้นหาและคลิกที่ <strong>"Cron Jobs"</strong> ในส่วน "Advanced"',
        'step3' => 'ใน "Add New Cron Job" เลือก <strong>"Once Per Minute (* * * * *)"</strong> จากเมนูแบบเลื่อนลง',
        'step4' => '<strong>วางคำสั่ง</strong>ที่คุณคัดลอกไว้ด้านบนลงในช่อง "Command"',
        'step5' => 'คลิก <strong>"Add New Cron Job"</strong> เพื่อบันทึก',
    ],
    'plesk' => [
        'step1' => 'เข้าสู่ระบบแผงควบคุม <strong>Plesk</strong> ของคุณ',
        'step2' => 'ไปที่ <strong>"Scheduled Tasks"</strong> (หรือ "Cron Jobs")',
        'step3' => 'คลิก <strong>"Add Task"</strong> หรือ <strong>"Schedule a Task"</strong>',
        'step4' => 'ตั้งค่าให้ทำงาน<strong>ทุกนาที</strong>และวางคำสั่ง',
        'step5' => 'คลิก <strong>"OK"</strong> หรือ <strong>"Apply"</strong> เพื่อบันทึก',
    ],
    'directadmin' => [
        'step1' => 'เข้าสู่ระบบแผง <strong>DirectAdmin</strong> ของคุณ',
        'step2' => 'ไปที่ <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'คลิก <strong>"Add Cron Job"</strong>',
        'step4' => 'ตั้งค่าช่องเวลาทั้งหมดเป็น <strong>*</strong> (ทุกนาที) และวางคำสั่ง',
        'step5' => 'คลิก <strong>"Add"</strong> เพื่อบันทึก cronjob',
    ],
    'ssh' => [
        'step1' => 'เชื่อมต่อกับเซิร์ฟเวอร์ผ่าน <strong>SSH</strong> โดยใช้ Terminal หรือ PuTTY',
        'step2' => 'พิมพ์ <code>crontab -e</code> และกด Enter เพื่อแก้ไขไฟล์ crontab',
        'step3' => 'เพิ่มบรรทัดใหม่ที่ด้านล่างและ<strong>วางคำสั่ง</strong>',
        'step4' => 'กด <strong>Ctrl+X</strong> จากนั้น <strong>Y</strong> จากนั้น <strong>Enter</strong> เพื่อบันทึก (สำหรับ nano editor)',
        'step5' => 'Cronjob ทำงานแล้วและจะทำงานทุกนาที',
    ],
    'need_help' => 'ต้องการความช่วยเหลือ? ติดต่อผู้ให้บริการโฮสติ้งของคุณและขอให้ตั้งค่า cronjob ที่ทำงานทุกนาทีด้วยคำสั่งที่แสดงด้านบน',
];
