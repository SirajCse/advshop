<?php

return [
    'payment_description' => 'הלקוח יכול לקנות מוצר ולשלם ישירות באמצעות Visa, כרטיס אשראי דרך :name',
    'api_key' => 'מפתח API',
    'api_key_helper' => 'קבל את מפתח ה-API שלך מלוח הבקרה של Mollie',
    'webhook_secret' => 'Webhook Secret (אופציונלי)',
    'webhook_secret_helper' => 'אופציונלי: הוסף webhook secret לאבטחה משופרת. הגדר זאת בלוח הבקרה של Mollie תחת Developers > Webhooks',
    'register_account' => 'רשום חשבון ב-:name',
    'after_registration' => 'לאחר הרשמה ב-:name, יהיה לך מפתח API',
    'enter_api_key' => 'הזן מפתח API בתיבה בצד ימין',
    'webhook_configuration' => 'הגדרת Webhook:',
    'webhook_url_instruction' => 'בלוח הבקרה של Mollie, הגדר את כתובת ה-URL של webhook כ:',
    'webhook_note' => 'הערה: החלף את {token} באסימון התשלום בפועל. ה-webhook יקרא אוטומטית על ידי Mollie לעדכון סטטוס התשלום.',
    'security_optional' => 'אבטחה (אופציונלי):',
    'security_instruction' => 'לאבטחה משופרת, אתה יכול להגדיר webhook secret בלוח הבקרה של Mollie תחת Developers > Webhooks, ואז להזין אותו בשדה Webhook Secret.',
];
