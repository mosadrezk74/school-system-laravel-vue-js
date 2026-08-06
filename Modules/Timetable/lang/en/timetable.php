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
