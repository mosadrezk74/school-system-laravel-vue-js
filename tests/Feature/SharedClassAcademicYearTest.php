<?php

use App\Models\User;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Services\AcademicContextService;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Communication\Services\RecipientResolverService;
use Modules\Core\Queries\SchoolOverviewQuery;
use Modules\Promotion\Queries\PromotionEligibilityQuery;
use Modules\Promotion\Repositories\PromotionRepository;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Setting\Models\SchoolSetting;
use Modules\Student\Models\StudentProfile;

/**
 * Classes are timeless structures shared by every academic year, so the same
 * class record legitimately holds students from more than one year at once.
 *
 * Every count or list that represents a *cohort* therefore has to filter on
 * student_profiles.academic_year_id. Without that, last year's students make a
 * class look full, look occupied, and receive this year's announcements.
 */
beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    SchoolSetting::query()->create([
        'school_name' => 'Athr Test School',
        'school_code' => 'ATHR',
        'setup_completed' => true,
    ]);

    $this->lastYear = AcademicYear::factory()->create([
        'name' => '2024/2025',
        'is_current' => false,
        'is_active' => true,
    ]);

    $this->thisYear = AcademicYear::factory()->create([
        'name' => '2025/2026',
        'is_current' => true,
        'is_active' => true,
    ]);

    AcademicContextService::flush();
    AcademicContextService::flush();

    $this->grade = GradeLevel::query()->create([
        'name' => ['en' => 'Grade 5'],
        'display_order' => 5,
        'is_active' => true,
    ]);

    // One class record, deliberately shared by both years.
    $this->sharedClass = SchoolClass::query()->create([
        'grade_level_id' => $this->grade->id,
        'name' => '5A',
        'capacity' => 3,
        'is_active' => true,
    ]);
});

function sharedClassStudent(AcademicYear $year, ?SchoolClass $class = null): StudentProfile
{
    $student = StudentProfile::factory()->create([
        'academic_year_id' => $year->id,
        'current_class_id' => ($class ?? test()->sharedClass)->id,
    ]);

    $student->user->update(['role' => UserRole::Student, 'is_active' => true]);
    $student->user->assignRole('student');

    return $student->refresh();
}

/*
|--------------------------------------------------------------------------
| The premise: one class, two cohorts
|--------------------------------------------------------------------------
*/

test('the same class can hold students from different academic years', function () {
    $old = sharedClassStudent($this->lastYear);
    $new = sharedClassStudent($this->thisYear);

    expect($old->current_class_id)->toBe($this->sharedClass->id)
        ->and($new->current_class_id)->toBe($this->sharedClass->id)
        ->and($old->academic_year_id)->not->toBe($new->academic_year_id)
        ->and(StudentProfile::query()->where('current_class_id', $this->sharedClass->id)->count())
        ->toBe(2);
});

/*
|--------------------------------------------------------------------------
| Promotion capacity counts the target year only
|--------------------------------------------------------------------------
*/

test('capacity counts only students belonging to the target academic year', function () {
    // Three students fill the class for last year — capacity is 3.
    sharedClassStudent($this->lastYear);
    sharedClassStudent($this->lastYear);
    sharedClassStudent($this->lastYear);

    $repository = app(PromotionRepository::class);

    $lastYearCounts = $repository->activeCountsByClass([$this->sharedClass->id], $this->lastYear->id);
    $thisYearCounts = $repository->activeCountsByClass([$this->sharedClass->id], $this->thisYear->id);

    // Full for last year, entirely free for this one.
    expect($lastYearCounts[$this->sharedClass->id] ?? 0)->toBe(3)
        ->and($thisYearCounts[$this->sharedClass->id] ?? 0)->toBe(0);
});

test('a class filled by last year does not block promotion into this year', function () {
    sharedClassStudent($this->lastYear);
    sharedClassStudent($this->lastYear);
    sharedClassStudent($this->lastYear);

    $counts = app(PromotionRepository::class)
        ->activeCountsByClass([$this->sharedClass->id], $this->thisYear->id);

    // capacity 3, zero taken for the target year -> the intake fits.
    expect(($counts[$this->sharedClass->id] ?? 0) + 3)->toBeLessThanOrEqual((int) $this->sharedClass->capacity);
});

