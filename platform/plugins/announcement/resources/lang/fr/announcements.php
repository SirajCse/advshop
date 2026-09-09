<?php

return [
    'name' => 'Annonces',

    'enums' => [
        'announce_placement' => [
            'top' => 'Haut',
            'bottom' => 'Fixé en bas',
            'popup' => 'Fenêtre contextuelle',
            'theme' => 'Intégré au thème',
        ],

        'text_alignment' => [
            'start' => 'Début',
            'center' => 'Centre',
        ],
    ],

    'validation' => [
        'font_size' => 'La taille de police doit être une valeur de taille de police CSS valide.',
        'text_color' => 'La couleur du texte doit être une valeur de couleur hexadécimale valide.',
    ],

    'create' => 'Créer une nouvelle annonce',
    'add_new' => 'Ajouter nouveau',
    'settings' => [
        'name' => 'Annonce',
        'description' => 'Gérer les paramètres des annonces',
    ],

    'background_color' => 'Couleur d\'arrière-plan',
    'font_size' => 'Taille de police',
    'font_size_help' => 'Laisser vide pour utiliser la valeur par défaut. Exemple : 1rem, 1em, 12px, ...',
    'text_color' => 'Couleur du texte',
    'start_date' => 'Date de début',
    'end_date' => 'Date de fin',
    'has_action' => 'A une action',
    'action_label' => 'Libellé de l\'action',
    'action_url' => 'URL de l\'action',
    'action_open_new_tab' => 'Ouvrir dans un nouvel onglet',
    'dismissible_label' => 'Autoriser l\'utilisateur à fermer l\'annonce',
    'placement' => 'Emplacement',
    'text_alignment' => 'Alignement du texte',
    'is_active' => 'Est actif',
    'max_width' => 'Largeur maximale',
    'max_width_help' => 'Laisser vide pour utiliser la valeur par défaut. Exemple : 100%, 500px, ...',
    'max_width_unit' => 'Unité de largeur maximale',
    'font_size_unit' => 'Unité de taille de police',
    'autoplay_label' => 'Lecture automatique',
    'autoplay_delay_label' => 'Délai de lecture automatique',
    'autoplay_delay_help' => 'Le délai entre chaque annonce en millisecondes. Laisser vide pour utiliser la valeur par défaut (5000).',
    'lazy_loading' => 'Chargement différé',
    'lazy_loading_description' => 'Activer cette option pour améliorer la vitesse de chargement de la page',
    'hide_on_mobile' => 'Masquer sur mobile',
    'dismiss' => 'Fermer',

    // Placeholders and help texts
    'name_placeholder' => 'Entrez le nom de l\'annonce',
    'name_help' => 'Nom pour référence interne uniquement, non visible par les utilisateurs',
    'content_placeholder' => 'Entrez votre message d\'annonce ici...',
    'content_help' => 'Le message qui sera affiché aux utilisateurs. Prend en charge le formatage HTML.',
    'start_date_placeholder' => 'Sélectionnez la date et l\'heure de début',
    'start_date_help' => 'L\'annonce sera visible à partir de cette date. Laissez vide pour commencer immédiatement.',
    'end_date_placeholder' => 'Sélectionnez la date et l\'heure de fin',
    'end_date_help' => 'L\'annonce sera masquée après cette date. Laissez vide pour aucune expiration.',
    'has_action_help' => 'Ajouter un bouton d\'appel à l\'action à votre annonce',
    'action_label_placeholder' => 'ex. : En savoir plus, Acheter maintenant',
    'action_label_help' => 'Texte affiché sur le bouton d\'action',
    'action_url_placeholder' => 'https://exemple.com/page',
    'action_url_help' => 'URL vers laquelle les utilisateurs seront redirigés lorsqu\'ils cliquent sur le bouton d\'action',
    'action_open_new_tab_help' => 'Ouvrir le lien d\'action dans un nouvel onglet du navigateur',
    'is_active_help' => 'Activer ou désactiver cette annonce sans la supprimer',
];
