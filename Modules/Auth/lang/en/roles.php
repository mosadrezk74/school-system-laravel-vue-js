<?php

return [
    /*
     * Top-level role labels are resolved by UserRole::label() as
     * "auth::roles.{value}" — keep them at this level.
     */
    'owner' => 'Owner',
    'admin' => 'Administrator',
    'teacher' => 'Teacher',
    'student' => 'Student',
    'guardian' => 'Guardian',
    'staff' => 'Staff',

    'created' => 'Role created successfully.',
    'updated' => 'Role updated successfully.',
    'deleted' => 'Role deleted successfully.',

    'fields' => [
        'name' => 'Name',
        'permissions' => 'Permissions',
        'is_system' => 'System role',
        'users_count' => 'Users',
        'permissions_count' => 'Permissions',
    ],

    'validation' => [
        'name_format' => 'The name must be lowercase and may contain only letters, numbers, hyphens and underscores.',
    ],

    'errors' => [
        'name_already_exists' => 'A role with this name already exists.',
        'system_cannot_rename' => 'System roles cannot be renamed.',
        'system_cannot_delete' => 'System roles cannot be deleted.',
        'owner_managed_by_owner' => 'Only the owner can modify the owner role.',
        'cannot_grant_unheld' => 'You cannot grant permissions that you do not hold yourself.',
        'still_assigned' => 'This role is still assigned to one or more users.',
        'would_cause_lockout' => 'This change would remove your own ability to manage roles.',
    ],
];
