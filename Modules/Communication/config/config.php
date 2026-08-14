<?php

return [
    'name' => 'Communication',

    /*
    |--------------------------------------------------------------------------
    | Chat attachments
    |--------------------------------------------------------------------------
    |
    | Message attachments are stored through Spatie Media Library on a private
    | disk and are only ever reachable through MessageAttachmentController,
    | which checks conversation membership first. Nothing here may point at a
    | publicly-served disk without also removing that guarantee.
    |
    */
    'attachments' => [

        /** Media Library collection every message attachment is filed under. */
        'collection' => 'message_attachments',

        /*
         * Must stay a private disk. `local` resolves to storage/app/private,
         * which has no public URL, so an attachment cannot be reached by
         * guessing a path the way a `public` disk would allow.
         *
         * Named rather than read from env() so that `config:cache` cannot
         * change what this resolves to — a disk that silently became `public`
         * in production would publish every attachment. A deployment that
         * needs S3 publishes this file and edits it deliberately.
         */
        'disk' => 'local',

        /** Per-file ceiling in kilobytes, matching Laravel's `max:` rule. */
        'max_size_kb' => 10240,

        /** Attachments a single message may carry. */
        'max_per_message' => 10,

        /*
         * Extension and mime are both checked, and both lists are closed.
         * A browser-supplied mime type is trivially forged, so the extension
         * is validated independently and the two must agree — see
         * MessageAttachmentRule.
         *
         * Executables, scripts and archives that can carry them are absent by
         * design. ZIP is included because coursework is routinely handed in
         * that way, but note that its contents are not inspected.
         */
        'extensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'webp',
            'pdf',
            'doc', 'docx',
            'xls', 'xlsx',
            'ppt', 'pptx',
            'txt', 'csv',
            'zip',
        ],

        'mimes' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
            'application/zip',
            'application/x-zip-compressed',
        ],

        /*
         * Mime types the chat renders inline. Everything else is download-only:
         * handing an arbitrary file to the browser to render is how stored XSS
         * gets served from a trusted origin, so HTML and SVG are absent here
         * on purpose.
         */
        'previewable' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ],
    ],
];
