<?php

return [
    'name' => '크론잡',
    'description' => '웹사이트가 원활하게 작동하도록 자동화된 백그라운드 작업을 설정하세요.',
    'is_not_ready' => '크론잡이 아직 구성되지 않았습니다',
    'is_not_ready_description' => '아래 지침에 따라 크론잡을 설정하세요. 이는 장바구니 포기 알림, 이메일 예약 및 기타 자동화된 작업과 같은 기능에 필요합니다.',
    'is_working' => '크론잡이 정상적으로 작동 중입니다!',
    'is_not_working' => '크론잡이 작동을 멈췄습니다',
    'is_not_working_description' => '크론잡이 지난 10분 동안 실행되지 않았습니다. 서버 구성을 확인하거나 호스팅 제공업체에 문의하세요.',
    'last_checked' => '마지막 활동: :time',
    'copy_button' => '명령어 복사',
    'what_is' => [
        'title' => '크론잡이란?',
        'description' => '크론잡은 서버의 백그라운드에서 실행되는 자동화된 작업입니다. 수동으로 아무것도 하지 않아도 웹사이트가 중요한 작업을 자동으로 수행할 수 있게 해줍니다.',
        'examples' => '예시',
        'features' => '장바구니 포기 알림 전송, 예약된 이메일 처리, 오래된 데이터 정리, 보고서 생성 등.',
    ],
    'command' => [
        'title' => '크론잡 명령어',
        'description' => '이 명령어를 복사하여 호스팅 제어판에 추가하세요. 자동화된 작업이 작동하려면 이 명령어가 매분 실행되어야 합니다.',
    ],
    'setup' => [
        'name' => '설정 방법',
        'copied' => '클립보드에 복사되었습니다!',
        'choose_hosting' => '아래에서 호스팅 제어판을 선택하고 단계별 지침을 따르세요:',
    ],
    'cpanel' => [
        'step1' => '<strong>cPanel</strong> 계정에 로그인합니다',
        'step2' => '"Advanced" 섹션에서 <strong>"Cron Jobs"</strong>를 찾아 클릭합니다',
        'step3' => '"Add New Cron Job"에서 타이밍 드롭다운에서 <strong>"Once Per Minute (* * * * *)"</strong>를 선택합니다',
        'step4' => '위에서 복사한 <strong>명령어를 붙여넣기</strong>하여 "Command" 필드에 입력합니다',
        'step5' => '<strong>"Add New Cron Job"</strong>을 클릭하여 저장합니다',
    ],
    'plesk' => [
        'step1' => '<strong>Plesk</strong> 제어판에 로그인합니다',
        'step2' => '<strong>"Scheduled Tasks"</strong> (또는 "Cron Jobs")로 이동합니다',
        'step3' => '<strong>"Add Task"</strong> 또는 <strong>"Schedule a Task"</strong>를 클릭합니다',
        'step4' => '일정을 <strong>매분</strong> 실행으로 설정하고 명령어를 붙여넣습니다',
        'step5' => '<strong>"OK"</strong> 또는 <strong>"Apply"</strong>를 클릭하여 저장합니다',
    ],
    'directadmin' => [
        'step1' => '<strong>DirectAdmin</strong> 패널에 로그인합니다',
        'step2' => '<strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>로 이동합니다',
        'step3' => '<strong>"Add Cron Job"</strong>을 클릭합니다',
        'step4' => '모든 시간 필드를 <strong>*</strong> (매분)로 설정하고 명령어를 붙여넣습니다',
        'step5' => '<strong>"Add"</strong>를 클릭하여 크론잡을 저장합니다',
    ],
    'ssh' => [
        'step1' => '터미널 또는 PuTTY를 사용하여 <strong>SSH</strong>로 서버에 연결합니다',
        'step2' => '<code>crontab -e</code>를 입력하고 Enter를 눌러 crontab 파일을 편집합니다',
        'step3' => '하단에 새 줄을 추가하고 <strong>명령어를 붙여넣습니다</strong>',
        'step4' => '<strong>Ctrl+X</strong>를 누른 다음 <strong>Y</strong>, 그리고 <strong>Enter</strong>를 눌러 저장합니다 (nano 편집기의 경우)',
        'step5' => '크론잡이 활성화되어 매분 실행됩니다',
    ],
    'need_help' => '도움이 필요하신가요? 호스팅 제공업체에 연락하여 위에 표시된 명령어로 매분 실행되는 크론잡을 설정해 달라고 요청하세요.',
];
