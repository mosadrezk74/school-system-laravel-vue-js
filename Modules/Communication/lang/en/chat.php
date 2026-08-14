<?php

return [
    'started' => 'Conversation started.',
    'unknown_participant' => 'Unknown participant',

    'types' => [
        'direct' => 'Direct',
        'group' => 'Group',
    ],

    'messageTypes' => [
        'text' => 'Text',
        'file' => 'File',
        'system' => 'System',
    ],

    'fields' => [
        'type' => 'Conversation type',
        'name' => 'Group name',
        'participants' => 'Participants',
        'body' => 'Message',
        'attachments' => 'Attachments',
    ],

    'notification' => [
        'title' => 'New message',
        'body' => 'You have a new message from :sender.',
    ],

    'errors' => [
        'not_a_participant' => 'You are not a participant in this conversation.',
        'ineligible_participants' => 'You are not allowed to start a conversation with one or more of the selected people.',
        'empty_message' => 'A message must contain text or at least one attachment.',
        'direct_requires_one_other' => 'A direct conversation must have exactly one other participant.',
        'group_requires_name' => 'A group conversation needs a name.',
        'attachment_not_found' => 'Attachment not found.',
        'attachment_missing' => 'This attachment is no longer available.',
    ],

    'validation' => [
        'attachment_invalid' => 'The attachment is not a valid file.',
        'attachment_upload_failed' => 'The file could not be uploaded. Please try again.',
        'attachment_extension' => 'Files of type “:extension” are not allowed.',
        'attachment_mime' => 'This file type is not allowed.',
        'attachment_size' => 'Each file must not be larger than :max KB.',
    ],
];
