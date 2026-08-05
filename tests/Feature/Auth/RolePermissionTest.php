<?php

use App\Models\User;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Models\Role;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('seeds a system role for every account type', function (): void {
    expect(Role::count())->toBe(count(UserRole::cases()));

    foreach (UserRole::cases() as $case) {
        expect(Role::where('name', $case->value)->value('is_system'))->toBeTrue();
    }
});

it('grants a teacher classroom duties but not finance', function (): void {
    $teacher = User::factory()->create(['role' => UserRole::Teacher, 'is_active' => true]);
    $teacher->assignRole('teacher');

    expect($teacher->can('assignment.create'))->toBeTrue()
        ->and($teacher->can('attendance.update'))->toBeTrue()
        ->and($teacher->can('invoice.delete'))->toBeFalse()
        ->and($teacher->can('user.create'))->toBeFalse();
});

it('lets a guardian read but never write school records', function (): void {
    $guardian = User::factory()->create(['role' => UserRole::Guardian, 'is_active' => true]);
    $guardian->assignRole('guardian');

    expect($guardian->can('invoice.view'))->toBeTrue()
        ->and($guardian->can('exam-result.view'))->toBeTrue()
        ->and($guardian->can('exam-result.update'))->toBeFalse()
        ->and($guardian->can('student.delete'))->toBeFalse();
});

it('passes the owner through every check, including unseeded permissions', function (): void {
    $owner = User::factory()->create(['role' => UserRole::Owner, 'is_active' => true]);

    // No permissions are attached to the owner role on purpose: a Gate::before
    // rule covers it, so a module added after the last seed still works.
    expect($owner->can('invoice.delete'))->toBeTrue()
        ->and($owner->can('a.permission.that.does.not.exist'))->toBeTrue();
});

it('denies a deactivated account even when its role allows the action', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => false]);
    $admin->assignRole('admin');

    // Spatie resolves role permissions without consulting the Gate, so this
    // guards the User::hasPermissionTo override rather than Gate::before.
    expect($admin->can('invoice.delete'))->toBeFalse()
        ->and($admin->can('user.view'))->toBeFalse();
});

it('denies a deactivated owner despite the bypass', function (): void {
    $owner = User::factory()->create(['role' => UserRole::Owner, 'is_active' => false]);

    expect($owner->can('invoice.delete'))->toBeFalse();
});

it('assigns a ulid and username on creation', function (): void {
    $user = User::factory()->create(['email' => 'nadia.hassan@athr-eg.com']);

    expect($user->ulid)->toHaveLength(26)
        ->and($user->username)->toBe('nadia.hassan');
});

it('keeps derived usernames unique', function (): void {
    $first = User::factory()->create(['email' => 'sara@athr-eg.com']);
    $second = User::factory()->create(['email' => 'sara@example.org']);

    expect($second->username)->not->toBe($first->username);
});
