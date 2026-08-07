<?php

return [
    'fields' => [
        'title' => 'Title',
        'description' => 'Description',
        'subject_id' => 'Subject',
        'grade_level_id' => 'Grade Level',
        'classroom_id' => 'Classroom',
        'teacher_id' => 'Teacher',
        'type' => 'Type',
        'external_url' => 'External URL',
        'file' => 'File',
    ],

    'types' => [
        'book' => 'Book',
        'worksheet' => 'Worksheet',
        'homework' => 'Homework',
        'presentation' => 'Presentation',
        'video' => 'Video',
        'audio' => 'Audio',
        'other' => 'Other',
    ],

    'created' => 'Study resource created.',
    'updated' => 'Study resource updated.',
    'archived' => 'Study resource archived.',
    'restored' => 'Study resource restored.',

    'file_or_url_required' => 'Provide a file, an external URL, or both.',
    'no_file_to_download' => 'This study resource has no file attached.',
    'teacher_is_not_a_teacher' => 'The selected user is not a teacher.',
];
