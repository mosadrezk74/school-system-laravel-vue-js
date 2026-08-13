<?php

return [

    'statuses' => [
        'new' => 'Nouveau',
        'reviewed' => 'Examiné',
        'resolved' => 'Résolu',
    ],

    'status' => [
        '400' => [
            'title' => 'Action impossible',
            'message' => 'Cette action n\'a pas pu être effectuée. Veuillez vérifier les informations et réessayer.',
        ],
        '422' => [
            'title' => 'Action impossible',
            'message' => 'Cette action n\'a pas pu être effectuée. Veuillez vérifier les informations et réessayer.',
        ],
        '401' => [
            'title' => 'Connexion requise',
            'message' => 'Vous devez vous connecter pour consulter cette page.',
        ],
        '403' => [
            'title' => 'Accès refusé',
            'message' => 'Vous n\'avez pas l\'autorisation de consulter cette page.',
        ],
        '404' => [
            'title' => 'Page introuvable',
            'message' => 'La page que vous recherchez n\'existe pas ou a été déplacée.',
        ],
        '419' => [
            'title' => 'Session expirée',
            'message' => 'Votre session a expiré pour des raisons de sécurité. Veuillez actualiser et réessayer.',
        ],
        '429' => [
            'title' => 'Trop de requêtes',
            'message' => 'Vous avez envoyé trop de requêtes. Veuillez patienter un instant et réessayer.',
        ],
        '500' => [
            'title' => 'Une erreur s\'est produite',
            'message' => 'Un problème est survenu lors de l\'opération. Réessayez et contactez l\'administration si cela persiste.',
        ],
        '503' => [
            'title' => 'Service indisponible',
            'message' => 'Le système est temporairement indisponible pour maintenance. Veuillez réessayer sous peu.',
        ],
    ],

    'actions' => [
        'home' => 'Retour à l\'accueil',
        'back' => 'Retour',
    ],

    'admin' => [
        'title' => 'Erreurs système',
        'subtitle' => 'Erreurs de l\'application enregistrées pour examen.',
        'updated' => 'Statut de l\'erreur mis à jour.',
        'empty' => 'Aucune erreur enregistrée. Tout fonctionne correctement.',
        'search' => 'Rechercher des erreurs',
        'details' => 'Détails de l\'erreur',
        'request' => 'Informations sur la requête',
        'user' => 'Informations sur l\'utilisateur',
        'exception' => 'Exception',
        'trace' => 'Trace d\'appels',
        'traceHidden' => 'Vous n\'avez pas l\'autorisation de voir les traces d\'appels.',
        'occurrences' => 'Occurrences',
        'firstSeen' => 'Première apparition',
        'lastSeen' => 'Dernière apparition',
        'markResolved' => 'Marquer comme résolu',
        'markReviewed' => 'Marquer comme examiné',
        'markNew' => 'Rouvrir',
        'resolvedBy' => 'Résolu par',
        'guest' => 'Invité',
        'fields' => [
            'status' => 'Statut',
            'level' => 'Niveau',
            'error' => 'Erreur',
            'route' => 'Route',
            'method' => 'Méthode',
            'user' => 'Utilisateur',
            'occurredAt' => 'Survenue le',
            'requestId' => 'ID de requête',
            'url' => 'URL',
            'ip' => 'Adresse IP',
            'userAgent' => 'Agent utilisateur',
            'file' => 'Fichier',
            'line' => 'Ligne',
            'errorCode' => 'Code d\'erreur',
        ],
    ],

];
