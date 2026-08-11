<?php

return [
    'created' => 'Saved successfully.',
    'updated' => 'Updated successfully.',
    'archived' => 'Archived successfully.',
    'restored' => 'Restored successfully.',
    'entries_saved' => 'Timetable saved successfully.',

    'class_already_has_timetable_for_year' => 'This class already has a timetable for the selected academic year.',
    'duplicate_period_number' => 'A period with this number already exists.',
    'overlapping_period_time' => 'This period\'s time overlaps with another period.',
    'entries_conflict' => 'Some entries conflict with an existing schedule.',

    'validation' => [
        'not_a_working_day' => 'The selected day is not one of the school\'s working days.',
        'teacher_does_not_teach_subject' => 'The selected teacher is not assigned to the selected subject.',
        'subject_not_taught_by_class' => 'The selected subject is not linked to this class.',
        'teacher_not_assigned_to_class_subject' => 'The selected teacher is not assigned to teach this subject in this class.',
        'duplicate_slot' => 'More than one lesson was placed in the same day and period.',
        'subject_required' => 'Every lesson must have a subject.',
        'teacher_required' => 'Every lesson must have a teacher.',
    ],

    'conflicts' => [
        'teacher' => 'Teacher :teacher is already scheduled on :day, period :period.',
        'classroom' => 'The classroom is already booked on :day, period :period.',
    ],

    'days' => [
        'Sunday' => 'Sunday',
        'Monday' => 'Monday',
        'Tuesday' => 'Tuesday',
        'Wednesday' => 'Wednesday',
        'Thursday' => 'Thursday',
        'Friday' => 'Friday',
        'Saturday' => 'Saturday',
    ],

    'fields' => [
        'period_number' => 'Period Number',
        'name' => 'Name',
        'start_time' => 'Start Time',
        'end_time' => 'End Time',
        'is_break' => 'Break',
        'is_active' => 'Active',
        'class' => 'Class',
        'academic_year' => 'Academic Year',
        'semester' => 'Semester',
        'subject' => 'Subject',
        'teacher' => 'Teacher',
        'classroom' => 'Classroom',
        'active_weekdays' => 'Active School Days',
    ],
];
