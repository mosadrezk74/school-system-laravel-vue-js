<?php

use App\Models\User;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Finance\Models\FeeCategory;
use Modules\Finance\Models\FeeStructure;
use Modules\Finance\Models\StudentFee;
use Modules\Student\Models\StudentProfile;
use Spatie\Permission\PermissionRegistrar;

/**
 * The two fields that were previously only reachable from the database: a
 * student's scholarship and a fee structure's subscription period.
 */
beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    // AcademicContextService memoises the current year in a static, which would
    // otherwise survive RefreshDatabase and point at a rolled-back row.
    AcademicContextService::flush();
});

/**
 * The year the app treats as current. UpdateStudentDTO reads the enrolment
 * year from the service rather than the student, so it must exist and be
 * active for any student write to succeed.
 */
function currentAcademicYear(): AcademicYear
{
    $year = AcademicYear::factory()->create([
        'is_active' => true,
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->addMonths(8)->toDateString(),
    ]);

    AcademicContextService::flush();

    return $year;
}

function adminUser(): User
{
    $user = User::factory()->create(['role' => UserRole::Admin->value]);
    $user->assignRole(UserRole::Admin->value);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user->refresh();
}

function studentPayload(StudentProfile $student, array $overrides = []): array
{
    return [
        'name' => 'A B',
        // The request validates `email:rfc,dns`, so the domain must resolve.
        'email' => 'student'.$student->id.'@gmail.com',
        'username' => 'student'.$student->id,
        'gender' => 'male',
        'birth_date' => now()->subYears(12)->toDateString(),
        'admission_date' => now()->subYear()->toDateString(),
        ...$overrides,
    ];
}

// --- Scholarship -----------------------------------------------------------

it('accepts scholarship percentages across the valid range', function (float $percentage) {
    // UpdateStudentAction refuses to touch a student whose year is inactive,
    // so the fixture has to reflect a real, current enrolment.
    $student = StudentProfile::factory()->create([
        'academic_year_id' => currentAcademicYear()->id,
    ]);

    $this->actingAs(adminUser())
        ->put("/user-management/students/{$student->id}", studentPayload($student, [
            'scholarship_percentage' => $percentage,
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    // Read straight from the row: the factory instance in hand is stale, and
    // refresh() on it was masking the value the request actually stored.
    $stored = DB::table('student_profiles')->where('id', $student->id)->value('scholarship_percentage');

    expect((float) $stored)->toBe($percentage);
})->with([0.0, 50.0, 100.0]);

it('rejects a scholarship outside 0-100', function (float $percentage) {
    $student = StudentProfile::factory()->create([
        'academic_year_id' => currentAcademicYear()->id,
    ]);

    $this->actingAs(adminUser())
        ->put("/user-management/students/{$student->id}", studentPayload($student, [
            'scholarship_percentage' => $percentage,
        ]))
        ->assertSessionHasErrors('scholarship_percentage');
})->with([-1.0, 101.0, 150.0]);

it('bills a fee using the stored scholarship rather than any submitted amount', function () {
    $student = StudentProfile::factory()->create([
        'academic_year_id' => currentAcademicYear()->id,
        'scholarship_percentage' => 50,
    ]);

    $category = FeeCategory::query()->create([
        'code' => 'tuition',
        'name' => ['en' => 'Tuition'],
        'is_active' => true,
    ]);

    $structure = FeeStructure::query()->create([
        'fee_category_id' => $category->id,
        'name' => ['en' => 'Term 1'],
        'amount' => 10000,
        'frequency' => 'semester',
    ]);

    $this->actingAs(adminUser())
        ->post('/finance/student-fees', [
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'amount' => 10000,
            // A tampered discount: the server must ignore it and use the profile.
            'discount_amount' => 9999,
        ]);

    $fee = StudentFee::query()->where('student_id', $student->id)->firstOrFail();

    expect((float) $fee->amount)->toBe(10000.0)
        ->and((float) $fee->discount_amount)->toBe(5000.0)
        ->and((float) $fee->final_amount)->toBe(5000.0);
});

// --- Fee structure subscription period -------------------------------------

function makeYearWithSemester(): array
{
    $year = AcademicYear::factory()->create([
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
    ]);

    $semester = Semester::query()->create([
        'academic_year_id' => $year->id,
        'name' => ['en' => 'Term 1'],
        'sequence' => 1,
        'start_date' => '2026-09-01',
        'end_date' => '2026-12-31',
    ]);

    return [$year, $semester];
}

function feeStructurePayload(array $overrides = []): array
{
    $category = FeeCategory::query()->create([
        'code' => 'cat'.fake()->unique()->numerify('###'),
        'name' => ['en' => 'Tuition'],
        'is_active' => true,
    ]);

    return [
        'fee_category_id' => $category->id,
        'name' => 'Term subscription',
        'amount' => 10000,
        'frequency' => 'semester',
        ...$overrides,
    ];
}

it('stores a term subscription against its academic year and semester', function () {
    [$year, $semester] = makeYearWithSemester();

    $this->actingAs(adminUser())
        ->post('/finance/fee-structures', feeStructurePayload([
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
        ]))
        ->assertSessionHasNoErrors();

    $structure = FeeStructure::query()->latest('id')->firstOrFail();

    expect($structure->academic_year_id)->toBe($year->id)
        ->and($structure->semester_id)->toBe($semester->id)
        ->and($structure->isTermSubscription())->toBeTrue();
});

it('stores a full-year subscription with no semester', function () {
    [$year] = makeYearWithSemester();

    $this->actingAs(adminUser())
        ->post('/finance/fee-structures', feeStructurePayload([
            'academic_year_id' => $year->id,
            'semester_id' => null,
            'frequency' => 'annual',
        ]))
        ->assertSessionHasNoErrors();

    $structure = FeeStructure::query()->latest('id')->firstOrFail();

    expect($structure->isFullYearSubscription())->toBeTrue()
        ->and($structure->semester_id)->toBeNull();
});

it('rejects a semester belonging to a different academic year', function () {
    [, $semester] = makeYearWithSemester();
    $otherYear = AcademicYear::factory()->create();

    $this->actingAs(adminUser())
        ->post('/finance/fee-structures', feeStructurePayload([
            'academic_year_id' => $otherYear->id,
            'semester_id' => $semester->id,
        ]))
        ->assertSessionHasErrors('semester_id');
});

it('derives the duration from the semester dates rather than storing it', function () {
    [$year, $semester] = makeYearWithSemester();

    $structure = FeeStructure::query()->create([
        'fee_category_id' => FeeCategory::query()->create([
            'code' => 'tuition-d', 'name' => ['en' => 'Tuition'], 'is_active' => true,
        ])->id,
        'academic_year_id' => $year->id,
        'semester_id' => $semester->id,
        'name' => ['en' => 'Term 1'],
        'amount' => 10000,
        'frequency' => 'semester',
    ]);

    // 01/09 → 31/12 is four months, read off the semester, not a stored column.
    expect($structure->load('semester')->durationInMonths())->toBe(4)
        ->and(Schema::hasColumn('fee_structures', 'duration_months'))->toBeFalse();
});
