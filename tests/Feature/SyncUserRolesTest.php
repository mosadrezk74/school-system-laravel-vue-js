<?php

use App\Models\User;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('attaches the missing role to an imported account', function (): void {
    $teacher = User::factory()->create(['role' => UserRole::Teacher, 'is_active' => true]);

    expect($teacher->getRoleNames())->toBeEmpty();

    $this->artisan('auth:sync-user-roles')->assertSuccessful();

    expect($teacher->fresh()->getRoleNames())->toContain('teacher')
        ->and($teacher->fresh()->can('meeting.create'))->toBeTrue();
});

it('writes nothing on a dry run', function (): void {
    $teacher = User::factory()->create(['role' => UserRole::Teacher, 'is_active' => true]);

    $this->artisan('auth:sync-user-roles', ['--dry-run' => true])->assertSuccessful();

    expect($teacher->fresh()->getRoleNames())->toBeEmpty();
});

it('leaves a correctly configured account untouched', function (): void {
    $teacher = User::factory()->create(['role' => UserRole::Teacher, 'is_active' => true]);
    $teacher->assignRole('teacher');

    $this->artisan('auth:sync-user-roles')->expectsOutput('0 account(s) updated.');

    expect($teacher->fresh()->getRoleNames())->toContain('teacher');
});
