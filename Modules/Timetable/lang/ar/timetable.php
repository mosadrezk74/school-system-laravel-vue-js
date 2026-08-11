<?php

return [
    'created' => 'تم الحفظ بنجاح.',
    'updated' => 'تم التحديث بنجاح.',
    'archived' => 'تم الأرشفة بنجاح.',
    'restored' => 'تم الاسترجاع بنجاح.',
    'entries_saved' => 'تم حفظ الجدول الدراسي بنجاح.',

    'class_already_has_timetable_for_year' => 'يوجد بالفعل جدول دراسي لهذا الفصل في العام الدراسي المحدد.',
    'duplicate_period_number' => 'يوجد بالفعل حصة بنفس هذا الرقم.',
    'overlapping_period_time' => 'توقيت هذه الحصة يتعارض مع حصة أخرى.',
    'entries_conflict' => 'بعض الحصص تتعارض مع جدول موجود بالفعل.',

    'validation' => [
        'not_a_working_day' => 'اليوم المحدد ليس من أيام الدوام المدرسي.',
        'teacher_does_not_teach_subject' => 'المعلم المحدد غير مسند إلى المادة المختارة.',
        'subject_not_taught_by_class' => 'المادة المختارة غير مرتبطة بهذا الفصل.',
        'teacher_not_assigned_to_class_subject' => 'المعلم المحدد غير مسند لتدريس هذه المادة في هذا الفصل.',
        'duplicate_slot' => 'يوجد أكثر من حصة في نفس اليوم والحصة.',
    ],

    'conflicts' => [
        'teacher' => 'المعلم :teacher مجدول بالفعل يوم :day، الحصة :period.',
        'classroom' => 'الفصل الدراسي محجوز بالفعل يوم :day، الحصة :period.',
    ],

    'days' => [
        'Sunday' => 'الأحد',
        'Monday' => 'الإثنين',
        'Tuesday' => 'الثلاثاء',
        'Wednesday' => 'الأربعاء',
        'Thursday' => 'الخميس',
        'Friday' => 'الجمعة',
        'Saturday' => 'السبت',
    ],

    'fields' => [
        'period_number' => 'رقم الحصة',
        'name' => 'الاسم',
        'start_time' => 'وقت البداية',
        'end_time' => 'وقت النهاية',
        'is_break' => 'استراحة',
        'is_active' => 'مفعّل',
        'class' => 'الفصل',
        'academic_year' => 'العام الدراسي',
        'semester' => 'الفصل الدراسي',
        'subject' => 'المادة',
        'teacher' => 'المعلم',
        'classroom' => 'قاعة الدراسة',
        'active_weekdays' => 'أيام الدراسة',
    ],
];
