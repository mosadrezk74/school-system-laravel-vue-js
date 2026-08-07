<?php

return [
    'role_attribute' => 'role',

    'fallback_route' => 'dashboard',

    'roles' => [
        'owner' => ['portal' => null, 'route' => 'dashboard'],
        'admin' => ['portal' => null, 'route' => 'dashboard'],
        'teacher' => ['portal' => 'teacher', 'route' => 'teacher.dashboard'],
        'guardian' => ['portal' => 'parent', 'route' => 'parent.dashboard'],
        'student' => ['portal' => 'student', 'route' => 'student.dashboard'],
    ],
];
