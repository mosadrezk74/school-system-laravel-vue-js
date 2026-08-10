<?php

return [
    'name' => 'Meeting',

    /*
     * Self-hosted Jitsi deployment. The domain is the single source of truth
     * for both the stored meeting_url and the External API script the SPA
     * loads, so moving to another instance is one env change.
     */
    'jitsi' => [
        'domain' => env('JITSI_DOMAIN', 'meet.etqan.cloud'),
    ],
];
