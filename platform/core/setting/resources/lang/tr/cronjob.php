<?php

return [
    'name' => 'Cronjob',
    'description' => 'Web sitenizin sorunsuz çalışması için otomatik arka plan görevlerini ayarlayın.',
    'is_not_ready' => 'Cronjob henüz yapılandırılmadı',
    'is_not_ready_description' => 'Cronjob\'u kurmak için lütfen aşağıdaki talimatları izleyin. Bu, terk edilmiş sepet hatırlatıcıları, e-posta planlama ve diğer otomatik görevler gibi özellikler için gereklidir.',
    'is_working' => 'Cronjob düzgün çalışıyor!',
    'is_not_working' => 'Cronjob çalışmayı durdurdu',
    'is_not_working_description' => 'Cronjob son 10 dakikadır çalışmadı. Lütfen sunucu yapılandırmanızı kontrol edin veya barındırma sağlayıcınızla iletişime geçin.',
    'last_checked' => 'Son aktivite: :time',
    'copy_button' => 'Komutu kopyala',
    'what_is' => [
        'title' => 'Cronjob nedir?',
        'description' => 'Cronjob, sunucunuzda arka planda çalışan otomatik bir görevdir. Web sitenizin önemli görevleri manuel olarak hiçbir şey yapmanıza gerek kalmadan otomatik olarak gerçekleştirmesine olanak tanır.',
        'examples' => 'Örnekler',
        'features' => 'Terk edilmiş sepet hatırlatıcıları gönderme, planlanmış e-postaları işleme, eski verileri temizleme, raporlar oluşturma ve daha fazlası.',
    ],
    'command' => [
        'title' => 'Cronjob komutunuz',
        'description' => 'Bu komutu kopyalayın ve barındırma kontrol panelinize ekleyin. Otomatik görevlerinizin çalışması için bu komutun her dakika çalışması gerekir.',
    ],
    'setup' => [
        'name' => 'Nasıl kurulur',
        'copied' => 'Panoya kopyalandı!',
        'choose_hosting' => 'Aşağıdan barındırma kontrol panelinizi seçin ve adım adım talimatları izleyin:',
    ],
    'cpanel' => [
        'step1' => '<strong>cPanel</strong> hesabınıza giriş yapın',
        'step2' => '"Advanced" bölümünde <strong>"Cron Jobs"</strong>\'u bulun ve tıklayın',
        'step3' => '"Add New Cron Job" altında, zamanlama açılır menüsünden <strong>"Once Per Minute (* * * * *)"</strong> seçin',
        'step4' => 'Yukarıda kopyaladığınız <strong>komutu yapıştırın</strong> ve "Command" alanına girin',
        'step5' => 'Kaydetmek için <strong>"Add New Cron Job"</strong>\'a tıklayın',
    ],
    'plesk' => [
        'step1' => '<strong>Plesk</strong> kontrol panelinize giriş yapın',
        'step2' => '<strong>"Scheduled Tasks"</strong> (veya "Cron Jobs") bölümüne gidin',
        'step3' => '<strong>"Add Task"</strong> veya <strong>"Schedule a Task"</strong>\'a tıklayın',
        'step4' => 'Zamanlamayı <strong>her dakika</strong> çalışacak şekilde ayarlayın ve komutu yapıştırın',
        'step5' => 'Kaydetmek için <strong>"OK"</strong> veya <strong>"Apply"</strong>\'a tıklayın',
    ],
    'directadmin' => [
        'step1' => '<strong>DirectAdmin</strong> panelinize giriş yapın',
        'step2' => '<strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong> bölümüne gidin',
        'step3' => '<strong>"Add Cron Job"</strong>\'a tıklayın',
        'step4' => 'Tüm zaman alanlarını <strong>*</strong> (her dakika) olarak ayarlayın ve komutu yapıştırın',
        'step5' => 'Cronjob\'u kaydetmek için <strong>"Add"</strong>\'a tıklayın',
    ],
    'ssh' => [
        'step1' => 'Terminal veya PuTTY kullanarak <strong>SSH</strong> ile sunucunuza bağlanın',
        'step2' => '<code>crontab -e</code> yazın ve crontab dosyasını düzenlemek için Enter\'a basın',
        'step3' => 'Alt kısma yeni bir satır ekleyin ve <strong>komutu yapıştırın</strong>',
        'step4' => 'Kaydetmek için <strong>Ctrl+X</strong>, ardından <strong>Y</strong>, ardından <strong>Enter</strong>\'a basın (nano editör için)',
        'step5' => 'Cronjob artık aktif ve her dakika çalışacak',
    ],
    'need_help' => 'Yardıma mı ihtiyacınız var? Barındırma sağlayıcınızla iletişime geçin ve yukarıda gösterilen komutla her dakika çalışan bir cronjob kurmalarını isteyin.',
];
