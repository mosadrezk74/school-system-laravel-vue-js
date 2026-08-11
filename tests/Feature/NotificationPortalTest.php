<?php

use App\Models\User;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

/**
 * The notifications page is shared by every role, so the client picks its
 * shell from `auth.portal`. If that prop stops being shared — or reports the
 * wrong portal — a teacher gets the admin sidebar.
 */
it('shares the teacher portal on the shared notifications page', function (): void {
    $teacher = User::factory()->create(['role' => UserRole::Teacher, 'is_active' => true]);
    $teacher->assignRole('teacher');

    $this->actingAs($teacher)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Notification::Notifications/Index')
            ->where('auth.portal', 'teacher')
        );
});

it('reports no portal for an admin, who keeps the admin shell', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('auth.portal', null));
});
