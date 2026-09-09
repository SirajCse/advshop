<?php

return [
    'name' => 'הודעות',

    'enums' => [
        'announce_placement' => [
            'top' => 'למעלה',
            'bottom' => 'קבוע בתחתית',
            'popup' => 'חלון קופץ',
            'theme' => 'מובנה בעיצוב',
        ],

        'text_alignment' => [
            'start' => 'התחלה',
            'center' => 'מרכז',
        ],
    ],

    'validation' => [
        'font_size' => 'גודל הגופן חייב להיות ערך תקין של גודל גופן CSS.',
        'text_color' => 'צבע הטקסט חייב להיות ערך צבע הקסדצימלי תקין.',
    ],

    'create' => 'צור הודעה חדשה',
    'add_new' => 'הוסף חדש',
    'settings' => [
        'name' => 'הודעה',
        'description' => 'נהל הגדרות הודעות',
    ],

    'background_color' => 'צבע רקע',
    'font_size' => 'גודל גופן',
    'font_size_help' => 'השאר ריק לשימוש בברירת מחדל. דוגמה: 1rem, 1em, 12px, ...',
    'text_color' => 'צבע טקסט',
    'start_date' => 'תאריך התחלה',
    'end_date' => 'תאריך סיום',
    'has_action' => 'יש פעולה',
    'action_label' => 'תווית פעולה',
    'action_url' => 'כתובת URL לפעולה',
    'action_open_new_tab' => 'פתח בכרטיסייה חדשה',
    'dismissible_label' => 'אפשר למשתמש לסגור את ההודעה',
    'placement' => 'מיקום',
    'text_alignment' => 'יישור טקסט',
    'is_active' => 'פעיל',
    'max_width' => 'רוחב מקסימלי',
    'max_width_help' => 'השאר ריק לשימוש בערך ברירת מחדל. דוגמה: 100%, 500px, ...',
    'max_width_unit' => 'יחידת רוחב מקסימלי',
    'font_size_unit' => 'יחידת גודל גופן',
    'autoplay_label' => 'הפעלה אוטומטית',
    'autoplay_delay_label' => 'עיכוב הפעלה אוטומטית',
    'autoplay_delay_help' => 'העיכוב בין כל הודעה באלפיות השנייה. השאר ריק לשימוש בערך ברירת מחדל (5000).',
    'lazy_loading' => 'טעינה עצלה',
    'lazy_loading_description' => 'אפשר אפשרות זו לשיפור מהירות טעינת העמוד',
    'hide_on_mobile' => 'הסתר בנייד',
    'dismiss' => 'סגור',

    // Placeholders and help texts
    'name_placeholder' => 'הזן שם הודעה',
    'name_help' => 'שם להתייחסות פנימית בלבד, לא גלוי למשתמשים',
    'content_placeholder' => 'הזן את הודעת ההכרזה שלך כאן...',
    'content_help' => 'ההודעה שתוצג למשתמשים. תומך בעיצוב HTML.',
    'start_date_placeholder' => 'בחר תאריך ושעת התחלה',
    'start_date_help' => 'ההודעה תהיה גלויה מתאריך זה. השאר ריק להתחלה מיידית.',
    'end_date_placeholder' => 'בחר תאריך ושעת סיום',
    'end_date_help' => 'ההודעה תוסתר לאחר תאריך זה. השאר ריק ללא תפוגה.',
    'has_action_help' => 'הוסף כפתור קריאה לפעולה להודעה שלך',
    'action_label_placeholder' => 'לדוגמה, למד עוד, קנה עכשיו',
    'action_label_help' => 'טקסט המוצג על כפתור הפעולה',
    'action_url_placeholder' => 'https://example.com/page',
    'action_url_help' => 'כתובת URL שאליה המשתמשים יופנו בעת לחיצה על כפתור הפעולה',
    'action_open_new_tab_help' => 'פתח את קישור הפעולה בכרטיסיית דפדפן חדשה',
    'is_active_help' => 'אפשר או השבת הודעה זו מבלי למחוק אותה',
];
