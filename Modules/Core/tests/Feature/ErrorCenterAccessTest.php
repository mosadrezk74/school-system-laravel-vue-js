<?php

use App\Models\User;
use Illuminate\Support\Str;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Enums\ErrorReportStatus;
use Modules\Core\Models\ErrorReport;
use Modules\Setting\Models\SchoolSetting;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function errorCentreActor(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    return $user;
}

function makeErrorReport(array $attributes = []): ErrorReport
{
    return ErrorReport::create([
        'fingerprint' => bin2hex(random_bytes(16)),
        'status' => ErrorReportStatus::New,
        'level' => 'error',
        'error_code' => 'INTERNAL_SERVER_ERROR',
        'status_code' => 500,
        'exception' => RuntimeException::class,
        'message' => 'Something exploded',
        'file' => '/app/Http/Controllers/Thing.php',
        'line' => 42,
        'route' => 'things.index',
        'method' => 'GET',
        'url' => 'http://localhost/things',
        'request_id' => (string) Str::uuid(),
        'trace' => "#0 /app/internal/secret/path.php(12): boom()\n#1 {main}",
        'occurrences' => 1,
        'first_seen_at' => now(),
        'last_seen_at' => now(),
        ...$attributes,
    ]);
}

test('an admin can open the error centre', function () {
    makeErrorReport();

    $this->actingAs(errorCentreActor(UserRole::Admin))
        ->get(route('system.errors.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Errors/Admin/Index')
            ->has('reports.data', 1)
        );
});

test('an owner can open the error centre', function () {
    /*
     * Owners are pushed to onboarding until setup is finished, which would
     * mask the authorisation result being asserted here.
     */
    SchoolSetting::query()->create([
        'school_name' => 'Athr Test School',
        'school_code' => 'ATHR',
        'setup_completed' => true,
    ]);

    $this->actingAs(errorCentreActor(UserRole::Owner))
        ->get(route('system.errors.index'))
        ->assertOk();
});

test('a teacher is forbidden from the error centre', function () {
    $this->actingAs(errorCentreActor(UserRole::Teacher))
        ->get(route('system.errors.index'))
        ->assertForbidden();
});

test('a student is forbidden from the error centre', function () {
    $this->actingAs(errorCentreActor(UserRole::Student))
        ->get(route('system.errors.index'))
        ->assertForbidden();
});

test('a guardian is forbidden from the error centre', function () {
    $this->actingAs(errorCentreActor(UserRole::Guardian))
        ->get(route('system.errors.index'))
        ->assertForbidden();
});

test('a guest is redirected to login rather than shown system errors', function () {
    $this->get(route('system.errors.index'))
        ->assertRedirect(route('login'));
});

test('a teacher cannot read a single error report', function () {
    $report = makeErrorReport();

    $this->actingAs(errorCentreActor(UserRole::Teacher))
        ->get(route('system.errors.show', $report))
        ->assertForbidden();
});

test('an admin sees the stack trace because the role holds the grant', function () {
    $report = makeErrorReport();

    $this->actingAs(errorCentreActor(UserRole::Admin))
        ->get(route('system.errors.show', $report))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Errors/Admin/Show')
            ->where('trace', $report->trace)
        );
});

test('the trace is withheld from an admin without the trace permission', function () {
    $report = makeErrorReport();

    $user = errorCentreActor(UserRole::Admin);
    $user->revokePermissionTo('error-report.view-trace');
    $user->roles()->first()->revokePermissionTo('error-report.view-trace');

    $this->actingAs($user)
        ->get(route('system.errors.show', $report))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('trace', null));
});

test('an admin can mark an error as resolved and the resolver is recorded', function () {
    $report = makeErrorReport();
    $admin = errorCentreActor(UserRole::Admin);

    $this->actingAs($admin)
        ->patch(route('system.errors.update', $report), ['status' => 'resolved'])
        ->assertRedirect();

    $report->refresh();

    expect($report->status)->toBe(ErrorReportStatus::Resolved)
        ->and($report->resolved_by)->toBe($admin->id)
        ->and($report->resolved_at)->not->toBeNull();
});

test('reopening an error clears the resolution record', function () {
    $admin = errorCentreActor(UserRole::Admin);
    $report = makeErrorReport([
        'status' => ErrorReportStatus::Resolved,
        'resolved_at' => now(),
        'resolved_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->patch(route('system.errors.update', $report), ['status' => 'new'])
        ->assertRedirect();

    $report->refresh();

    expect($report->status)->toBe(ErrorReportStatus::New)
        ->and($report->resolved_by)->toBeNull()
        ->and($report->resolved_at)->toBeNull();
});

test('an unknown status is rejected', function () {
    $report = makeErrorReport();

    $this->actingAs(errorCentreActor(UserRole::Admin))
        ->patch(route('system.errors.update', $report), ['status' => 'exploded'])
        ->assertSessionHasErrors('status');
});

test('a teacher cannot change an error status', function () {
    $report = makeErrorReport();

    $this->actingAs(errorCentreActor(UserRole::Teacher))
        ->patch(route('system.errors.update', $report), ['status' => 'resolved'])
        ->assertForbidden();

    expect($report->fresh()->status)->toBe(ErrorReportStatus::New);
});
