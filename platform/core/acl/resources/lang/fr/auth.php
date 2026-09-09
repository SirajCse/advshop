<?php

return [
    'login' => [
        'username' => 'Email/Nom d\'utilisateur',
        'email' => 'Email',
        'password' => 'Mot de passe',
        'title' => 'Utilisateur en ligne',
        'remember' => 'Souviens-toi de moi?',
        'login' => 'S\'inscrire',
        'placeholder' => [
            'username' => 's\'il vous plaît entrez votre nom d\'utilisateur',
            'email' => 'Veuillez saisir votre e-mail',
            'password' => 'Enter your password',
        ],
        'success' => 'Connectez-vous avec succès !',
        'fail' => 'Nom d\'utilisateur ou mot de passe incorrect.',
        'not_active' => 'Votre compte n\'a pas encore été activé !',
        'banned' => 'Ce compte est interdit.',
        'logout_success' => 'Déconnexion réussie !',
        'dont_have_account' => 'Vous n\'avez pas de compte sur ce système, veuillez contacter l\'administrateur pour plus d\'informations !',
    ],
    'forgot_password' => [
        'title' => 'Mot de passe oublié',
        'message' => '<p>Avez-vous oublié votre mot de passe?</p><p>Veuillez entrer votre compte e-mail. Le système enverra un e-mail avec un lien actif pour réinitialiser votre mot de passe.</p>',
        'submit' => 'Envoyer',
    ],
    'reset' => [
        'new_password' => 'Nouveau mot de passe',
        'password_confirmation' => 'Confirmer le nouveau mot de passe',
        'email' => 'Email',
        'title' => 'réinitialisez votre mot de passe',
        'update' => 'Mettre à jour',
        'wrong_token' => 'Ce lien est invalide ou expiré. Veuillez réessayer d\'utiliser le formulaire de réinitialisation.',
        'user_not_found' => 'Ce nom d\'utilisateur n\'existe pas.',
        'success' => 'Réinitialisez le mot de passe avec succès !',
        'fail' => 'Le Token n\'est pas valide, le lien de réinitialisation du mot de passe a expiré !',
        'reset' => [
            'title' => 'Mot de passe de réinitialisation de l\'e-mail',
        ],
        'send' => [
            'success' => 'Un e-mail a été envoyé à votre compte de messagerie. Veuillez vérifier et terminer cette action.',
            'fail' => 'Impossible d\'envoyer un e-mail pendant cette période. Veuillez réessayer plus tard.',
        ],
        'new-password' => 'Nouveau mot de passe',
        'placeholder' => [
            'new_password' => 'Enter your new password',
            'new_password_confirmation' => 'Confirm your new password',
        ],
    ],
    'email' => [
        'reminder' => [
            'title' => 'Mot de passe de réinitialisation de l\'e-mail',
        ],
    ],
    'password_confirmation' => 'Confirmer le mot de passe',
    'failed' => 'Échoué',
    'throttle' => 'Accélérateur',
    'not_member' => 'Pas encore membre?',
    'register_now' => 'S\'inscrire maintenant',
    'lost_your_password' => 'Mot de passe perdu?',
    'login_title' => 'Admin',
    'login_via_social' => 'Se connecter avec les réseaux sociaux',
    'back_to_login' => 'Retour à la page de connexion',
    'sign_in_below' => 'S\'inscrire ci-dessous',
    'languages' => 'Languages',
    'reset_password' => 'réinitialiser le mot de passe',
    'deactivated_message' => 'Votre compte a été désactivé. Veuillez contacter l\'administrateur.',
    'password_changed_message' => 'Votre mot de passe a été modifié. Veuillez vous reconnecter avec votre nouveau mot de passe.',
    'settings' => [
        'email' => [
            'title' => 'ACL',
            'description' => 'ACL email configuration',
            'templates' => [
                'password_reminder' => [
                    'title' => 'Reset password',
                    'description' => 'Send email to user when requesting reset password',
                    'subject' => 'Reset Password',
                    'reset_link' => 'Reset password link',
                    'email_title' => 'Reset Password Instruction',
                    'email_message' => 'You are receiving this email because we received a password reset request for your account.',
                    'button_text' => 'Reset password',
                    'trouble_text' => 'If you\'re having trouble clicking the \\\"Reset Password\\\" button, copy and paste the URL below into your web browser: <a href=\\\":reset_link\\\">:reset_link</a> and paste it into your browser. If you didn\'t request a password reset, please ignore this message or contact us if you have any questions.',
                ],
            ],
        ],
    ],
];
