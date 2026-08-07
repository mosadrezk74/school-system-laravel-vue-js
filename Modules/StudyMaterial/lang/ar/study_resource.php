<?php

return [
    'fields' => [
        'title' => 'العنوان',
        'description' => 'الوصف',
        'subject_id' => 'المادة',
        'grade_level_id' => 'الصف',
        'classroom_id' => 'الفصل',
        'teacher_id' => 'المعلم',
        'type' => 'النوع',
        'external_url' => 'رابط خارجي',
        'file' => 'الملف',
    ],

    'types' => [
        'book' => 'كتاب',
        'worksheet' => 'ورقة عمل',
        'homework' => 'واجب',
        'presentation' => 'عرض تقديمي',
        'video' => 'فيديو',
        'audio' => 'ملف صوتي',
        'other' => 'أخرى',
    ],

    'created' => 'تم إنشاء المصدر التعليمي.',
    'updated' => 'تم تحديث المصدر التعليمي.',
    'archived' => 'تمت أرشفة المصدر التعليمي.',
    'restored' => 'تمت استعادة المصدر التعليمي.',

    'file_or_url_required' => 'يجب إدخال ملف أو رابط خارجي أو كليهما.',
    'no_file_to_download' => 'لا يوجد ملف مرفق بهذا المصدر التعليمي.',
    'teacher_is_not_a_teacher' => 'المستخدم المختار ليس معلماً.',
];
