<?php

return [
    'title' => 'Branches',
    'addNew' => 'Add New Branch',
    'edit' => 'Edit Branch',
    'subtitle' => 'Manage school branches and locations',

    'created' => 'Branch created successfully.',
    'updated' => 'Branch updated successfully.',
    'archived' => 'Branch archived successfully.',
    'restored' => 'Branch restored successfully.',

    'code_already_exists' => 'A branch with this code already exists.',
    'branch_not_found' => 'Branch not found.',
    'cannot_delete_main_branch' => 'Cannot delete the main branch.',
    'main_branch_already_exists' => 'A main branch already exists.',

    'fields' => [
        'name' => 'Name',
        'code' => 'Code',
        'address' => 'Address',
        'city' => 'City',
        'phone' => 'Phone',
        'manager_id' => 'Manager',
        'is_main' => 'Main Branch',
        'is_active' => 'Active',
    ],

    'hints' => [
        'name' => 'Required. Full name of the branch.',
        'code' => 'Optional. Unique branch code (e.g., BR001).',
        'address' => 'Optional. Physical address of the branch.',
        'city' => 'Optional. City where the branch is located.',
        'phone' => 'Optional. Contact phone number for the branch.',
        'manager_id' => 'Optional. User responsible for managing this branch.',
        'is_main' => 'Designate this as the main branch of the school.',
        'is_active' => 'Branch is operational and available for use.',
    ],

    'sections' => [
        'basic' => 'Basic Information',
        'location' => 'Location',
        'management' => 'Management',
        'departments' => 'Departments',
        'statistics' => 'Statistics',
    ],
];