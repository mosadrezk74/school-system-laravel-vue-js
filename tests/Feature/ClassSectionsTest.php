<?php

use App\Models\User;
use Illuminate\Database\QueryException;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Services\AcademicYearService;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Classroom\Models\Classroom;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;
use Spatie\Permission\PermissionRegistrar;

/**
 * A grade level runs many sections in the same year. These cover the rule that
 * replaced "one class per grade level": unique on year + grade + name.
 */
beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    AcademicYearService::flush();
});

function classAdmin(): User
{
    $user = User::factory()->create(['role' => UserRole::Admin->value]);
    $user->assignRole(UserRole::Admin->value);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user->refresh();
}

function currentYear(): AcademicYear
{
    $year = AcademicYear::factory()->create([
        'is_active' => true,
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->addMonths(8)->toDateString(),
    ]);

    AcademicYearService::flush();

    return $year;
}

function makeGrade(string $name = 'Grade 3'): GradeLevel
{
    return GradeLevel::query()->create([
        'name' => ['en' => $name],
        'level_order' => fake()->unique()->numberBetween(1, 9999),
        'is_active' => true,
    ]);
}

function section(string $name, int $capacity = 30, ?int $classroomId = null): array
{
    return ['name' => $name, 'capacity' => $capacity, 'classroom_id' => $classroomId, 'is_active' => true];
}

// --- Single class (must keep working) --------------------------------------

it('still creates a single class', function () {
    currentYear();
    $grade = makeGrade();

    $this->actingAs(classAdmin())
        ->post('/academic/classes', [
            'grade_level_id' => $grade->id,
            'name' => '3-A',
            'capacity' => 30,
        ])
        ->assertSessionHasNoErrors();

    expect(SchoolClass::query()->where('name', '3-A')->count())->toBe(1);
});

// --- Batch creation --------------------------------------------------------

it('creates four sections under one grade level', function () {
    $year = currentYear();
    $grade = makeGrade();

    $this->actingAs(classAdmin())
        ->post('/academic/classes/sections', [
            'grade_level_id' => $grade->id,
            'sections' => [section('3-A'), section('3-B'), section('3-C'), section('3-D')],
        ])
        ->assertSessionHasNoErrors();

    // Classes are no longer year-scoped, so the grade level alone identifies them.
    expect(SchoolClass::query()
        ->where('grade_level_id', $grade->id)
        ->count())->toBe(4);
});

it('creates ten sections under one grade level', function () {
    $grade = makeGrade();
    currentYear();

    $sections = collect(range(1, 10))
        ->map(fn (int $n): array => section('3-'.chr(64 + $n)))
        ->all();

    $this->actingAs(classAdmin())
        ->post('/academic/classes/sections', [
            'grade_level_id' => $grade->id,
            'sections' => $sections,
        ])
        ->assertSessionHasNoErrors();

    expect(SchoolClass::query()->where('grade_level_id', $grade->id)->count())->toBe(10);
});

// --- Uniqueness ------------------------------------------------------------

it('rejects two sections with the same name in one submission', function () {
    currentYear();
    $grade = makeGrade();

    $this->actingAs(classAdmin())
        ->post('/academic/classes/sections', [
            'grade_level_id' => $grade->id,
            'sections' => [section('3-A'), section('3-A')],
        ])
        ->assertSessionHasErrors();

    expect(SchoolClass::query()->count())->toBe(0);
});

it('rejects a section that already exists for the grade level', function () {
    currentYear();
    $grade = makeGrade();

    $this->actingAs(classAdmin())->post('/academic/classes/sections', [
        'grade_level_id' => $grade->id,
        'sections' => [section('3-A'), section('3-B')],
    ]);

    $this->actingAs(classAdmin())
        ->post('/academic/classes/sections', [
            'grade_level_id' => $grade->id,
            'sections' => [section('3-C'), section('3-D'), section('3-B')],
        ])
        ->assertSessionHasErrors();

    // The whole batch rolls back, so 3-C and 3-D are not left behind either.
    expect(SchoolClass::query()->where('grade_level_id', $grade->id)->count())->toBe(2);
});

