<?php

use App\Models\User;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Models\Role;
use Modules\Setting\Models\SchoolSetting;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    /*
     * Staff are pushed to /onboarding until setup is finished, which would
     * mask every authorisation result asserted below.
     */
    SchoolSetting::query()->create([
        'school_name' => 'Athr Test School',
        'school_code' => 'ATHR',
        'setup_completed' => true,
    ]);
});

function roleActor(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user;
}

function makeCustomRole(string $name, array $permissions = []): Role
{
    $role = Role::query()->create([
        'name' => $name,
        'guard_name' => 'web',
        'is_system' => false,
    ]);

    $role->syncPermissions($permissions);

    return $role->refresh();
}

/*
|--------------------------------------------------------------------------
| Authorization
|--------------------------------------------------------------------------
*/

test('an admin can list roles', function () {
    $this->actingAs(roleActor(UserRole::Admin))
        ->get(route('auth.roles.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Auth::Roles/Index')->has('roles.data'));
});

test('an owner can list roles', function () {
    $this->actingAs(roleActor(UserRole::Owner))
        ->get(route('auth.roles.index'))
        ->assertOk();
});

test('a teacher cannot list roles', function () {
    $this->actingAs(roleActor(UserRole::Teacher))
        ->get(route('auth.roles.index'))
        ->assertForbidden();
});

test('a student cannot list roles', function () {
    $this->actingAs(roleActor(UserRole::Student))
        ->get(route('auth.roles.index'))
        ->assertForbidden();
});

test('a guardian cannot create a role', function () {
    $this->actingAs(roleActor(UserRole::Guardian))
        ->post(route('auth.roles.store'), ['name' => 'sneaky', 'permissions' => []])
        ->assertForbidden();

    expect(Role::query()->where('name', 'sneaky')->exists())->toBeFalse();
});

test('a deactivated admin cannot manage roles', function () {
    $user = roleActor(UserRole::Admin);
    $user->update(['is_active' => false]);

    $this->actingAs($user)
        ->get(route('auth.roles.index'))
        ->assertForbidden();
});

test('a guest is redirected to login', function () {
    $this->get(route('auth.roles.index'))->assertRedirect();
});

/*
|--------------------------------------------------------------------------
| Creating
|--------------------------------------------------------------------------
*/

test('an owner can create a role with permissions', function () {
    $this->actingAs(roleActor(UserRole::Owner))
        ->post(route('auth.roles.store'), [
            'name' => 'librarian',
            'permissions' => ['student.view', 'assignment.view'],
        ])
        ->assertRedirect(route('auth.roles.index'));

    $role = Role::query()->where('name', 'librarian')->first();

    expect($role)->not->toBeNull()
        ->and($role->isSystem())->toBeFalse()
        ->and($role->permissions->pluck('name')->all())
        ->toEqualCanonicalizing(['student.view', 'assignment.view']);
});

