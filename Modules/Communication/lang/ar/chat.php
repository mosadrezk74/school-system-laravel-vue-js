<?php

return [
    'started' => 'تم بدء المحادثة.',
    'unknown_participant' => 'مشارك غير معروف',

    'types' => [
        'direct' => 'محادثة مباشرة',
        'group' => 'محادثة جماعية',
    ],

    'messageTypes' => [
        'text' => 'نص',
        'file' => 'ملف',
        'system' => 'رسالة نظام',
    ],

    'fields' => [
        'type' => 'نوع المحادثة',
        'name' => 'اسم المجموعة',
        'participants' => 'المشاركون',
        'body' => 'الرسالة',
        'attachments' => 'المرفقات',
    ],

    'notification' => [
        'title' => 'رسالة جديدة',
        'body' => 'لديك رسالة جديدة من :sender.',
    ],

    'errors' => [
        'not_a_participant' => 'أنت لست مشاركًا في هذه المحادثة.',
        'ineligible_participants' => 'لا يُسمح لك ببدء محادثة مع واحد أو أكثر من الأشخاص المحددين.',
        'empty_message' => 'يجب أن تحتوي الرسالة على نص أو مرفق واحد على الأقل.',
        'direct_requires_one_other' => 'يجب أن تحتوي المحادثة المباشرة على مشارك واحد فقط غيرك.',
        'group_requires_name' => 'يجب إدخال اسم للمحادثة الجماعية.',
        'attachment_not_found' => 'المرفق غير موجود.',
        'attachment_missing' => 'هذا المرفق لم يعد متاحًا.',
    ],

    'validation' => [
        'attachment_invalid' => 'المرفق ليس ملفًا صالحًا.',
        'attachment_upload_failed' => 'تعذّر رفع الملف. يرجى المحاولة مرة أخرى.',
        'attachment_extension' => 'الملفات من نوع «:extension» غير مسموح بها.',
        'attachment_mime' => 'نوع هذا الملف غير مسموح به.',
        'attachment_size' => 'يجب ألا يتجاوز حجم كل ملف :max كيلوبايت.',
    ],
];
