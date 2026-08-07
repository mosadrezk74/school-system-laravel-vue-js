<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Models\Permission;
use Modules\Auth\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Resources that can be permissioned, grouped by the module that owns
     * them. Adding a module here creates its four permissions on the next
     * seed — nothing else needs editing.
     *
     * @var array<string, array<int, string>>
     */
    private const RESOURCES = [
        'Auth' => ['user', 'role'],
        'Setting' => ['setting', 'language'],
        'Teacher' => ['department'],
        'Academic' => ['academic-year', 'semester', 'grade-level'],
        'Classroom' => ['classroom'],
        'SchoolClass' => ['school-class'],
        'Subject' => ['subject'],
        'Student' => ['student'],
        'Teacher' => ['teacher'],
        'GuardianProfile' => ['guardian'],
        'Staff' => ['staff', 'leave-request'],
        'Timetable' => ['timetable'],
        'Attendance' => ['attendance'],
        'Examination' => ['exam', 'exam-result'],
        'Assignment' => ['assignment'],
        'StudyMaterial' => ['study-material'],
        'Behavior' => ['behavior-record'],
        'Meeting' => ['meeting'],
        'Finance' => ['fee', 'invoice', 'payment'],
        'Library' => ['library-item', 'library-borrowing'],
        'Inventory' => ['inventory-item', 'purchase'],
        'Transport' => ['transport-route', 'transport-vehicle'],
        'Hostel' => ['hostel'],
        'Communication' => ['message', 'broadcast'],
        'Report' => ['report'],
    ];

    private const ABILITIES = ['view', 'create', 'update', 'delete'];

    /**
     * Which resources each role may touch. Owner is deliberately absent —
     * it is granted everything via a Gate::before rule instead, so a new
     * module never leaves the owner locked out.
     *
     * @var array<string, array<string, array<int, string>>>
     */
    private const ROLE_GRANTS = [
        'admin' => ['*' => ['view', 'create', 'update', 'delete']],

        'teacher' => [
            'student' => ['view'],
            'attendance' => ['view', 'create', 'update'],
            'assignment' => ['view', 'create', 'update', 'delete'],
            'study-material' => ['view', 'create', 'update', 'delete'],
            'exam' => ['view'],
            'exam-result' => ['view', 'create', 'update'],
            'behavior-record' => ['view', 'create'],
            'timetable' => ['view'],
            'school-class' => ['view'],
            'subject' => ['view'],
            'message' => ['view', 'create'],
            'meeting' => ['view', 'create', 'update', 'delete'],
        ],

        'staff' => [
            'student' => ['view'],
            'invoice' => ['view', 'create'],
            'payment' => ['view', 'create'],
            'library-item' => ['view', 'create', 'update'],
            'library-borrowing' => ['view', 'create', 'update'],
            'inventory-item' => ['view', 'create', 'update'],
            'message' => ['view', 'create'],
        ],

        'student' => [
            'assignment' => ['view'],
            'study-material' => ['view'],
            'exam-result' => ['view'],
            'timetable' => ['view'],
            'attendance' => ['view'],
            'message' => ['view', 'create'],
            'meeting' => ['view'],
        ],

        'guardian' => [
            'student' => ['view'],
            'attendance' => ['view'],
            'exam-result' => ['view'],
            'invoice' => ['view'],
            'payment' => ['view'],
            'timetable' => ['view'],
            'message' => ['view', 'create'],
        ],
    ];

    public function run(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        DB::transaction(function () use ($guard): void {
            $this->seedPermissions($guard);
            $this->seedRoles($guard);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function seedPermissions(string $guard): void
    {
        foreach (self::RESOURCES as $module => $resources) {
            foreach ($resources as $resource) {
                foreach (self::ABILITIES as $ability) {
                    Permission::query()->updateOrCreate(
                        ['name' => "{$resource}.{$ability}", 'guard_name' => $guard],
                        ['module' => $module],
                    );
                }
            }
        }
    }

    private function seedRoles(string $guard): void
    {
        foreach (UserRole::cases() as $case) {
            $role = Role::query()->updateOrCreate(
                ['name' => $case->value, 'guard_name' => $guard],
                ['is_system' => true],
            );

            // Owner bypasses permission checks entirely (see AuthServiceProvider),
            // so attaching rows for it would be misleading.
            if ($case === UserRole::Owner) {
                continue;
            }

            $role->syncPermissions($this->permissionsFor($case->value, $guard));
        }
    }

    /**
     * @return array<int, string>
     */
    private function permissionsFor(string $role, string $guard): array
    {
        $grants = self::ROLE_GRANTS[$role] ?? [];

        if (isset($grants['*'])) {
            return Permission::query()->where('guard_name', $guard)->pluck('name')->all();
        }

        $names = [];
        foreach ($grants as $resource => $abilities) {
            foreach ($abilities as $ability) {
                $names[] = "{$resource}.{$ability}";
            }
        }

        return $names;
    }
}
