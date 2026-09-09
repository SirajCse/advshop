<?php

return [
    'name' => 'Cronjob',
    'description' => 'הגדר משימות רקע אוטומטיות כדי לשמור על האתר שלך פועל בצורה חלקה.',
    'is_not_ready' => 'Cronjob עדיין לא מוגדר',
    'is_not_ready_description' => 'אנא עקוב אחר ההוראות למטה כדי להגדיר את ה-cronjob. זה נדרש עבור תכונות כמו תזכורות עגלת קניות נטושה, תזמון אימייל ומשימות אוטומטיות אחרות.',
    'is_working' => 'Cronjob פועל כראוי!',
    'is_not_working' => 'Cronjob הפסיק לעבוד',
    'is_not_working_description' => 'ה-cronjob לא רץ ב-10 הדקות האחרונות. אנא בדוק את תצורת השרת שלך או צור קשר עם ספק האחסון שלך.',
    'last_checked' => 'פעילות אחרונה: :time',
    'copy_button' => 'העתק פקודה',
    'what_is' => [
        'title' => 'מה זה Cronjob?',
        'description' => 'Cronjob היא משימה אוטומטית שרצה ברקע בשרת שלך. היא מאפשרת לאתר שלך לבצע משימות חשובות באופן אוטומטי מבלי שתצטרך לעשות משהו באופן ידני.',
        'examples' => 'דוגמאות',
        'features' => 'שליחת תזכורות עגלת קניות נטושה, עיבוד אימיילים מתוזמנים, ניקוי נתונים ישנים, יצירת דוחות ועוד.',
    ],
    'command' => [
        'title' => 'פקודת ה-Cronjob שלך',
        'description' => 'העתק פקודה זו והוסף אותה ללוח הבקרה של האחסון שלך. פקודה זו צריכה לרוץ כל דקה כדי שהמשימות האוטומטיות שלך יפעלו.',
    ],
    'setup' => [
        'name' => 'כיצד להגדיר',
        'copied' => 'הועתק ללוח!',
        'choose_hosting' => 'בחר את לוח הבקרה של האחסון שלך למטה ועקוב אחר ההוראות צעד אחר צעד:',
    ],
    'cpanel' => [
        'step1' => 'היכנס לחשבון <strong>cPanel</strong> שלך',
        'step2' => 'מצא ולחץ על <strong>"Cron Jobs"</strong> בקטע "Advanced"',
        'step3' => 'תחת "Add New Cron Job", בחר <strong>"Once Per Minute (* * * * *)"</strong> מהתפריט הנפתח',
        'step4' => '<strong>הדבק את הפקודה</strong> שהעתקת למעלה בשדה "Command"',
        'step5' => 'לחץ על <strong>"Add New Cron Job"</strong> כדי לשמור',
    ],
    'plesk' => [
        'step1' => 'היכנס ללוח הבקרה <strong>Plesk</strong> שלך',
        'step2' => 'עבור אל <strong>"Scheduled Tasks"</strong> (או "Cron Jobs")',
        'step3' => 'לחץ על <strong>"Add Task"</strong> או <strong>"Schedule a Task"</strong>',
        'step4' => 'הגדר את לוח הזמנים לרוץ <strong>כל דקה</strong> והדבק את הפקודה',
        'step5' => 'לחץ על <strong>"OK"</strong> או <strong>"Apply"</strong> כדי לשמור',
    ],
    'directadmin' => [
        'step1' => 'היכנס ללוח <strong>DirectAdmin</strong> שלך',
        'step2' => 'נווט אל <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'לחץ על <strong>"Add Cron Job"</strong>',
        'step4' => 'הגדר את כל שדות הזמן ל-<strong>*</strong> (כל דקה) והדבק את הפקודה',
        'step5' => 'לחץ על <strong>"Add"</strong> כדי לשמור את ה-cronjob',
    ],
    'ssh' => [
        'step1' => 'התחבר לשרת שלך דרך <strong>SSH</strong> באמצעות Terminal או PuTTY',
        'step2' => 'הקלד <code>crontab -e</code> ולחץ Enter כדי לערוך את קובץ ה-crontab',
        'step3' => 'הוסף שורה חדשה בתחתית ו<strong>הדבק את הפקודה</strong>',
        'step4' => 'לחץ <strong>Ctrl+X</strong>, אז <strong>Y</strong>, אז <strong>Enter</strong> כדי לשמור (לעורך nano)',
        'step5' => 'ה-cronjob פעיל עכשיו וירוץ כל דקה',
    ],
    'need_help' => 'צריך עזרה? צור קשר עם ספק האחסון שלך ובקש מהם להגדיר cronjob שרץ כל דקה עם הפקודה המוצגת למעלה.',
];
