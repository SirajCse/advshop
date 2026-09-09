<?php

return [
    'name' => 'ঘোষণা',

    'enums' => [
        'announce_placement' => [
            'top' => 'শীর্ষে',
            'bottom' => 'নিচে স্থির',
            'popup' => 'পপআপ',
            'theme' => 'থিমে অন্তর্নির্মিত',
        ],

        'text_alignment' => [
            'start' => 'শুরু',
            'center' => 'কেন্দ্র',
        ],
    ],

    'validation' => [
        'font_size' => 'ফন্ট সাইজ একটি বৈধ CSS ফন্ট সাইজ মান হতে হবে।',
        'text_color' => 'টেক্সট রঙ একটি বৈধ হেক্স রঙ মান হতে হবে।',
    ],

    'create' => 'নতুন ঘোষণা তৈরি করুন',
    'add_new' => 'নতুন যোগ করুন',
    'settings' => [
        'name' => 'ঘোষণা',
        'description' => 'ঘোষণা সেটিংস পরিচালনা করুন',
    ],

    'background_color' => 'পটভূমির রঙ',
    'font_size' => 'ফন্ট সাইজ',
    'font_size_help' => 'ডিফল্ট ব্যবহার করতে খালি রাখুন। উদাহরণ: 1rem, 1em, 12px, ...',
    'text_color' => 'টেক্সট রঙ',
    'start_date' => 'শুরুর তারিখ',
    'end_date' => 'শেষ তারিখ',
    'has_action' => 'কর্ম আছে',
    'action_label' => 'কর্ম লেবেল',
    'action_url' => 'কর্ম URL',
    'action_open_new_tab' => 'নতুন ট্যাবে খুলুন',
    'dismissible_label' => 'ব্যবহারকারীকে ঘোষণা বাতিল করার অনুমতি দিন',
    'placement' => 'অবস্থান',
    'text_alignment' => 'টেক্সট সারিবদ্ধতা',
    'is_active' => 'সক্রিয়',
    'max_width' => 'সর্বোচ্চ প্রস্থ',
    'max_width_help' => 'ডিফল্ট মান ব্যবহার করতে খালি রাখুন। উদাহরণ: 100%, 500px, ...',
    'max_width_unit' => 'সর্বোচ্চ প্রস্থ একক',
    'font_size_unit' => 'ফন্ট সাইজ একক',
    'autoplay_label' => 'স্বয়ংক্রিয় প্লে',
    'autoplay_delay_label' => 'স্বয়ংক্রিয় প্লে বিলম্ব',
    'autoplay_delay_help' => 'প্রতিটি ঘোষণার মধ্যে মিলিসেকেন্ডে বিলম্ব। ডিফল্ট মান ব্যবহার করতে খালি রাখুন (5000)।',
    'lazy_loading' => 'লেজি লোডিং',
    'lazy_loading_description' => 'পৃষ্ঠা লোডিং গতি উন্নত করতে এই বিকল্পটি সক্রিয় করুন',
    'hide_on_mobile' => 'মোবাইলে লুকান',
    'dismiss' => 'বাতিল করুন',

    // Placeholders and help texts
    'name_placeholder' => 'ঘোষণার নাম লিখুন',
    'name_help' => 'শুধুমাত্র অভ্যন্তরীণ রেফারেন্সের জন্য নাম, ব্যবহারকারীদের কাছে দৃশ্যমান নয়',
    'content_placeholder' => 'এখানে আপনার ঘোষণা বার্তা লিখুন...',
    'content_help' => 'বার্তা যা ব্যবহারকারীদের কাছে প্রদর্শিত হবে। HTML ফরম্যাটিং সমর্থন করে।',
    'start_date_placeholder' => 'শুরুর তারিখ এবং সময় নির্বাচন করুন',
    'start_date_help' => 'এই তারিখ থেকে ঘোষণা দৃশ্যমান হবে। অবিলম্বে শুরু করতে খালি রাখুন।',
    'end_date_placeholder' => 'শেষ তারিখ এবং সময় নির্বাচন করুন',
    'end_date_help' => 'এই তারিখের পরে ঘোষণা লুকানো হবে। কোন মেয়াদ শেষ না হওয়ার জন্য খালি রাখুন।',
    'has_action_help' => 'আপনার ঘোষণায় একটি কল-টু-অ্যাকশন বোতাম যোগ করুন',
    'action_label_placeholder' => 'যেমন, আরও জানুন, এখনই কিনুন',
    'action_label_help' => 'অ্যাকশন বোতামে প্রদর্শিত টেক্সট',
    'action_url_placeholder' => 'https://example.com/page',
    'action_url_help' => 'অ্যাকশন বোতামে ক্লিক করার সময় ব্যবহারকারীদের যেখানে পুনঃনির্দেশিত করা হবে সেই URL',
    'action_open_new_tab_help' => 'একটি নতুন ব্রাউজার ট্যাবে অ্যাকশন লিঙ্ক খুলুন',
    'is_active_help' => 'এই ঘোষণা মুছে না ফেলে সক্রিয় বা নিষ্ক্রিয় করুন',
];
