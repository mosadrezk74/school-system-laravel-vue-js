<?php

return [
    'created' => 'تم إنشاء الواجب بنجاح.',
    'updated' => 'تم تحديث الواجب بنجاح.',
    'archived' => 'تم أرشفة الواجب بنجاح.',
    'restored' => 'تم استعادة الواجب بنجاح.',
    'submission_created' => 'تم تسجيل التسليم بنجاح.',
    'submission_graded' => 'تم تقييم التسليم بنجاح.',

    'assignment_not_found' => 'الواجب غير موجود.',
    'submission_already_exists' => 'قام هذا الطالب بتسليم هذا الواجب مسبقًا.',
    'submission_not_found' => 'التسليم غير موجود.',

    'fields' => [
        'class_id' => 'الفصل',
        'subject_id' => 'المادة',
        'teacher_id' => 'المعلم',
        'academic_year_id' => 'العام الدراسي',
        'semester_id' => 'الفصل الدراسي',
        'title' => 'العنوان',
        'max_score' => 'الدرجة الكلية',
        'deadline' => 'الموعد النهائي',
    ],

    'validation' => [
        'subject_not_taught_by_class' => 'المادة المختارة غير مرتبطة بهذا الفصل.',
        'teacher_not_assigned_to_class_subject' => 'المعلم المحدد غير مسند لتدريس هذه المادة في هذا الفصل.',
    ],
];