it('allows the same section name under a different grade level', function () {
    currentYear();
    $grade3 = makeGrade('Grade 3');
    $grade4 = makeGrade('Grade 4');

    $admin = classAdmin();

    $this->actingAs($admin)->post('/academic/classes/sections', [
        'grade_level_id' => $grade3->id,
        'sections' => [section('3-A')],
    ])->assertSessionHasNoErrors();

    $this->actingAs($admin)->post('/academic/classes/sections', [
        'grade_level_id' => $grade4->id,
        'sections' => [section('3-A')],
    ])->assertSessionHasNoErrors();

    expect(SchoolClass::query()->where('name', '3-A')->count())->toBe(2);
});

it('refuses the same section name in the same grade level whatever the year', function () {
    currentYear();
    $grade = makeGrade();

    SchoolClass::query()->create([
        'grade_level_id' => $grade->id,
        'name' => '3-A',
        'capacity' => 30,
        'is_active' => true,
    ]);

    /*
     * Classes are no longer scoped to an academic year, so a section name is
     * unique within its grade level outright — the same name can no longer be
     * reused by moving to a different year.
     */
    expect(fn () => SchoolClass::query()->create([
        'grade_level_id' => $grade->id,
        'name' => '3-A',
        'capacity' => 30,
        'is_active' => true,
    ]))->toThrow(QueryException::class);
});

// --- Atomicity -------------------------------------------------------------

it('creates nothing when one row in the batch is invalid', function () {
    currentYear();
    $grade = makeGrade();

    $this->actingAs(classAdmin())
        ->post('/academic/classes/sections', [
            'grade_level_id' => $grade->id,
            'sections' => [
                section('3-A'),
                section('3-B'),
                // Capacity below the minimum: the batch must not half-apply.
                ['name' => '3-C', 'capacity' => 0, 'is_active' => true],
            ],
        ])
        ->assertSessionHasErrors();

    expect(SchoolClass::query()->count())->toBe(0);
});

// --- Classroom -------------------------------------------------------------

it('rejects a classroom that does not exist', function () {
    currentYear();
    $grade = makeGrade();

    $this->actingAs(classAdmin())
        ->post('/academic/classes/sections', [
            'grade_level_id' => $grade->id,
            'sections' => [section('3-A', 30, 999999)],
        ])
        ->assertSessionHasErrors();

    expect(SchoolClass::query()->count())->toBe(0);
});

it('accepts a valid classroom on each section', function () {
    currentYear();
    $grade = makeGrade();

    $roomA = Classroom::query()->create(['name' => ['en' => 'Room A'], 'capacity' => 30, 'is_active' => true]);
    $roomB = Classroom::query()->create(['name' => ['en' => 'Room B'], 'capacity' => 30, 'is_active' => true]);

    $this->actingAs(classAdmin())
        ->post('/academic/classes/sections', [
            'grade_level_id' => $grade->id,
            'sections' => [section('3-A', 30, $roomA->id), section('3-B', 25, $roomB->id)],
        ])
        ->assertSessionHasNoErrors();

    expect(SchoolClass::query()->whereNotNull('classroom_id')->count())->toBe(2);
});

// --- Permissions -----------------------------------------------------------

it('refuses batch creation to a user without the class create permission', function () {
    currentYear();
    $grade = makeGrade();

    $guardian = User::factory()->create(['role' => UserRole::Guardian->value]);
    $guardian->assignRole(UserRole::Guardian->value);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->actingAs($guardian->refresh())
        ->post('/academic/classes/sections', [
            'grade_level_id' => $grade->id,
            'sections' => [section('3-A')],
        ])
        ->assertForbidden();

    expect(SchoolClass::query()->count())->toBe(0);
});

// --- Existing relationships ------------------------------------------------

it('leaves an existing class and its student attachment untouched', function () {
    $year = currentYear();
    $grade = makeGrade();

    $existing = SchoolClass::query()->create([
        'grade_level_id' => $grade->id,
        'name' => '3-A',
        'capacity' => 30,
        'is_active' => true,
    ]);

    $student = StudentProfile::factory()->create([
        'academic_year_id' => $year->id,
        'current_class_id' => $existing->id,
    ]);

    $this->actingAs(classAdmin())->post('/academic/classes/sections', [
        'grade_level_id' => $grade->id,
        'sections' => [section('3-B'), section('3-C')],
    ])->assertSessionHasNoErrors();

    expect($student->refresh()->current_class_id)->toBe($existing->id)
        ->and($existing->refresh()->exists)->toBeTrue()
        ->and(SchoolClass::query()->where('grade_level_id', $grade->id)->count())->toBe(3);
});
