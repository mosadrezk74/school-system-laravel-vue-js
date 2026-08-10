<?php

use App\Models\User;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create([
        'role' => UserRole::Admin,
        'is_active' => true,
        'theme' => 'light',
    ]);
    $this->user->assignRole('admin');
});

test('the chosen theme is stored on the user row', function () {
    $this->actingAs($this->user)
        ->patch(route('settings.theme.update'), ['theme' => 'dark'])
        ->assertRedirect();

    expect($this->user->fresh()->theme)->toBe('dark');
});

test('the stored theme is shared with the frontend so a reload restores it', function () {
    $this->user->update(['theme' => 'dark']);

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('auth.user.theme', 'dark'));
});

test('an unknown theme is rejected', function () {
    $this->actingAs($this->user)
        ->patch(route('settings.theme.update'), ['theme' => 'neon'])
        ->assertSessionHasErrors('theme');

    expect($this->user->fresh()->theme)->toBe('light');
});

test('guests cannot change a theme', function () {
    $this->patch(route('settings.theme.update'), ['theme' => 'dark'])
        ->assertRedirect(route('login'));
});