test('capacity ignores inactive and graduated students', function () {
    $active = sharedClassStudent($this->thisYear);
    $inactive = sharedClassStudent($this->thisYear);
    $graduated = sharedClassStudent($this->thisYear);

    $inactive->update(['is_active' => false]);
    $graduated->update(['is_graduated' => true]);

    $counts = app(PromotionRepository::class)
        ->activeCountsByClass([$this->sharedClass->id], $this->thisYear->id);

    expect($counts[$this->sharedClass->id] ?? 0)->toBe(1)
        ->and($active->is_active)->toBeTrue();
});

test('the wizard reports occupancy for the target year not every year', function () {
    sharedClassStudent($this->lastYear);
    sharedClassStudent($this->lastYear);

    $forThisYear = app(PromotionEligibilityQuery::class)
        ->targetClasses($this->thisYear->id)
        ->firstWhere('class_id', $this->sharedClass->id);

    $forLastYear = app(PromotionEligibilityQuery::class)
        ->targetClasses($this->lastYear->id)
        ->firstWhere('class_id', $this->sharedClass->id);

    expect($forThisYear['occupied'])->toBe(0)
        ->and($forThisYear['available'])->toBe(3)
        ->and($forLastYear['occupied'])->toBe(2)
        ->and($forLastYear['available'])->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Dashboard emptiness is per year
|--------------------------------------------------------------------------
*/

test('a class used only by another year counts as empty for this year', function () {
    sharedClassStudent($this->lastYear);

    $overview = app(SchoolOverviewQuery::class);

    // Occupied last year, but nobody is enrolled in it for this one.
    expect($overview->classesWithoutStudents($this->thisYear->id))->toBe(1)
        ->and($overview->classesWithoutStudents($this->lastYear->id))->toBe(0);
});

test('a class with a student this year is not reported as empty', function () {
    sharedClassStudent($this->thisYear);

    expect(app(SchoolOverviewQuery::class)->classesWithoutStudents($this->thisYear->id))->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Broadcasting by class reaches one cohort
|--------------------------------------------------------------------------
*/

test('broadcasting to a class excludes students from another academic year', function () {
    $old = sharedClassStudent($this->lastYear);
    $current = sharedClassStudent($this->thisYear);

    $recipientIds = app(RecipientResolverService::class)
        ->resolve(RecipientResolverService::TYPE_CLASS, [$this->sharedClass->id])
        ->pluck('id');

    expect($recipientIds)->toContain($current->user_id)
        ->and($recipientIds)->not->toContain($old->user_id);
});

test('broadcasting to a class still reaches this year students', function () {
    $a = sharedClassStudent($this->thisYear);
    $b = sharedClassStudent($this->thisYear);

    $recipientIds = app(RecipientResolverService::class)
        ->resolve(RecipientResolverService::TYPE_CLASS, [$this->sharedClass->id])
        ->pluck('id');

    expect($recipientIds)->toContain($a->user_id)
        ->and($recipientIds)->toContain($b->user_id);
});

/*
|--------------------------------------------------------------------------
| Authorization is unchanged
|--------------------------------------------------------------------------
*/

test('a teacher still cannot reach the promotion screens', function () {
    $teacher = User::factory()->create(['role' => UserRole::Teacher, 'is_active' => true]);
    $teacher->assignRole('teacher');

    $this->actingAs($teacher)->get(route('promotions.index'))->assertForbidden();
    $this->actingAs($teacher)->get(route('promotions.create'))->assertForbidden();
});

test('a student and a guardian still cannot reach the promotion screens', function (UserRole $role) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    $this->actingAs($user)->get(route('promotions.index'))->assertForbidden();
})->with([
    'student' => UserRole::Student,
    'guardian' => UserRole::Guardian,
]);
