<?php

return [
    'created' => 'تم إنشاء الاجتماع بنجاح.',
    'updated' => 'تم تحديث الاجتماع بنجاح.',
    'deleted' => 'تم حذف الاجتماع بنجاح.',
    'started' => 'تم بدء الاجتماع بنجاح.',
    'finished' => 'تم إنهاء الاجتماع بنجاح.',
    'cancelled' => 'تم إلغاء الاجتماع بنجاح.',

    'meeting_not_found' => 'الاجتماع غير موجود.',
    'invalid_status_transition' => 'لا يمكن تنفيذ هذا الإجراء على الاجتماع في حالته الحالية.',
    'room_name_already_exists' => 'يوجد اجتماع بهذا الاسم بالفعل.',

    'fields' => [
        'title' => 'العنوان',
        'description' => 'الوصف',
        'scheduled_at' => 'موعد الاجتماع',
        'duration_minutes' => 'المدة (بالدقائق)',
        'teacher_id' => 'المعلم',
        'academic_year_id' => 'العام الدراسي',
        'semester_id' => 'الفصل الدراسي',
    ],
    'statuses' => [
        'scheduled' => 'مجدول',
        'live' => 'جارٍ الآن',
        'finished' => 'منتهٍ',
        'cancelled' => 'ملغى',
    ],
];
