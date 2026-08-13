<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Services\AcademicContextService;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Exceptions\BaseException;
use Modules\Student\Models\StudentProfile;

/**
 * A refused operation should say why it was refused.
 *
 * The handler used to replace every message with the generic copy for the
 * status code, so "this academic year is not active" reached the user as "an
 * unexpected error occurred". Messages the application raises on purpose are
 * now shown; anything else still falls back, so internals never leak.
 */
beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    AcademicContextService::flush();
});

function errorMessageActor(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    return $user;
}

/*
|--------------------------------------------------------------------------
| Deliberate messages reach the user
|--------------------------------------------------------------------------
*/

test('activating an inactive academic year shows the real reason', function () {
    $year = AcademicYear::factory()->create(['is_active' => false, 'is_current' => false]);

    $this->actingAs(errorMessageActor())
        ->patch(route('academic.academic-years.set-current', $year))
        ->assertStatus(400)
        ->assertInertia(fn ($page) => $page
            ->component('Errors/ErrorPage')
            ->where('status', 400)
            ->where('detail', __('academic::academic_years.cannot_activate_inactive'))
        );
});

test('the 400 page is no longer titled as an unexpected server error', function () {
    $year = AcademicYear::factory()->create(['is_active' => false, 'is_current' => false]);

    $this->actingAs(errorMessageActor())
        ->patch(route('academic.academic-years.set-current', $year))
        ->assertStatus(400)
        ->assertInertia(fn ($page) => $page
            // Was '500', which is what made the page read as a crash.
            ->where('statusKey', '400')
            ->where('title', __('core::errors.status.400.title'))
        );
});

test('a deliberate message is returned to API clients too', function () {
    Route::middleware('web')->get('/__test/intentional', function (): void {
        throw BaseException::badRequest('This class is already full.');
    });

    $this->actingAs(errorMessageActor())
        ->getJson('/__test/intentional')
        ->assertStatus(400)
        ->assertJson([
            'status' => 'error',
            'message' => 'This class is already full.',
            'error_code' => 'BAD_REQUEST',
        ]);
});

/*
|--------------------------------------------------------------------------
| Everything else keeps the generic copy
|--------------------------------------------------------------------------
*/

test('an internal exception message is never shown to the user', function () {
    Route::middleware('web')->get('/__test/internal', function (): void {
        throw new RuntimeException('SQLSTATE[42S02]: Base table students_secret not found');
    });

    $this->actingAs(errorMessageActor())
        ->get('/__test/internal')
        ->assertStatus(500)
        ->assertInertia(fn ($page) => $page
            ->where('detail', null)
            ->where('description', __('core::errors.status.500.message'))
        );
})->skip(fn () => config('app.debug'), 'The full Laravel error screen is kept when debug is on.');

test('an internal exception message is withheld from API clients', function () {
    Route::middleware('web')->get('/__test/internal-json', function (): void {
        throw new RuntimeException('SQLSTATE[42S02]: Base table students_secret not found');
    });

    $response = $this->actingAs(errorMessageActor())
        ->getJson('/__test/internal-json')
        ->assertStatus(500);

    expect($response->json('message'))->not->toContain('students_secret')
        ->and($response->json('message'))->toBe(__('core::errors.status.500.message'));
});

test('a missing model does not leak its class name through the 404 page', function () {
    /*
     * Route-model binding raises a NotFoundHttpException whose message reads
     * "No query results for model [Modules\Student\Models\StudentProfile] 999".
     * It is an HttpException, so a looser rule than BaseException would have
     * published the model's namespace on the error page.
     */
    $this->actingAs(errorMessageActor())
        ->get(route('students.show', 999))
        ->assertStatus(404)
        ->assertInertia(fn ($page) => $page
            ->where('detail', null)
            ->where('statusKey', '404')
        );
});

test('a forbidden page keeps its generic copy', function () {
    $student = StudentProfile::factory()->create();

    $this->actingAs(errorMessageActor(UserRole::Student))
        ->get(route('students.show', $student))
        ->assertStatus(403)
        ->assertInertia(fn ($page) => $page
            ->where('detail', null)
            ->where('statusKey', '403')
        );
});
