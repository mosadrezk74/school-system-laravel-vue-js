<?php

return [
    'promoted' => ':count student decision(s) recorded successfully.',
    'reverted' => 'Promotion reverted successfully.',

    'fields' => [
        'student' => 'Student',
        'students' => 'Students',
        'status' => 'Decision',
        'from_academic_year' => 'From year',
        'to_academic_year' => 'To year',
        'to_semester' => 'Target semester',
        'from_class' => 'From class',
        'to_class' => 'To class',
        'promoted_by' => 'Decided by',
        'promoted_at' => 'Decided at',
        'remarks' => 'Remarks',
    ],

    'statuses' => [
        'promoted' => 'Promoted',
        'retained' => 'Retained',
        'graduated' => 'Graduated',
        'transferred' => 'Transferred',
    ],

    'errors' => [
        'student_not_found' => 'One of the selected students could not be found.',
        'student_not_in_source_year' => 'A selected student does not belong to the current academic year.',
        'student_already_decided' => 'A decision has already been recorded for this student in the current year.',
        'student_already_graduated' => 'A graduated student cannot be promoted.',
        'target_year_invalid' => 'The target academic year is invalid or inactive.',
        'target_year_same_as_source' => 'The target academic year must differ from the current year.',
        'target_semester_invalid' => 'The target semester is invalid or inactive.',
        'target_semester_required' => 'A target semester is required to promote a student.',
        'target_class_invalid' => 'The target class is invalid or inactive.',
        'grade_level_regression' => 'A student cannot be moved to a lower grade level.',
        'target_class_full' => 'The target class does not have enough capacity.',
        'target_class_required' => 'A target class is required to promote a student.',
        'promotion_not_found' => 'Promotion record not found.',
        'superseded_by_newer_promotion' => 'This promotion cannot be reverted because a newer decision exists for the student.',
        'no_students_selected' => 'Select at least one student.',
    ],
];
