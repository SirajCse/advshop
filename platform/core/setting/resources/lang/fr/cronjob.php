<?php

return [
    'name' => 'Cronjob',
    'description' => 'Configurez des tâches automatisées en arrière-plan pour que votre site web fonctionne sans problème.',
    'is_not_ready' => 'Le cronjob n\'est pas encore configuré',
    'is_not_ready_description' => 'Veuillez suivre les instructions ci-dessous pour configurer le cronjob. Ceci est nécessaire pour des fonctionnalités comme les rappels de panier abandonné, la planification des e-mails et d\'autres tâches automatisées.',
    'is_working' => 'Le cronjob fonctionne correctement !',
    'is_not_working' => 'Le cronjob a cessé de fonctionner',
    'is_not_working_description' => 'Le cronjob n\'a pas fonctionné au cours des 10 dernières minutes. Veuillez vérifier la configuration de votre serveur ou contacter votre hébergeur.',
    'last_checked' => 'Dernière activité : :time',
    'copy_button' => 'Copier la commande',
    'what_is' => [
        'title' => 'Qu\'est-ce qu\'un Cronjob ?',
        'description' => 'Un cronjob est une tâche automatisée qui s\'exécute en arrière-plan sur votre serveur. Il permet à votre site web d\'effectuer automatiquement des tâches importantes sans que vous ayez à faire quoi que ce soit manuellement.',
        'examples' => 'Exemples',
        'features' => 'Envoyer des rappels de panier abandonné, traiter les e-mails planifiés, nettoyer les anciennes données, générer des rapports, et plus encore.',
    ],
    'command' => [
        'title' => 'Votre commande Cronjob',
        'description' => 'Copiez cette commande et ajoutez-la à votre panneau de contrôle d\'hébergement. Cette commande doit s\'exécuter toutes les minutes pour que vos tâches automatisées fonctionnent.',
    ],
    'setup' => [
        'name' => 'Comment configurer',
        'copied' => 'Copié dans le presse-papiers !',
        'choose_hosting' => 'Sélectionnez votre panneau de contrôle d\'hébergement ci-dessous et suivez les instructions étape par étape :',
    ],
    'cpanel' => [
        'step1' => 'Connectez-vous à votre compte <strong>cPanel</strong>',
        'step2' => 'Trouvez et cliquez sur <strong>"Cron Jobs"</strong> dans la section "Advanced"',
        'step3' => 'Sous "Add New Cron Job", sélectionnez <strong>"Once Per Minute (* * * * *)"</strong> dans le menu déroulant',
        'step4' => '<strong>Collez la commande</strong> que vous avez copiée ci-dessus dans le champ "Command"',
        'step5' => 'Cliquez sur <strong>"Add New Cron Job"</strong> pour enregistrer',
    ],
    'plesk' => [
        'step1' => 'Connectez-vous à votre panneau de contrôle <strong>Plesk</strong>',
        'step2' => 'Allez dans <strong>"Scheduled Tasks"</strong> (ou "Cron Jobs")',
        'step3' => 'Cliquez sur <strong>"Add Task"</strong> ou <strong>"Schedule a Task"</strong>',
        'step4' => 'Définissez le planning pour s\'exécuter <strong>toutes les minutes</strong> et collez la commande',
        'step5' => 'Cliquez sur <strong>"OK"</strong> ou <strong>"Apply"</strong> pour enregistrer',
    ],
    'directadmin' => [
        'step1' => 'Connectez-vous à votre panneau <strong>DirectAdmin</strong>',
        'step2' => 'Naviguez vers <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Cliquez sur <strong>"Add Cron Job"</strong>',
        'step4' => 'Définissez tous les champs de temps sur <strong>*</strong> (toutes les minutes) et collez la commande',
        'step5' => 'Cliquez sur <strong>"Add"</strong> pour enregistrer le cronjob',
    ],
    'ssh' => [
        'step1' => 'Connectez-vous à votre serveur via <strong>SSH</strong> en utilisant Terminal ou PuTTY',
        'step2' => 'Tapez <code>crontab -e</code> et appuyez sur Entrée pour modifier le fichier crontab',
        'step3' => 'Ajoutez une nouvelle ligne en bas et <strong>collez la commande</strong>',
        'step4' => 'Appuyez sur <strong>Ctrl+X</strong>, puis <strong>Y</strong>, puis <strong>Entrée</strong> pour enregistrer (pour l\'éditeur nano)',
        'step5' => 'Le cronjob est maintenant actif et s\'exécutera toutes les minutes',
    ],
    'need_help' => 'Besoin d\'aide ? Contactez votre hébergeur et demandez-lui de configurer un cronjob qui s\'exécute toutes les minutes avec la commande indiquée ci-dessus.',
];
