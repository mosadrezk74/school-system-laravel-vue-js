<?php

return [
    'created' => 'تم إنشاء المدير بنجاح.',
    'updated' => 'تم تحديث المدير بنجاح.',
    'deleted' => 'تم حذف المدير بنجاح.',
    'activated' => 'تم تفعيل المدير.',
    'deactivated' => 'تم إيقاف المدير.',

    'fields' => [
        'name' => 'الاسم الكامل',
        'email' => 'البريد الإلكتروني',
        'username' => 'اسم المستخدم',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'phone' => 'رقم الجوال',
        'gender' => 'الجنس',
        'birth_date' => 'تاريخ الميلاد',
        'national_id' => 'رقم الهوية',
        'is_active' => 'نشط',
        'account_role' => 'نوع الحساب',
        'roles' => 'الأدوار',
    ],

    'errors' => [
        'email_already_exists' => 'يوجد حساب بهذا البريد الإلكتروني بالفعل.',
        'owner_assignable_by_owner' => 'المالك فقط يمكنه منح صلاحية المالك.',
        'cannot_modify_owner' => 'المالك فقط يمكنه تعديل حساب المالك.',
        'cannot_delete_self' => 'لا يمكنك حذف حسابك الخاص.',
        'cannot_deactivate_self' => 'لا يمكنك إيقاف حسابك الخاص.',
        'would_leave_no_administrator' => 'سيؤدي هذا إلى ترك المدرسة بدون مدير نشط.',
    ],
];
