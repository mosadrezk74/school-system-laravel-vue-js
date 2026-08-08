<?php

use App\Models\User;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $admin->assignRole('admin');

    $this->actingAs($admin);
});

test('the onboarding wizard no longer ships the modules prop', function () {
    $this->get(route('onboarding.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Setting::Onboarding/Wizard')
            ->has('rows')
            ->missing('modules')
        );
});

test('the organization and module onboarding routes are gone', function () {
    expect(Route::has('onboarding.organization'))->toBeFalse()
        ->and(Route::has('onboarding.modules.toggle'))->toBeFalse();

    $this->post('/onboarding/organization')->assertNotFound();
    $this->patch('/onboarding/modules/Student/toggle')->assertNotFound();
});
