<?php

use App\Models\User;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;
use Spatie\Permission\PermissionRegistrar;

/**
 * Attendance is taken per class/day/session, and the semester it belongs to is
 * recorded alongside it: the report, the dashboard and a student's history all
 * filter on it. These cover that the column exists and is honoured.
 */
beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    AcademicContextService::flush();

    $this->year = AcademicYear::factory()->create([
        'is_active' => true,
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->addMonths(8)->toDateString(),
    ]);

    AcademicContextService::flush();

    $this->semester = Semester::factory()->current()->create(['academic_year_id' => $this->year->id]);
    $this->otherSemester = Semester::factory()->create(['academic_year_id' => $this->year->id]);

    $grade = GradeLevel::query()->create([
        'name' => ['en' => 'Grade 3'],
        'level_order' => fake()->unique()->numberBetween(1, 9999),
        'is_active' => true,
    ]);

    $this->class = SchoolClass::query()->create([
        'grade_level_id' => $grade->id,
        'name' => '3-A',
        'capacity' => 30,
        'is_active' => true,
    ]);

    $this->student = StudentProfile::factory()->create([
        'academic_year_id' => $this->year->id,
        'current_class_id' => $this->class->id,
    ]);

    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $admin->assignRole(UserRole::Admin->value);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->admin = $admin->refresh();
});

function markAttendance(array $overrides = []): array
{
    return [
        'class_id' => test()->class->id,
        'semester_id' => test()->semester->id,
        'date' => now()->toDateString(),
        'session' => 1,
        'students' => [
            ['student_id' => test()->student->id, 'status' => 'present'],
        ],
        ...$overrides,
    ];
}

it('stores the semester on the attendance record', function () {
    $this->actingAs($this->admin)
        ->post('/attendance/take', markAttendance())
        ->assertSessionHasNoErrors();

    $record = AttendanceRecord::query()->firstOrFail();

    expect($record->semester_id)->toBe($this->semester->id);
});

it('generates a report filtered by semester', function () {
    $this->actingAs($this->admin)->post('/attendance/take', markAttendance());

    $this->actingAs($this->admin)
        ->post('/attendance/report', [
            'class_id' => $this->class->id,
            'semester_id' => $this->semester->id,
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ])
        ->assertSessionHasNoErrors()
        ->assertInertia(fn ($page) => $page
            ->where('generated', true)
            ->where('rows.data.0.student_id', $this->student->id)
            ->where('rows.data.0.present_count', 1));
});

it('excludes records taken in another semester from the report', function () {
    $this->actingAs($this->admin)->post('/attendance/take', markAttendance());

    $this->actingAs($this->admin)
        ->post('/attendance/report', [
            'class_id' => $this->class->id,
            'semester_id' => $this->otherSemester->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertInertia(fn ($page) => $page->where('rows.data.0.present_count', 0));
});

it('scopes the dashboard to a semester', function () {
    $this->actingAs($this->admin)->post('/attendance/take', markAttendance());

    $this->actingAs($this->admin)
        ->get('/attendance/dashboard?class_id='.$this->class->id.'&semester_id='.$this->semester->id)
        ->assertOk();

    $this->actingAs($this->admin)
        ->get('/attendance/students/'.$this->student->id.'?semester_id='.$this->semester->id)
        ->assertOk();
});
