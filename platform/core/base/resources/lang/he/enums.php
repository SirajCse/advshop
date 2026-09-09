<?php

return [
    'statuses' => [
        'draft' => 'טיוטה',
        'pending' => 'ממתין',
        'published' => 'פורסם',
    ],
    'system_updater_steps' => [
        'download' => 'הורדת קבצי עדכון',
        'update_files' => 'עדכון קבצי מערכת',
        'update_database' => 'עדכון מסדי נתונים',
        'publish_core_assets' => 'פרסום נכסי ליבה',
        'publish_packages_assets' => 'פרסום נכסי חבילות',
        'clean_up' => 'ניקוי קבצי עדכון מערכת',
        'done' => 'המערכת עודכנה בהצלחה',
        'unknown' => 'שלב לא ידוע',
        'messages' => [
            'download' => 'מוריד קבצי עדכון...',
            'update_files' => 'מעדכן קבצי מערכת...',
            'update_database' => 'מעדכן מסדי נתונים...',
            'publish_core_assets' => 'מפרסם נכסי ליבה...',
            'publish_packages_assets' => 'מפרסם נכסי חבילות...',
            'clean_up' => 'מנקה קבצי עדכון מערכת...',
            'done' => 'בוצע! הדפדפן שלך יתרענן בעוד 30 שניות.',
        ],
        'failed_messages' => [
            'download' => 'לא ניתן להוריד קבצי עדכון',
            'update_files' => 'לא ניתן לעדכן קבצי מערכת',
            'update_database' => 'לא ניתן לעדכן מסדי נתונים',
            'publish_core_assets' => 'לא ניתן לפרסם נכסי ליבה',
            'publish_packages_assets' => 'לא ניתן לפרסם נכסי חבילות',
            'clean_up' => 'לא ניתן לנקות קבצי עדכון מערכת',
        ],
        'success_messages' => [
            'download' => 'קבצי עדכון הורדו בהצלחה.',
            'update_files' => 'קבצי מערכת עודכנו בהצלחה.',
            'update_database' => 'מסדי נתונים עודכנו בהצלחה.',
            'publish_core_assets' => 'נכסי ליבה פורסמו בהצלחה.',
            'publish_packages_assets' => 'נכסי חבילות פורסמו בהצלחה.',
            'clean_up' => 'קבצי עדכון מערכת נוקו בהצלחה.',
        ],
    ],
];
