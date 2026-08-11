<?php

return [
    'fields' => [
        'name' => 'الاسم',
        'code' => 'الرمز',
        'category' => 'التصنيف',
        'query_data' => 'الاستعلام',
        'columns' => 'الأعمدة',
        'filters' => 'المرشحات',
        'format' => 'الصيغة',
        'is_active' => 'نشط',
    ],

    'categories' => [
        'academic' => 'أكاديمي',
        'attendance' => 'الحضور',
        'finance' => 'المالية',
        'student' => 'الطلاب',
        'meeting' => 'الاجتماعات',
        'other' => 'أخرى',
    ],

    'formats' => [
        'json' => 'على الشاشة',
        'pdf' => 'PDF',
        'excel' => 'Excel',
    ],

    'generation_statuses' => [
        'pending' => 'قيد الانتظار',
        'completed' => 'مكتمل',
        'failed' => 'فشل',
    ],

    'filter_types' => [
        'text' => 'نص',
        'number' => 'رقم',
        'date' => 'تاريخ',
        'select' => 'اختيار',
        'boolean' => 'نعم / لا',
    ],

    'column_types' => [
        'text' => 'نص',
        'number' => 'رقم',
        'money' => 'مبلغ',
        'percentage' => 'نسبة مئوية',
        'date' => 'تاريخ',
        'datetime' => 'تاريخ ووقت',
        'badge' => 'شارة',
    ],

    'providers' => [
        'attendance_summary' => 'ملخص الحضور',
        'student_roster' => 'كشف الطلاب',
        'finance_outstanding_fees' => 'الرسوم المستحقة',
        'finance_invoices' => 'الفواتير',
        'meeting_sessions' => 'جلسات الاجتماعات',
    ],

    'filters' => [
        'class_id' => 'الفصل',
        'grade_level_id' => 'المرحلة الدراسية',
        'teacher_id' => 'المعلم',
        'student_id' => 'الطالب',
        'status' => 'الحالة',
        'type' => 'النوع',
        'gender' => 'الجنس',
        'is_active' => 'نشط',
        'date_from' => 'من تاريخ',
        'date_to' => 'إلى تاريخ',
    ],

    'columns' => [
        'student_name' => 'الطالب',
        'admission_number' => 'رقم القيد',
        'class_name' => 'الفصل',
        'grade_level_name' => 'المرحلة الدراسية',
        'subject_name' => 'المادة',
        'teacher_name' => 'المعلم',
        'title' => 'العنوان',
        'type' => 'النوع',
        'gender' => 'الجنس',
        'status' => 'الحالة',
        'present' => 'حاضر',
        'absent' => 'غائب',
        'late' => 'متأخر',
        'excused' => 'بعذر',
        'total' => 'الإجمالي',
        'percentage' => 'النسبة',
        'invoice_number' => 'رقم الفاتورة',
        'issue_date' => 'تاريخ الإصدار',
        'due_date' => 'تاريخ الاستحقاق',
        'total_amount' => 'الإجمالي',
        'paid_amount' => 'المدفوع',
        'balance_due' => 'الرصيد',
        'admission_date' => 'تاريخ القيد',
        'scheduled_at' => 'موعد الجلسة',
        'duration_minutes' => 'المدة (دقيقة)',
        'downloads_count' => 'التنزيلات',
        'created_at' => 'تاريخ الإنشاء',
    ],

    'options' => [
        'male' => 'ذكر',
        'female' => 'أنثى',
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'scheduled' => 'مجدولة',
        'live' => 'جارية',
        'ended' => 'منتهية',
        'cancelled' => 'ملغاة',
    ],

    'template_created' => 'تم إنشاء قالب التقرير بنجاح.',
    'template_updated' => 'تم تحديث قالب التقرير بنجاح.',
    'template_deleted' => 'تم حذف قالب التقرير بنجاح.',
    'template_restored' => 'تمت استعادة قالب التقرير بنجاح.',

    'provider_not_found' => 'هذا التقرير غير موجود.',
    'code_already_exists' => 'يوجد قالب تقرير بهذا الرمز بالفعل.',
    'template_has_generations' => 'هذا القالب له تقارير مُولّدة ولا يمكن حذفه.',
    'generation_failed' => 'تعذّر توليد التقرير.',
];
