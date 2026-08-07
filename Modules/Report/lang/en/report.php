<?php

return [
    'fields' => [
        'name' => 'Name',
        'code' => 'Code',
        'category' => 'Category',
        'query_data' => 'Query',
        'columns' => 'Columns',
        'filters' => 'Filters',
        'format' => 'Format',
        'is_active' => 'Active',
    ],

    'categories' => [
        'academic' => 'Academic',
        'attendance' => 'Attendance',
        'finance' => 'Finance',
        'student' => 'Students',
        'meeting' => 'Meetings',
        'other' => 'Other',
    ],

    'formats' => [
        'json' => 'On screen',
        'pdf' => 'PDF',
        'excel' => 'Excel',
    ],

    'generation_statuses' => [
        'pending' => 'Pending',
        'completed' => 'Completed',
        'failed' => 'Failed',
    ],

    'filter_types' => [
        'text' => 'Text',
        'number' => 'Number',
        'date' => 'Date',
        'select' => 'Select',
        'boolean' => 'Yes / No',
    ],

    'column_types' => [
        'text' => 'Text',
        'number' => 'Number',
        'money' => 'Money',
        'percentage' => 'Percentage',
        'date' => 'Date',
        'datetime' => 'Date & time',
        'badge' => 'Badge',
    ],

    'providers' => [
        'attendance_summary' => 'Attendance Summary',
        'student_roster' => 'Student Roster',
        'finance_outstanding_fees' => 'Outstanding Fees',
        'finance_invoices' => 'Invoices',
        'meeting_sessions' => 'Meeting Sessions',
        'study_material_resources' => 'Study Resources',
    ],

    'filters' => [
        'class_id' => 'Class',
        'grade_level_id' => 'Grade Level',
        'teacher_id' => 'Teacher',
        'student_id' => 'Student',
        'status' => 'Status',
        'type' => 'Type',
        'gender' => 'Gender',
        'is_active' => 'Active',
        'date_from' => 'Date From',
        'date_to' => 'Date To',
    ],

    'columns' => [
        'student_name' => 'Student',
        'admission_number' => 'Admission No.',
        'class_name' => 'Class',
        'grade_level_name' => 'Grade Level',
        'subject_name' => 'Subject',
        'teacher_name' => 'Teacher',
        'title' => 'Title',
        'type' => 'Type',
        'gender' => 'Gender',
        'status' => 'Status',
        'present' => 'Present',
        'absent' => 'Absent',
        'late' => 'Late',
        'excused' => 'Excused',
        'total' => 'Total',
        'percentage' => 'Percentage',
        'invoice_number' => 'Invoice No.',
        'issue_date' => 'Issue Date',
        'due_date' => 'Due Date',
        'total_amount' => 'Total',
        'paid_amount' => 'Paid',
        'balance_due' => 'Balance',
        'admission_date' => 'Admission Date',
        'scheduled_at' => 'Scheduled At',
        'duration_minutes' => 'Duration (min)',
        'downloads_count' => 'Downloads',
        'created_at' => 'Created At',
    ],

    'options' => [
        'male' => 'Male',
        'female' => 'Female',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'scheduled' => 'Scheduled',
        'live' => 'Live',
        'ended' => 'Ended',
        'cancelled' => 'Cancelled',
    ],

    'template_created' => 'Report template created successfully.',
    'template_updated' => 'Report template updated successfully.',
    'template_deleted' => 'Report template deleted successfully.',
    'template_restored' => 'Report template restored successfully.',

    'provider_not_found' => 'This report does not exist.',
    'code_already_exists' => 'A report template with this code already exists.',
    'template_has_generations' => 'This template has generated reports and cannot be deleted.',
    'generation_failed' => 'The report could not be generated.',
];
