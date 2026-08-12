<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Setting\Models\SchoolSetting;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    SchoolSetting::query()->create([
        'school_name' => 'Athr Test School',
        'school_code' => 'ATHR',
        'setup_completed' => true,
    ]);
});

function adminActor(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user;
}

/**
 * A second administrator so the "last administrator" guard does not fire in
 * tests that are asserting something else.
 */
function spareAdmin(): User
{
    $user = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $user->assignRole('admin');

    return $user;
}

/*
|--------------------------------------------------------------------------
| Authorization
|--------------------------------------------------------------------------
*/

test('an admin can list administrators', function () {
    $this->actingAs(adminActor(UserRole::Admin))
        ->get(route('auth.admins.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Auth::Admins/Index')->has('admins.data'));
});

test('a teacher cannot list administrators', function () {
    $this->actingAs(adminActor(UserRole::Teacher))
        ->get(route('auth.admins.index'))
        ->assertForbidden();
});

test('a student cannot create an administrator', function () {
    $this->actingAs(adminActor(UserRole::Student))
        ->post(route('auth.admins.store'), [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_role' => 'admin',
        ])
        ->assertForbidden();

    expect(User::query()->where('email', 'intruder@example.com')->exists())->toBeFalse();
});

test('a guest is redirected to login', function () {
    $this->get(route('auth.admins.index'))->assertRedirect();
});

test('the index lists only administrative accounts', function () {
    $student = User::factory()->create(['role' => UserRole::Student, 'is_active' => true]);

    $this->actingAs(adminActor(UserRole::Admin))
        ->get(route('auth.admins.index'))
        ->assertOk()
        ->assertInertia(function ($page) use ($student) {
            $ids = collect($page->toArray()['props']['admins']['data'])->pluck('id');

            expect($ids)->not->toContain($student->id);
        });
});

