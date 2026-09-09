<?php

return [
    'product_specification' => 'مشخصات محصول',
    'specification_groups' => [
        'title' => 'گروه‌های مشخصات',
        'menu_name' => 'گروه‌ها',

        'create' => [
            'title' => 'ایجاد گروه مشخصات',
        ],

        'edit' => [
            'title' => 'ویرایش گروه مشخصات ":name"',
        ],
    ],

    'specification_attributes' => [
        'title' => 'ویژگی‌های مشخصات',
        'menu_name' => 'ویژگی‌ها',

        'group' => 'گروه مرتبط',
        'group_placeholder' => 'یک گروه انتخاب کنید',
        'name_placeholder' => 'نام ویژگی را وارد کنید',
        'type' => 'نوع فیلد',
        'type_placeholder' => 'نوع فیلد را انتخاب کنید',
        'default_value' => 'مقدار پیش‌فرض',
        'default_value_placeholder' => 'مقدار پیش‌فرض را وارد کنید (اختیاری)',
        'options' => [
            'heading' => 'گزینه‌ها',

            'add' => [
                'label' => 'افزودن گزینه جدید',
            ],
        ],

        'create' => [
            'title' => 'ایجاد ویژگی مشخصات',
        ],

        'edit' => [
            'title' => 'ویرایش ویژگی مشخصات ":name"',
        ],
    ],

    'specification_tables' => [
        'title' => 'جداول مشخصات',
        'menu_name' => 'جداول',

        'create' => [
            'title' => 'ایجاد جدول مشخصات',
        ],

        'edit' => [
            'title' => 'ویرایش جدول مشخصات ":name"',
        ],

        'fields' => [
            'groups' => 'گروه‌هایی را برای نمایش در این جدول انتخاب کنید',
            'name' => 'نام گروه',
            'assigned_groups' => 'گروه‌های تخصیص داده شده',
            'sorting' => 'مرتب‌سازی',
        ],
    ],

    'product' => [
        'specification_table' => [
            'options' => 'گزینه‌ها',
            'title' => 'جدول مشخصات',
            'select_none' => 'هیچ',
            'description' => 'جدول مشخصات را برای نمایش در این محصول انتخاب کنید',
            'group' => 'گروه',
            'attribute' => 'ویژگی',
            'value' => 'مقدار ویژگی',
            'hide' => 'مخفی',
            'sorting' => 'مرتب‌سازی',
            'enter_value' => 'مقدار را وارد کنید',
            'enter_translation' => 'ترجمه را وارد کنید',
            'not_set' => 'تنظیم نشده',
        ],
    ],

    'enums' => [
        'field_types' => [
            'text' => 'متن',
            'textarea' => 'متن بلند',
            'select' => 'انتخاب',
            'checkbox' => 'چک‌باکس',
            'radio' => 'رادیو',
        ],
    ],
];
