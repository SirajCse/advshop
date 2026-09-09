<?php

return [
    'name' => '공지사항',

    'enums' => [
        'announce_placement' => [
            'top' => '상단',
            'bottom' => '하단 고정',
            'popup' => '팝업',
            'theme' => '테마 내장',
        ],

        'text_alignment' => [
            'start' => '시작',
            'center' => '중앙',
        ],
    ],

    'validation' => [
        'font_size' => '글꼴 크기는 유효한 CSS 글꼴 크기 값이어야 합니다.',
        'text_color' => '텍스트 색상은 유효한 16진수 색상 값이어야 합니다.',
    ],

    'create' => '새 공지사항 만들기',
    'add_new' => '새로 추가',
    'settings' => [
        'name' => '공지사항',
        'description' => '공지사항 설정 관리',
    ],

    'background_color' => '배경색',
    'font_size' => '글꼴 크기',
    'font_size_help' => '기본값을 사용하려면 비워 두세요. 예: 1rem, 1em, 12px, ...',
    'text_color' => '텍스트 색상',
    'start_date' => '시작일',
    'end_date' => '종료일',
    'has_action' => '액션 있음',
    'action_label' => '액션 레이블',
    'action_url' => '액션 URL',
    'action_open_new_tab' => '새 탭에서 열기',
    'dismissible_label' => '사용자가 공지사항을 닫을 수 있도록 허용',
    'placement' => '배치',
    'text_alignment' => '텍스트 정렬',
    'is_active' => '활성',
    'max_width' => '최대 너비',
    'max_width_help' => '기본값을 사용하려면 비워 두세요. 예: 100%, 500px, ...',
    'max_width_unit' => '최대 너비 단위',
    'font_size_unit' => '글꼴 크기 단위',
    'autoplay_label' => '자동 재생',
    'autoplay_delay_label' => '자동 재생 지연',
    'autoplay_delay_help' => '각 공지사항 사이의 지연(밀리초). 기본값을 사용하려면 비워 두세요(5000).',
    'lazy_loading' => '지연 로딩',
    'lazy_loading_description' => '페이지 로딩 속도를 향상시키려면 이 옵션을 활성화하세요',
    'hide_on_mobile' => '모바일에서 숨기기',
    'dismiss' => '닫기',

    // Placeholders and help texts
    'name_placeholder' => '공지사항 이름 입력',
    'name_help' => '내부 참조용 이름만 표시되며 사용자에게는 표시되지 않습니다',
    'content_placeholder' => '여기에 공지사항 메시지를 입력하세요...',
    'content_help' => '사용자에게 표시될 메시지입니다. HTML 형식을 지원합니다.',
    'start_date_placeholder' => '시작 날짜 및 시간 선택',
    'start_date_help' => '이 날짜부터 공지사항이 표시됩니다. 즉시 시작하려면 비워 두세요.',
    'end_date_placeholder' => '종료 날짜 및 시간 선택',
    'end_date_help' => '이 날짜 이후에 공지사항이 숨겨집니다. 만료 없이 사용하려면 비워 두세요.',
    'has_action_help' => '공지사항에 행동 유도 버튼 추가',
    'action_label_placeholder' => '예: 자세히 알아보기, 지금 쇼핑하기',
    'action_label_help' => '액션 버튼에 표시되는 텍스트',
    'action_url_placeholder' => 'https://example.com/page',
    'action_url_help' => '액션 버튼을 클릭할 때 사용자가 리디렉션될 URL',
    'action_open_new_tab_help' => '액션 링크를 새 브라우저 탭에서 열기',
    'is_active_help' => '이 공지사항을 삭제하지 않고 활성화 또는 비활성화',
];
