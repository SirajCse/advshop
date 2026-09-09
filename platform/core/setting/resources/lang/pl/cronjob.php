<?php

return [
    'name' => 'Cronjob',
    'description' => 'Skonfiguruj automatyczne zadania w tle, aby Twoja strona działała płynnie.',
    'is_not_ready' => 'Cronjob nie jest jeszcze skonfigurowany',
    'is_not_ready_description' => 'Postępuj zgodnie z poniższymi instrukcjami, aby skonfigurować cronjob. Jest to wymagane dla funkcji takich jak przypomnienia o porzuconym koszyku, planowanie e-maili i inne zautomatyzowane zadania.',
    'is_working' => 'Cronjob działa poprawnie!',
    'is_not_working' => 'Cronjob przestał działać',
    'is_not_working_description' => 'Cronjob nie uruchomił się w ciągu ostatnich 10 minut. Sprawdź konfigurację serwera lub skontaktuj się z dostawcą hostingu.',
    'last_checked' => 'Ostatnia aktywność: :time',
    'copy_button' => 'Kopiuj polecenie',
    'what_is' => [
        'title' => 'Czym jest Cronjob?',
        'description' => 'Cronjob to automatyczne zadanie, które działa w tle na Twoim serwerze. Pozwala Twojej stronie automatycznie wykonywać ważne zadania bez konieczności ręcznego działania.',
        'examples' => 'Przykłady',
        'features' => 'Wysyłanie przypomnień o porzuconym koszyku, przetwarzanie zaplanowanych e-maili, czyszczenie starych danych, generowanie raportów i więcej.',
    ],
    'command' => [
        'title' => 'Twoje polecenie Cronjob',
        'description' => 'Skopiuj to polecenie i dodaj je do panelu sterowania hostingiem. To polecenie musi być uruchamiane co minutę, aby Twoje automatyczne zadania działały.',
    ],
    'setup' => [
        'name' => 'Jak skonfigurować',
        'copied' => 'Skopiowano do schowka!',
        'choose_hosting' => 'Wybierz poniżej swój panel sterowania hostingiem i postępuj zgodnie z instrukcjami krok po kroku:',
    ],
    'cpanel' => [
        'step1' => 'Zaloguj się do swojego konta <strong>cPanel</strong>',
        'step2' => 'Znajdź i kliknij <strong>"Cron Jobs"</strong> w sekcji "Advanced"',
        'step3' => 'W sekcji "Add New Cron Job" wybierz <strong>"Once Per Minute (* * * * *)"</strong> z rozwijanego menu',
        'step4' => '<strong>Wklej polecenie</strong>, które skopiowałeś powyżej, do pola "Command"',
        'step5' => 'Kliknij <strong>"Add New Cron Job"</strong>, aby zapisać',
    ],
    'plesk' => [
        'step1' => 'Zaloguj się do swojego panelu sterowania <strong>Plesk</strong>',
        'step2' => 'Przejdź do <strong>"Scheduled Tasks"</strong> (lub "Cron Jobs")',
        'step3' => 'Kliknij <strong>"Add Task"</strong> lub <strong>"Schedule a Task"</strong>',
        'step4' => 'Ustaw harmonogram na uruchamianie <strong>co minutę</strong> i wklej polecenie',
        'step5' => 'Kliknij <strong>"OK"</strong> lub <strong>"Apply"</strong>, aby zapisać',
    ],
    'directadmin' => [
        'step1' => 'Zaloguj się do swojego panelu <strong>DirectAdmin</strong>',
        'step2' => 'Przejdź do <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Kliknij <strong>"Add Cron Job"</strong>',
        'step4' => 'Ustaw wszystkie pola czasowe na <strong>*</strong> (co minutę) i wklej polecenie',
        'step5' => 'Kliknij <strong>"Add"</strong>, aby zapisać cronjob',
    ],
    'ssh' => [
        'step1' => 'Połącz się z serwerem przez <strong>SSH</strong> używając Terminal lub PuTTY',
        'step2' => 'Wpisz <code>crontab -e</code> i naciśnij Enter, aby edytować plik crontab',
        'step3' => 'Dodaj nową linię na dole i <strong>wklej polecenie</strong>',
        'step4' => 'Naciśnij <strong>Ctrl+X</strong>, następnie <strong>Y</strong>, następnie <strong>Enter</strong>, aby zapisać (dla edytora nano)',
        'step5' => 'Cronjob jest teraz aktywny i będzie uruchamiany co minutę',
    ],
    'need_help' => 'Potrzebujesz pomocy? Skontaktuj się z dostawcą hostingu i poproś o skonfigurowanie cronjob, który uruchamia się co minutę z poleceniem pokazanym powyżej.',
];
