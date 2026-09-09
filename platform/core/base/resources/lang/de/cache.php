<?php

return [
    'cache_management' => 'Cache-Verwaltung',
    'cache_management_description' => 'Leeren Sie den Cache, damit Ihre Website stets aktuell dargestellt wird.',
    'cache_commands' => 'Cache-Bereinigungsbefehle',
    'commands' =>
        [
            'clear_cms_cache' =>
                [
                    'title' => 'Gesamten CMS-Cache leeren',
                    'description' => 'Löscht CMS-Caches wie Datenbank-Cache und statische Blöcke. Führen Sie den Befehl aus, wenn Änderungen nach Aktualisierungen nicht sichtbar sind.',
                    'success_msg' => 'Cache geleert',
                ],
            'refresh_compiled_views' =>
                [
                    'title' => 'Kompilierte Ansichten aktualisieren',
                    'description' => 'Entfernt kompilierte Ansichten, damit neue Änderungen sichtbar werden.',
                    'success_msg' => 'Ansichtscache aktualisiert',
                ],
            'clear_config_cache' =>
                [
                    'title' => 'Konfigurations-Cache leeren',
                    'description' => 'Erneuert den Konfigurations-Cache, wenn Sie Einstellungen in der Produktionsumgebung ändern.',
                    'success_msg' => 'Konfigurationscache geleert',
                ],
            'clear_route_cache' =>
                [
                    'title' => 'Routen-Cache leeren',
                    'description' => 'Entfernt zwischengespeicherte Routen.',
                    'success_msg' => 'Routencache geleert',
                ],
            'clear_log' =>
                [
                    'title' => 'Protokolle leeren',
                    'description' => 'Löscht Systemprotokolldateien.',
                    'success_msg' => 'Systemprotokolle gelöscht',
                ],
        ],
    'optimization' =>
        [
            'title' => 'Leistungsoptimierung',
            'optimize' =>
                [
                    'title' => 'Website-Leistung optimieren',
                    'description' => 'Zwischenspeichern von Konfigurationen, Routen und Ansichten für schnellere Ladezeiten.',
                    'button' => 'Optimieren',
                    'success_msg' => 'Optimierung erfolgreich abgeschlossen',
                ],
            'clear' =>
                [
                    'title' => 'Optimierungs-Cache leeren',
                    'description' => 'Entfernt den Optimierungs-Cache, damit Konfigurationsänderungen übernommen werden.',
                    'button' => 'Leeren',
                    'success_msg' => 'Optimierungs-Cache erfolgreich geleert',
                ],
            'messages' =>
                [
                    'config_cached' => 'Konfiguration zwischengespeichert',
                    'routes_cleared' => 'Routen geleert (Befehlszeile für Caching erforderlich)',
                    'views_compiled' => 'Ansichten kompiliert',
                    'framework_cache_cleared' => 'Framework-Cache geleert',
                    'optimization_completed' => 'Optimierung abgeschlossen: :details',
                    'optimization_failed' => 'Optimierung fehlgeschlagen: :error',
                    'clear_failed' => 'Optimierungsbereinigung fehlgeschlagen: :error',
                ],
        ],
    'type' => 'Typ',
    'description' => 'Beschreibung',
    'action' => 'Aktion',
    'current_size' => 'Aktuelle Größe',
    'clear_button' => 'Leeren',
    'refresh_button' => 'Aktualisieren',
    'cache_size_warning' => 'Der CMS-Cache ist größer als 50 MB. Ein Leeren kann die Systemleistung verbessern.',
    'cache_size_warning_dynamic' => 'Der CMS-Cache ist größer als :size MB. Ein Leeren kann die Systemleistung verbessern.',
    'footer_note' => 'Leeren Sie den Cache nach Änderungen an Ihrer Website, damit sie korrekt angezeigt wird.',
];
