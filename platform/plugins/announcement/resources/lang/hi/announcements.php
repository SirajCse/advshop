<?php

return [
    'name' => 'घोषणाएँ',

    'enums' => [
        'announce_placement' => [
            'top' => 'ऊपर',
            'bottom' => 'नीचे स्थिर',
            'popup' => 'पॉपअप',
            'theme' => 'थीम में अंतर्निहित',
        ],

        'text_alignment' => [
            'start' => 'आरंभ',
            'center' => 'केंद्र',
        ],
    ],

    'validation' => [
        'font_size' => 'फ़ॉन्ट आकार एक मान्य CSS फ़ॉन्ट आकार मान होना चाहिए।',
        'text_color' => 'टेक्स्ट रंग एक मान्य हेक्स रंग मान होना चाहिए।',
    ],

    'create' => 'नई घोषणा बनाएँ',
    'add_new' => 'नया जोड़ें',
    'settings' => [
        'name' => 'घोषणा',
        'description' => 'घोषणा सेटिंग्स प्रबंधित करें',
    ],

    'background_color' => 'पृष्ठभूमि रंग',
    'font_size' => 'फ़ॉन्ट आकार',
    'font_size_help' => 'डिफ़ॉल्ट उपयोग करने के लिए खाली छोड़ें। उदाहरण: 1rem, 1em, 12px, ...',
    'text_color' => 'टेक्स्ट रंग',
    'start_date' => 'आरंभ तिथि',
    'end_date' => 'समाप्ति तिथि',
    'has_action' => 'कार्रवाई है',
    'action_label' => 'कार्रवाई लेबल',
    'action_url' => 'कार्रवाई URL',
    'action_open_new_tab' => 'नए टैब में खोलें',
    'dismissible_label' => 'उपयोगकर्ता को घोषणा बंद करने की अनुमति दें',
    'placement' => 'स्थान',
    'text_alignment' => 'टेक्स्ट संरेखण',
    'is_active' => 'सक्रिय है',
    'max_width' => 'अधिकतम चौड़ाई',
    'max_width_help' => 'डिफ़ॉल्ट मान उपयोग करने के लिए खाली छोड़ें। उदाहरण: 100%, 500px, ...',
    'max_width_unit' => 'अधिकतम चौड़ाई इकाई',
    'font_size_unit' => 'फ़ॉन्ट आकार इकाई',
    'autoplay_label' => 'ऑटोप्ले',
    'autoplay_delay_label' => 'ऑटोप्ले विलंब',
    'autoplay_delay_help' => 'प्रत्येक घोषणा के बीच मिलीसेकंड में विलंब। डिफ़ॉल्ट मान उपयोग करने के लिए खाली छोड़ें (5000)।',
    'lazy_loading' => 'आलसी लोडिंग',
    'lazy_loading_description' => 'पेज लोडिंग गति में सुधार के लिए इस विकल्प को सक्षम करें',
    'hide_on_mobile' => 'मोबाइल पर छिपाएँ',
    'dismiss' => 'बंद करें',

    // Placeholders and help texts
    'name_placeholder' => 'घोषणा का नाम दर्ज करें',
    'name_help' => 'केवल आंतरिक संदर्भ के लिए नाम, उपयोगकर्ताओं को दिखाई नहीं देता',
    'content_placeholder' => 'यहाँ अपना घोषणा संदेश दर्ज करें...',
    'content_help' => 'वह संदेश जो उपयोगकर्ताओं को प्रदर्शित किया जाएगा। HTML स्वरूपण का समर्थन करता है।',
    'start_date_placeholder' => 'आरंभ तिथि और समय चुनें',
    'start_date_help' => 'घोषणा इस तिथि से दिखाई देगी। तुरंत शुरू करने के लिए खाली छोड़ें।',
    'end_date_placeholder' => 'समाप्ति तिथि और समय चुनें',
    'end_date_help' => 'इस तिथि के बाद घोषणा छिपा दी जाएगी। कोई समाप्ति न होने के लिए खाली छोड़ें।',
    'has_action_help' => 'अपनी घोषणा में कॉल-टू-एक्शन बटन जोड़ें',
    'action_label_placeholder' => 'उदा., और जानें, अभी खरीदें',
    'action_label_help' => 'कार्रवाई बटन पर प्रदर्शित टेक्स्ट',
    'action_url_placeholder' => 'https://example.com/page',
    'action_url_help' => 'URL जहां कार्रवाई बटन पर क्लिक करने पर उपयोगकर्ता पुनर्निर्देशित किए जाएंगे',
    'action_open_new_tab_help' => 'कार्रवाई लिंक को नए ब्राउज़र टैब में खोलें',
    'is_active_help' => 'इस घोषणा को हटाए बिना सक्षम या अक्षम करें',
];
