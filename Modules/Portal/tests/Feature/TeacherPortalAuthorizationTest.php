<?php

use App\Models\User;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Models\Role;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function portalUser(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    return $user;
}

/**
 * The seeder must create a role row for every UserRole case. A missing row
 * makes assignRole() a no-op-with-exception at account creation, which is how
 * every teacher ended up with zero permissions and a 403 on their own portal.
 */
it('seeds a role row for every user role case', function (): void {
    foreach (UserRole::cases() as $case) {
        expect(Role::query()->where('name', $case->value)->exists())
            ->toBeTrue("missing role row for [{$case->value}]");
    }
});

/**
 * Reseeding over a legacy differently-cased role row must leave the canonical
 * lowercase name in place, otherwise assignRole() silently resolves nothing.
 */
it('normalises a legacy differently-cased role row', function (): void {
    Role::query()->where('name', 'teacher')->update(['name' => 'Teacher']);

    $this->seed(RolePermissionSeeder::class);

    $teacher = portalUser(UserRole::Teacher);

    expect($teacher->getRoleNames())->toContain('teacher')
        ->and($teacher->can('meeting.create'))->toBeTrue();
});

it('grants a seeded teacher the permissions their portal needs', function (): void {
    $teacher = portalUser(UserRole::Teacher);

    expect($teacher->can('meeting.create'))->toBeTrue()
        ->and($teacher->can('attendance.create'))->toBeTrue()
        ->and($teacher->can('assignment.create'))->toBeTrue()
        ->and($teacher->can('exam-result.update'))->toBeTrue()
        ->and($teacher->can('timetable.view'))->toBeTrue();
});

it('does not grant a teacher administrative permissions', function (): void {
    $teacher = portalUser(UserRole::Teacher);

    expect($teacher->can('user.create'))->toBeFalse()
        ->and($teacher->can('role.update'))->toBeFalse()
        ->and($teacher->can('setting.update'))->toBeFalse()
        ->and($teacher->can('academic-year.create'))->toBeFalse()
        ->and($teacher->can('school-class.delete'))->toBeFalse();
});

it('lets a teacher open the meeting create page', function (): void {
    $this->actingAs(portalUser(UserRole::Teacher))
        ->get(route('teacher.meetings.create'))
        ->assertOk();
});

it('keeps non-teacher portal users out of the teacher portal', function (UserRole $role): void {
    $this->actingAs(portalUser($role))
        ->get(route('teacher.meetings.create'))
        ->assertForbidden();
})->with([
    'student' => UserRole::Student,
    'guardian' => UserRole::Guardian,
]);