test('a new role is never created as a system role', function () {
    $this->actingAs(roleActor(UserRole::Owner))
        ->post(route('auth.roles.store'), [
            'name' => 'coordinator',
            'permissions' => [],
            // Even if the client sends it, is_system is not fillable here.
            'is_system' => true,
        ])
        ->assertRedirect();

    expect(Role::query()->where('name', 'coordinator')->first()->isSystem())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

test('a role name is required', function () {
    $this->actingAs(roleActor(UserRole::Owner))
        ->post(route('auth.roles.store'), ['permissions' => []])
        ->assertSessionHasErrors('name');
});

test('a duplicate role name is rejected', function () {
    $this->actingAs(roleActor(UserRole::Owner))
        ->post(route('auth.roles.store'), ['name' => 'teacher', 'permissions' => []])
        ->assertSessionHasErrors('name');
});

test('an uppercase role name is rejected', function () {
    $this->actingAs(roleActor(UserRole::Owner))
        ->post(route('auth.roles.store'), ['name' => 'Librarian', 'permissions' => []])
        ->assertSessionHasErrors('name');
});

test('an unknown permission name is rejected', function () {
    $this->actingAs(roleActor(UserRole::Owner))
        ->post(route('auth.roles.store'), [
            'name' => 'librarian',
            'permissions' => ['not-a-real.permission'],
        ])
        ->assertSessionHasErrors('permissions.0');
});

/*
|--------------------------------------------------------------------------
| Business rules
|--------------------------------------------------------------------------
*/

test('a system role cannot be renamed', function () {
    $teacher = Role::query()->where('name', 'teacher')->first();

    $this->actingAs(roleActor(UserRole::Owner))
        ->put(route('auth.roles.update', $teacher), [
            'name' => 'instructor',
            'permissions' => ['student.view'],
        ])
        ->assertForbidden();

    expect($teacher->refresh()->name)->toBe('teacher');
});

test('a system role can still have its permissions adjusted', function () {
    $teacher = Role::query()->where('name', 'teacher')->first();

    $this->actingAs(roleActor(UserRole::Owner))
        ->put(route('auth.roles.update', $teacher), [
            'name' => 'teacher',
            'permissions' => ['student.view', 'exam.view'],
        ])
        ->assertRedirect(route('auth.roles.index'));

    expect($teacher->refresh()->permissions->pluck('name')->all())
        ->toEqualCanonicalizing(['student.view', 'exam.view']);
});

test('a system role cannot be deleted', function () {
    $teacher = Role::query()->where('name', 'teacher')->first();

    $this->actingAs(roleActor(UserRole::Owner))
        ->delete(route('auth.roles.destroy', $teacher))
        ->assertForbidden();

    expect(Role::query()->where('name', 'teacher')->exists())->toBeTrue();
});

test('a custom role with no users can be deleted', function () {
    $role = makeCustomRole('librarian', ['student.view']);

    $this->actingAs(roleActor(UserRole::Owner))
        ->delete(route('auth.roles.destroy', $role))
        ->assertRedirect(route('auth.roles.index'));

    expect(Role::query()->where('name', 'librarian')->exists())->toBeFalse();
});

test('a custom role that is still assigned cannot be deleted', function () {
    $role = makeCustomRole('librarian', ['student.view']);

    $holder = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $holder->assignRole('librarian');

    $this->actingAs(roleActor(UserRole::Owner))
        ->delete(route('auth.roles.destroy', $role))
        ->assertStatus(409);

    expect(Role::query()->where('name', 'librarian')->exists())->toBeTrue();
});

test('only the owner may edit the owner role', function () {
    $owner = Role::query()->where('name', 'owner')->first();

    $this->actingAs(roleActor(UserRole::Admin))
        ->put(route('auth.roles.update', $owner), [
            'name' => 'owner',
            'permissions' => ['role.view', 'role.update'],
        ])
        ->assertForbidden();
});

test('an admin cannot grant a permission it does not itself hold', function () {
    /*
     * The admin role is granted everything by the seeder, so the escalation
     * is only observable from a narrower custom role.
     */
    $limited = makeCustomRole('limited-manager', ['role.view', 'role.update', 'role.create']);

    $actor = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $actor->syncRoles(['limited-manager']);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->actingAs($actor)
        ->post(route('auth.roles.store'), [
            'name' => 'escalated',
            'permissions' => ['student.delete'],
        ])
        ->assertForbidden();

    expect(Role::query()->where('name', 'escalated')->exists())->toBeFalse();
});

test('an admin cannot strip its own ability to manage roles', function () {
    $limited = makeCustomRole('limited-manager', ['role.view', 'role.update']);

    $actor = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $actor->syncRoles(['limited-manager']);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->actingAs($actor)
        ->put(route('auth.roles.update', $limited), [
            'name' => 'limited-manager',
            'permissions' => [],
        ])
        ->assertStatus(422);

    expect($limited->refresh()->permissions->pluck('name')->all())
        ->toEqualCanonicalizing(['role.view', 'role.update']);
});

test('permission changes take effect immediately for the affected users', function () {
    $role = makeCustomRole('librarian', []);

    $holder = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $holder->syncRoles(['librarian']);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    expect($holder->fresh()->can('student.view'))->toBeFalse();

    $this->actingAs(roleActor(UserRole::Owner))
        ->put(route('auth.roles.update', $role), [
            'name' => 'librarian',
            'permissions' => ['student.view'],
        ])
        ->assertRedirect();

    // Would still read false if the Action had not flushed Spatie's cache.
    expect($holder->fresh()->can('student.view'))->toBeTrue();
});
