<?php

return [
    'name' => 'Vendeurs non vérifiés',
    'verify' => 'Vérifier le vendeur « :name »',
    'forms' => [
        'email' => 'E-mail',
        'store_name' => 'Nom du magasin',
        'store_phone' => 'Téléphone du magasin',
        'vendor_phone' => 'Téléphone du vendeur',
        'verify_vendor' => 'Vérifier le vendeur',
        'registered_at' => 'Inscrit le',
        'certificate' => 'Certificat',
        'government_id' => 'Pièce d\'identité',
    ],
    'view_certificate' => 'Voir le certificat',
    'view_government_id' => 'Voir la pièce d\'identité',
    'approve' => 'Approuver le vendeur',
    'reject' => 'Rejeter',
    'approve_vendor_confirmation' => 'Confirmation d\'approbation du vendeur',
    'approve_vendor_confirmation_description' => 'Êtes-vous sûr de vouloir vraiment approuver :vendor pour vendre sur ce site ?',
    'reject_vendor_confirmation' => 'Confirmation de rejet du vendeur',
    'reject_vendor_confirmation_description' => 'Êtes-vous sûr de vouloir vraiment rejeter :vendor pour vendre sur ce site ?',
    'new_vendor_notifications' => [
        'new_vendor' => 'Nouveau vendeur',
        'view' => 'Voir',
        'description' => ':customer s\'est inscrit mais n\'a pas été vérifié.',
    ],
];
