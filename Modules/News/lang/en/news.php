<?php

return [
    'created' => 'News item created successfully.',
    'updated' => 'News item updated successfully.',
    'deleted' => 'News item deleted successfully.',
    'published' => 'News item published.',
    'unpublished' => 'News item moved back to draft.',
    'pinned' => 'News item pinned.',
    'unpinned' => 'News item unpinned.',

    'fields' => [
        'title' => 'Title',
        'body' => 'Content',
        'status' => 'Status',
        'audience' => 'Audience',
        'published_at' => 'Publish at',
        'is_pinned' => 'Pinned',
        'author' => 'Author',
    ],

    'statuses' => [
        'draft' => 'Draft',
        'published' => 'Published',
    ],

    'audiences' => [
        'all' => 'Everyone',
        'students' => 'Students',
        'guardians' => 'Guardians',
        'teachers' => 'Teachers',
    ],

    'errors' => [
        'not_found' => 'News item not found.',
        'already_published' => 'This news item is already published.',
        'not_published' => 'This news item is not published.',
    ],
];