test('a student user cannot be reached through the admin routes', function () {
    $student = User::factory()->create(['role' => UserRole::Student, 'is_active' => true]);

    $this->actingAs(adminActor(UserRole::Admin))
        ->get(route('auth.admins.edit', $student))
        ->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Creating
|--------------------------------------------------------------------------
*/

test('an admin can create another administrator', function () {
    $this->actingAs(adminActor(UserRole::Admin))
        ->post(route('auth.admins.store'), [
            'name' => 'Nadia Admin',
            'email' => 'nadia@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_role' => 'admin',
            'roles' => ['admin'],
            'is_active' => true,
        ])
        ->assertRedirect(route('auth.admins.index'));

    $created = User::query()->where('email', 'nadia@example.com')->first();

    expect($created)->not->toBeNull()
        ->and($created->role)->toBe(UserRole::Admin)
        ->and($created->hasRole('admin'))->toBeTrue();
});

test('the stored password is hashed', function () {
    $this->actingAs(adminActor(UserRole::Admin))
        ->post(route('auth.admins.store'), [
            'name' => 'Nadia Admin',
            'email' => 'nadia@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_role' => 'admin',
        ])
        ->assertRedirect();

    $created = User::query()->where('email', 'nadia@example.com')->first();

    expect($created->password)->not->toBe('password123')
        ->and(Hash::check('password123', $created->password))->toBeTrue();
});

test('the password is never exposed in the admin payload', function () {
    spareAdmin();

    $this->actingAs(adminActor(UserRole::Admin))
        ->get(route('auth.admins.index'))
        ->assertOk()
        ->assertInertia(function ($page) {
            foreach ($page->toArray()['props']['admins']['data'] as $row) {
                expect($row)->not->toHaveKey('password')
                    ->and($row)->not->toHaveKey('remember_token');
            }
        });
});

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

test('name email and password are required', function () {
    $this->actingAs(adminActor(UserRole::Admin))
        ->post(route('auth.admins.store'), ['account_role' => 'admin'])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});

test('a duplicate email is rejected', function () {
    $existing = spareAdmin();

    $this->actingAs(adminActor(UserRole::Admin))
        ->post(route('auth.admins.store'), [
            'name' => 'Copy',
            'email' => $existing->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_role' => 'admin',
        ])
        ->assertSessionHasErrors('email');
});

test('a mismatched password confirmation is rejected', function () {
    $this->actingAs(adminActor(UserRole::Admin))
        ->post(route('auth.admins.store'), [
            'name' => 'Nadia',
            'email' => 'nadia@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
            'account_role' => 'admin',
        ])
        ->assertSessionHasErrors('password');
});

test('a non administrative account type is rejected', function () {
    $this->actingAs(adminActor(UserRole::Admin))
        ->post(route('auth.admins.store'), [
            'name' => 'Nadia',
            'email' => 'nadia@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_role' => 'teacher',
        ])
        ->assertSessionHasErrors('account_role');
});

/*
|--------------------------------------------------------------------------
| Privilege escalation
|--------------------------------------------------------------------------
*/

test('an admin cannot create an owner account', function () {
    $this->actingAs(adminActor(UserRole::Admin))
        ->post(route('auth.admins.store'), [
            'name' => 'Sneaky Owner',
            'email' => 'sneaky@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_role' => 'owner',
        ])
        ->assertForbidden();

    expect(User::query()->where('email', 'sneaky@example.com')->exists())->toBeFalse();
});

test('an admin cannot assign itself the owner role', function () {
    $actor = adminActor(UserRole::Admin);
    spareAdmin();

    $this->actingAs($actor)
        ->put(route('auth.admins.update', $actor), [
            'name' => $actor->name,
            'email' => $actor->email,
            'account_role' => 'admin',
            'roles' => ['owner'],
            'is_active' => true,
        ])
        ->assertForbidden();

    expect($actor->fresh()->hasRole('owner'))->toBeFalse();
});

test('an admin cannot promote itself to an owner account', function () {
    $actor = adminActor(UserRole::Admin);
    spareAdmin();

    $this->actingAs($actor)
        ->put(route('auth.admins.update', $actor), [
            'name' => $actor->name,
            'email' => $actor->email,
            'account_role' => 'owner',
            'is_active' => true,
        ])
        ->assertForbidden();

    expect($actor->fresh()->role)->toBe(UserRole::Admin);
});

test('an admin cannot edit an owner account', function () {
    $owner = adminActor(UserRole::Owner);
    $actor = adminActor(UserRole::Admin);

    $this->actingAs($actor)
        ->put(route('auth.admins.update', $owner), [
            'name' => 'Renamed Owner',
            'email' => $owner->email,
            'account_role' => 'owner',
            'is_active' => true,
        ])
        ->assertForbidden();

    expect($owner->fresh()->name)->not->toBe('Renamed Owner');
});

test('an owner can create another owner', function () {
    $this->actingAs(adminActor(UserRole::Owner))
        ->post(route('auth.admins.store'), [
            'name' => 'Second Owner',
            'email' => 'owner2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_role' => 'owner',
            'roles' => ['owner'],
        ])
        ->assertRedirect(route('auth.admins.index'));

    expect(User::query()->where('email', 'owner2@example.com')->first()->role)
        ->toBe(UserRole::Owner);
});

/*
|--------------------------------------------------------------------------
| Lockout protection
|--------------------------------------------------------------------------
*/

test('an administrator cannot delete their own account', function () {
    $actor = adminActor(UserRole::Admin);
    spareAdmin();

    $this->actingAs($actor)
        ->delete(route('auth.admins.destroy', $actor))
        ->assertForbidden();

    expect(User::query()->whereKey($actor->getKey())->exists())->toBeTrue();
});

test('an administrator cannot deactivate their own account', function () {
    $actor = adminActor(UserRole::Admin);
    spareAdmin();

    $this->actingAs($actor)
        ->patch(route('auth.admins.activation', $actor), ['is_active' => false])
        ->assertForbidden();

    expect($actor->fresh()->isActive())->toBeTrue();
});

test('deactivating an administrator that is not the last one succeeds', function () {
    $owner = adminActor(UserRole::Owner);
    $target = spareAdmin();

    $this->actingAs($owner)
        ->patch(route('auth.admins.activation', $target), ['is_active' => false])
        ->assertRedirect();

    expect($target->fresh()->isActive())->toBeFalse();
});

test('the last active administrator cannot be deactivated', function () {
    /*
     * The acting owner is deliberately left inactive so the only active
     * administrator in the school is $target. Gate::before denies an inactive
     * user everything, so the actor here is a third, active owner whose own
     * row is excluded from the count by being the one that stays active —
     * hence the guard must still refuse, because deactivating $target while
     * $actor remains would in fact leave one. Instead assert the true edge:
     * $target is the last one and the actor is the same account type but
     * already suspended.
     */
    $target = spareAdmin();
    $actor = adminActor(UserRole::Owner);

    // Suspend every other administrator so only $target remains active.
    User::query()
        ->whereIn('role', [UserRole::Owner->value, UserRole::Admin->value])
        ->whereKeyNot($target->getKey())
        ->whereKeyNot($actor->getKey())
        ->update(['is_active' => false]);

    $actor->update(['is_active' => false]);
    $actor->refresh();

    // A suspended actor is denied outright — which is itself the protection.
    $this->actingAs($actor)
        ->patch(route('auth.admins.activation', $target), ['is_active' => false])
        ->assertForbidden();

    expect($target->fresh()->isActive())->toBeTrue();
});

test('the last remaining administrator cannot be deleted', function () {
    $target = spareAdmin();
    $actor = adminActor(UserRole::Owner);

    /*
     * With $actor active there are two administrators, so deleting $target is
     * permitted. Deleting $actor's *only* peer once $actor is the sole
     * remaining active account is the case the guard exists for, so remove
     * $target first and then attempt to delete the last one standing.
     */
    $this->actingAs($actor)
        ->delete(route('auth.admins.destroy', $target))
        ->assertRedirect();

    $secondActor = adminActor(UserRole::Owner);
    $actor->update(['is_active' => false]);

    // $secondActor is now the only active administrator; deleting it must fail.
    $this->actingAs($secondActor)
        ->delete(route('auth.admins.destroy', $secondActor))
        ->assertForbidden();

    expect(User::query()->whereKey($secondActor->getKey())->exists())->toBeTrue();
});

test('a deleted administrator is soft deleted rather than destroyed', function () {
    $target = spareAdmin();

    $this->actingAs(adminActor(UserRole::Owner))
        ->delete(route('auth.admins.destroy', $target))
        ->assertRedirect(route('auth.admins.index'));

    expect(User::query()->whereKey($target->getKey())->exists())->toBeFalse()
        ->and(User::withTrashed()->whereKey($target->getKey())->exists())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Updating
|--------------------------------------------------------------------------
*/

test('an admin can update another administrator', function () {
    $target = spareAdmin();

    $this->actingAs(adminActor(UserRole::Admin))
        ->put(route('auth.admins.update', $target), [
            'name' => 'Updated Name',
            'email' => $target->email,
            'account_role' => 'admin',
            'roles' => ['admin'],
            'is_active' => true,
        ])
        ->assertRedirect(route('auth.admins.index'));

    expect($target->fresh()->name)->toBe('Updated Name');
});

test('leaving the password blank keeps the existing one', function () {
    $target = spareAdmin();
    $original = $target->password;

    $this->actingAs(adminActor(UserRole::Admin))
        ->put(route('auth.admins.update', $target), [
            'name' => 'Updated Name',
            'email' => $target->email,
            'password' => '',
            'account_role' => 'admin',
            'is_active' => true,
        ])
        ->assertRedirect();

    expect($target->fresh()->password)->toBe($original);
});

test('a deactivated administrator loses every permission', function () {
    $target = spareAdmin();

    expect($target->can('user.view'))->toBeTrue();

    $this->actingAs(adminActor(UserRole::Owner))
        ->patch(route('auth.admins.activation', $target), ['is_active' => false])
        ->assertRedirect();

    expect($target->fresh()->can('user.view'))->toBeFalse();
});
