<?php

return [
    'created' => 'Administrator created successfully.',
    'updated' => 'Administrator updated successfully.',
    'deleted' => 'Administrator deleted successfully.',
    'activated' => 'Administrator activated.',
    'deactivated' => 'Administrator deactivated.',

    'fields' => [
        'name' => 'Full name',
        'email' => 'Email',
        'username' => 'Username',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
        'phone' => 'Phone',
        'gender' => 'Gender',
        'birth_date' => 'Date of birth',
        'national_id' => 'National ID',
        'is_active' => 'Active',
        'account_role' => 'Account type',
        'roles' => 'Roles',
    ],

    'errors' => [
        'email_already_exists' => 'An account with this email already exists.',
        'owner_assignable_by_owner' => 'Only the owner can grant owner access.',
        'cannot_modify_owner' => 'Only the owner can modify an owner account.',
        'cannot_delete_self' => 'You cannot delete your own account.',
        'cannot_deactivate_self' => 'You cannot deactivate your own account.',
        'would_leave_no_administrator' => 'This would leave the school without an active administrator.',
    ],
];
