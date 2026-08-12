<?php

return [
    'created' => 'تم إنشاء الخبر بنجاح.',
    'updated' => 'تم تحديث الخبر بنجاح.',
    'deleted' => 'تم حذف الخبر بنجاح.',
    'published' => 'تم نشر الخبر.',
    'unpublished' => 'تمت إعادة الخبر إلى المسودة.',
    'pinned' => 'تم تثبيت الخبر.',
    'unpinned' => 'تم إلغاء تثبيت الخبر.',

    'fields' => [
        'title' => 'العنوان',
        'body' => 'المحتوى',
        'status' => 'الحالة',
        'audience' => 'الجمهور',
        'published_at' => 'تاريخ النشر',
        'is_pinned' => 'مثبّت',
        'author' => 'الكاتب',
    ],

    'statuses' => [
        'draft' => 'مسودة',
        'published' => 'منشور',
    ],

    'audiences' => [
        'all' => 'الجميع',
        'students' => 'الطلاب',
        'guardians' => 'أولياء الأمور',
        'teachers' => 'المعلّمون',
    ],

    'errors' => [
        'not_found' => 'الخبر غير موجود.',
        'already_published' => 'هذا الخبر منشور بالفعل.',
        'not_published' => 'هذا الخبر غير منشور.',
    ],
];
