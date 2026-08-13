<?php

return [
    'created' => 'Assignment created successfully.',
    'updated' => 'Assignment updated successfully.',
    'archived' => 'Assignment archived successfully.',
    'restored' => 'Assignment restored successfully.',
    'submission_created' => 'Submission recorded successfully.',
    'submission_graded' => 'Submission graded successfully.',

    'assignment_not_found' => 'Assignment not found.',
    'submission_already_exists' => 'This student has already submitted this assignment.',
    'submission_not_found' => 'Submission not found.',

    'fields' => [
        'class_id' => 'Class',
        'subject_id' => 'Subject',
        'teacher_id' => 'Teacher',
        'academic_year_id' => 'Academic Year',
        'semester_id' => 'Semester',
        'title' => 'Title',
        'max_score' => 'Max Score',
        'deadline' => 'Deadline',
    ],

    'validation' => [
        'subject_not_taught_by_class' => 'The selected subject is not linked to this class.',
        'teacher_not_assigned_to_class_subject' => 'The selected teacher is not assigned to teach this subject in this class.',
        'score_above_max' => 'The score cannot be higher than the assignment maximum of :max.',
    ],

    /** Labels for Modules\Assignment\Enums\AssignmentType. */
    'types' => [
        'homework' => 'Homework',
        'project' => 'Project',
        'essay' => 'Essay',
        'presentation' => 'Presentation',
        'practical' => 'Practical',
        'reading' => 'Reading',
    ],
];
