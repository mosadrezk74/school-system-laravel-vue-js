<?php

return [
    'created' => 'Draft terms created successfully.',
    'updated' => 'Draft terms updated successfully.',
    'published' => 'Terms published successfully.',
    'deleted' => 'Draft terms deleted successfully.',
    'accepted' => 'Thank you — your agreement has been recorded.',

    'fields' => [
        'title' => 'Title',
        'content' => 'Content',
        'version' => 'Version',
        'status' => 'Status',
        'published_at' => 'Published at',
        'created_by' => 'Created by',
        'acceptances' => 'Acceptances',
    ],

    'statuses' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ],

    'errors' => [
        'published_cannot_be_edited' => 'A published version cannot be edited. Create a new version instead.',
        'only_draft_can_be_published' => 'Only a draft version can be published.',
        'only_draft_can_be_deleted' => 'Only a draft version can be deleted.',
        'draft_already_exists' => 'A draft already exists. Publish or delete it before creating another.',
        'not_acceptable' => 'Only the published version of the terms can be accepted.',
        'already_accepted' => 'You have already accepted this version of the terms.',
        'no_published_version' => 'The school has not published any terms yet.',
    ],
];
