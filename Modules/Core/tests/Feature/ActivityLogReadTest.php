<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Models\Activity;
use Modules\Setting\Models\SchoolSetting;

/**
 * The activity log is read-only and staff-only, and every read reports the
 * academic context it happened under — resolved from AcademicContextService,
 * never from the query string, because activity_log stores no academic
 * columns of its own.
 */
beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    AcademicContextService::flush();
});

function activityActor(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    return $user;
}

function makeActivity(array $attributes = []): Activity
{
    return Activity::create([
        'log_name' => 'default',
        'description' => 'Student record updated',
        'event' => 'updated',
        'subject_type' => 'Modules\Student\Models\StudentProfile',
        'subject_id' => 1,
        ...$attributes,
    ]);
}

function currentYearWithSemester(): array
{
    $year = AcademicYear::factory()->current()->create();

    $semester = Semester::query()->create([
        'academic_year_id' => $year->id,
        'name' => ['en' => 'Term 1'],
        'sequence' => 1,
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => now()->addMonths(3)->toDateString(),
        'is_active' => true,
        'is_current' => true,
    ]);

    AcademicContextService::flush();

    return [$year, $semester];
}

/*
|--------------------------------------------------------------------------
| The academic context on read responses
|--------------------------------------------------------------------------
*/

test('the index reports the current academic year and semester', function () {
    [$year, $semester] = currentYearWithSemester();
    makeActivity();

    $this->actingAs(activityActor(UserRole::Admin))
        ->get(route('system.activity.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('System/Activity/Index')
            ->has('activities.data', 1)
            ->where('context.academic_year_id', $year->id)
            ->where('context.semester_id', $semester->id)
        );
});

test('the show response reports the current academic year and semester', function () {
    [$year, $semester] = currentYearWithSemester();
    $activity = makeActivity();

    $this->actingAs(activityActor(UserRole::Admin))
        ->get(route('system.activity.show', $activity))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('System/Activity/Show')
            ->where('activity.id', $activity->id)
            ->where('context.academic_year_id', $year->id)
            ->where('context.semester_id', $semester->id)
        );
});

test('the context follows the year that is flagged current, not the newest row', function () {
    [$current] = currentYearWithSemester();

    // A later year exists but is not the current one.
    AcademicYear::factory()->create([
        'is_current' => false,
        'is_active' => true,
        'start_date' => now()->addYear()->toDateString(),
        'end_date' => now()->addYears(2)->toDateString(),
    ]);

    AcademicContextService::flush();

    $this->actingAs(activityActor(UserRole::Admin))
        ->get(route('system.activity.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('context.academic_year_id', $current->id)
        );
});

test('a client cannot relabel the context through the query string', function () {
    [$year, $semester] = currentYearWithSemester();

    $otherYear = AcademicYear::factory()->create(['is_current' => false, 'is_active' => true]);

    AcademicContextService::flush();

    $this->actingAs(activityActor(UserRole::Admin))
        ->get(route('system.activity.index', [
            'academic_year_id' => $otherYear->id,
            'semester_id' => 999,
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('context.academic_year_id', $year->id)
            ->where('context.semester_id', $semester->id)
        );
});

test('the log stays readable before any academic year is configured', function () {
    makeActivity();

    /*
     * requireContext() would throw here. The audit log has to stay open while
     * a school is still being set up, so the controller uses context() and
     * reports nulls instead of failing the request.
     */
    $this->actingAs(activityActor(UserRole::Admin))
        ->get(route('system.activity.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('context.academic_year_id', null)
            ->where('context.semester_id', null)
        );
});

/*
|--------------------------------------------------------------------------
| The entries themselves
|--------------------------------------------------------------------------
*/

test('an entry carries no academic ids of its own', function () {
    currentYearWithSemester();
    makeActivity();

    $this->actingAs(activityActor(UserRole::Admin))
        ->get(route('system.activity.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->missing('activities.data.0.academic_year_id')
            ->missing('activities.data.0.semester_id')
            ->where('activities.data.0.description', 'Student record updated')
            ->where('activities.data.0.event', 'updated')
            ->where('activities.data.0.subject_type_short', 'StudentProfile')
        );
});

test('the causer is reported when one is recorded', function () {
    currentYearWithSemester();

    $admin = activityActor(UserRole::Admin);

    makeActivity([
        'causer_type' => User::class,
        'causer_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('system.activity.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('activities.data.0.causer.name', $admin->name)
        );
});

test('entries can be filtered by event', function () {
    currentYearWithSemester();

    makeActivity(['event' => 'created', 'description' => 'Created a thing']);
    makeActivity(['event' => 'deleted', 'description' => 'Deleted a thing']);

    $this->actingAs(activityActor(UserRole::Admin))
        ->get(route('system.activity.index', ['event' => 'deleted']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('activities.data', 1)
            ->where('activities.data.0.event', 'deleted')
        );
});

/*
|--------------------------------------------------------------------------
| Access
|--------------------------------------------------------------------------
*/

test('an owner can open the activity log', function () {
    /*
     * Owners are pushed to onboarding until setup is finished, which would
     * mask the authorisation result being asserted here.
     */
    SchoolSetting::query()->create([
        'school_name' => 'Athr Test School',
        'school_code' => 'ATHR',
        'setup_completed' => true,
    ]);

    $this->actingAs(activityActor(UserRole::Owner))
        ->get(route('system.activity.index'))
        ->assertOk();
});

test('a teacher is forbidden from the activity log', function () {
    $this->actingAs(activityActor(UserRole::Teacher))
        ->get(route('system.activity.index'))
        ->assertForbidden();
});

test('a student is forbidden from the activity log', function () {
    $this->actingAs(activityActor(UserRole::Student))
        ->get(route('system.activity.index'))
        ->assertForbidden();
});

test('a guardian is forbidden from a single activity entry', function () {
    $activity = makeActivity();

    $this->actingAs(activityActor(UserRole::Guardian))
        ->get(route('system.activity.show', $activity))
        ->assertForbidden();
});

test('a guest is redirected to login rather than shown the activity log', function () {
    $this->get(route('system.activity.index'))
        ->assertRedirect(route('login'));
});

test('the log is read-only: there is no write route', function () {
    expect(Route::has('system.activity.store'))->toBeFalse()
        ->and(Route::has('system.activity.update'))->toBeFalse()
        ->and(Route::has('system.activity.destroy'))->toBeFalse();
});
