<?php

return [
    'created' => 'تم إنشاء مسودة الشروط بنجاح.',
    'updated' => 'تم تحديث مسودة الشروط بنجاح.',
    'published' => 'تم نشر الشروط بنجاح.',
    'deleted' => 'تم حذف مسودة الشروط بنجاح.',
    'accepted' => 'شكرًا لك — تم تسجيل موافقتك.',

    'fields' => [
        'title' => 'العنوان',
        'content' => 'المحتوى',
        'version' => 'الإصدار',
        'status' => 'الحالة',
        'published_at' => 'تاريخ النشر',
        'created_by' => 'أنشأه',
        'acceptances' => 'الموافقات',
    ],

    'statuses' => [
        'draft' => 'مسودة',
        'published' => 'منشور',
        'archived' => 'مؤرشف',
    ],

    'errors' => [
        'published_cannot_be_edited' => 'لا يمكن تعديل إصدار منشور. أنشئ إصدارًا جديدًا بدلًا من ذلك.',
        'only_draft_can_be_published' => 'يمكن نشر المسودات فقط.',
        'only_draft_can_be_deleted' => 'يمكن حذف المسودات فقط.',
        'draft_already_exists' => 'توجد مسودة بالفعل. انشرها أو احذفها قبل إنشاء أخرى.',
        'not_acceptable' => 'يمكن الموافقة على الإصدار المنشور فقط من الشروط.',
        'already_accepted' => 'لقد وافقت بالفعل على هذا الإصدار من الشروط.',
        'no_published_version' => 'لم تنشر المدرسة أي شروط بعد.',
    ],
];
