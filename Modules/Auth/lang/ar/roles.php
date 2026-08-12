<?php

return [
    /*
     * Top-level role labels are resolved by UserRole::label() as
     * "auth::roles.{value}" — keep them at this level.
     */
    'owner' => 'المالك',
    'admin' => 'مدير النظام',
    'teacher' => 'معلّم',
    'student' => 'طالب',
    'guardian' => 'ولي أمر',
    'staff' => 'موظّف',

    'created' => 'تم إنشاء الدور بنجاح.',
    'updated' => 'تم تحديث الدور بنجاح.',
    'deleted' => 'تم حذف الدور بنجاح.',

    'fields' => [
        'name' => 'الاسم',
        'permissions' => 'الصلاحيات',
        'is_system' => 'دور نظام',
        'users_count' => 'المستخدمون',
        'permissions_count' => 'الصلاحيات',
    ],

    'validation' => [
        'name_format' => 'يجب أن يكون الاسم بأحرف صغيرة وأن يحتوي فقط على حروف وأرقام وشرطات وشرطات سفلية.',
    ],

    'errors' => [
        'name_already_exists' => 'يوجد دور بهذا الاسم بالفعل.',
        'system_cannot_rename' => 'لا يمكن إعادة تسمية أدوار النظام.',
        'system_cannot_delete' => 'لا يمكن حذف أدوار النظام.',
        'owner_managed_by_owner' => 'المالك فقط يمكنه تعديل دور المالك.',
        'cannot_grant_unheld' => 'لا يمكنك منح صلاحيات لا تملكها بنفسك.',
        'still_assigned' => 'هذا الدور ما زال مُسندًا إلى مستخدم أو أكثر.',
        'would_cause_lockout' => 'هذا التغيير سيؤدي إلى فقدانك القدرة على إدارة الأدوار.',
    ],
];
