<?php

return [

    'statuses' => [
        'new' => 'Neu',
        'reviewed' => 'Geprüft',
        'resolved' => 'Gelöst',
    ],

    'status' => [
        '401' => [
            'title' => 'Anmeldung erforderlich',
            'message' => 'Sie müssen sich anmelden, um diese Seite zu sehen.',
        ],
        '403' => [
            'title' => 'Zugriff verweigert',
            'message' => 'Sie haben keine Berechtigung, diese Seite anzuzeigen.',
        ],
        '404' => [
            'title' => 'Seite nicht gefunden',
            'message' => 'Die gesuchte Seite existiert nicht oder wurde verschoben.',
        ],
        '419' => [
            'title' => 'Sitzung abgelaufen',
            'message' => 'Ihre Sitzung ist aus Sicherheitsgründen abgelaufen. Bitte aktualisieren Sie die Seite.',
        ],
        '429' => [
            'title' => 'Zu viele Anfragen',
            'message' => 'Sie haben zu viele Anfragen gesendet. Bitte warten Sie einen Moment.',
        ],
        '500' => [
            'title' => 'Ein Fehler ist aufgetreten',
            'message' => 'Bei der Ausführung ist ein Problem aufgetreten. Versuchen Sie es erneut und wenden Sie sich bei anhaltenden Problemen an die Verwaltung.',
        ],
        '503' => [
            'title' => 'Dienst nicht verfügbar',
            'message' => 'Das System ist wegen Wartungsarbeiten vorübergehend nicht verfügbar. Bitte versuchen Sie es in Kürze erneut.',
        ],
    ],

    'actions' => [
        'home' => 'Zurück zur Startseite',
        'back' => 'Zurück',
    ],

    'admin' => [
        'title' => 'Systemfehler',
        'subtitle' => 'Zur Überprüfung erfasste Anwendungsfehler.',
        'updated' => 'Fehlerstatus aktualisiert.',
        'empty' => 'Keine Fehler erfasst. Alles sieht gut aus.',
        'search' => 'Fehler suchen',
        'details' => 'Fehlerdetails',
        'request' => 'Anfrageinformationen',
        'user' => 'Benutzerinformationen',
        'exception' => 'Ausnahme',
        'trace' => 'Stacktrace',
        'traceHidden' => 'Sie haben keine Berechtigung, Stacktraces anzuzeigen.',
        'occurrences' => 'Vorkommen',
        'firstSeen' => 'Zuerst gesehen',
        'lastSeen' => 'Zuletzt gesehen',
        'markResolved' => 'Als gelöst markieren',
        'markReviewed' => 'Als geprüft markieren',
        'markNew' => 'Wieder öffnen',
        'resolvedBy' => 'Gelöst von',
        'guest' => 'Gast',
        'fields' => [
            'status' => 'Status',
            'level' => 'Stufe',
            'error' => 'Fehler',
            'route' => 'Route',
            'method' => 'Methode',
            'user' => 'Benutzer',
            'occurredAt' => 'Aufgetreten am',
            'requestId' => 'Anfrage-ID',
            'url' => 'URL',
            'ip' => 'IP-Adresse',
            'userAgent' => 'User-Agent',
            'file' => 'Datei',
            'line' => 'Zeile',
            'errorCode' => 'Fehlercode',
        ],
    ],

];
