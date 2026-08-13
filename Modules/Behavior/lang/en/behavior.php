<?php

return [
    'created' => 'Behavior record created successfully.',
    'updated' => 'Behavior record updated successfully.',
    'deleted' => 'Behavior record deleted successfully.',

    'category_created' => 'Behavior category created successfully.',
    'category_updated' => 'Behavior category updated successfully.',
    'category_deleted' => 'Behavior category deleted successfully.',

    'student_not_found' => 'Student not found.',
    'category_not_found' => 'Behavior category not found.',
    'category_inactive' => 'The selected behavior category is not active.',

    'fields' => [
        'name' => 'Name',
        'code' => 'Code',
        'type' => 'Type',
        'points' => 'Points',
        'student' => 'Student',
        'category' => 'Category',
        'date' => 'Date',
        'status' => 'Status',
        'reviewed_by' => 'Reviewed By',
    ],

    /** Labels for Modules\Behavior\Enums\BehaviorType. */
    'types' => [
        'merit' => 'Merit',
        'violation' => 'Violation',
    ],

    /** Labels for Modules\Behavior\Enums\BehaviorRecordStatus. */
    'statuses' => [
        'reported' => 'Reported',
        'reviewed' => 'Reviewed',
    ],
];
