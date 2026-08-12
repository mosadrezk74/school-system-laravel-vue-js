<?php

return [
    'promoted' => 'تم تسجيل قرار :count طالب بنجاح.',
    'reverted' => 'تم التراجع عن الترقية بنجاح.',

    'fields' => [
        'student' => 'الطالب',
        'students' => 'الطلاب',
        'status' => 'القرار',
        'from_academic_year' => 'من العام',
        'to_academic_year' => 'إلى العام',
        'to_semester' => 'الفصل المستهدف',
        'from_class' => 'من الصف',
        'to_class' => 'إلى الصف',
        'promoted_by' => 'صاحب القرار',
        'promoted_at' => 'تاريخ القرار',
        'remarks' => 'ملاحظات',
    ],

    'statuses' => [
        'promoted' => 'مُرقّى',
        'retained' => 'مُعاد',
        'graduated' => 'متخرّج',
        'transferred' => 'مُحوّل',
    ],

    'errors' => [
        'student_not_found' => 'تعذّر العثور على أحد الطلاب المحددين.',
        'student_not_in_source_year' => 'أحد الطلاب المحددين لا ينتمي إلى العام الدراسي الحالي.',
        'student_already_decided' => 'تم تسجيل قرار لهذا الطالب في العام الحالي بالفعل.',
        'student_already_graduated' => 'لا يمكن ترقية طالب متخرّج.',
        'target_year_invalid' => 'العام الدراسي المستهدف غير صالح أو غير مفعّل.',
        'target_year_same_as_source' => 'يجب أن يختلف العام المستهدف عن العام الحالي.',
        'target_semester_invalid' => 'الفصل الدراسي المستهدف غير صالح أو غير مفعّل.',
        'target_semester_required' => 'يجب تحديد فصل دراسي مستهدف لترقية الطالب.',
        'target_class_invalid' => 'الصف المستهدف غير صالح أو غير مفعّل.',
        'grade_level_regression' => 'لا يمكن نقل الطالب إلى مرحلة دراسية أدنى.',
        'target_class_full' => 'لا تتوفر سعة كافية في الصف المستهدف.',
        'target_class_required' => 'يجب تحديد صف مستهدف لترقية الطالب.',
        'promotion_not_found' => 'سجل الترقية غير موجود.',
        'superseded_by_newer_promotion' => 'لا يمكن التراجع عن هذه الترقية لوجود قرار أحدث للطالب.',
        'no_students_selected' => 'اختر طالبًا واحدًا على الأقل.',
    ],
];
