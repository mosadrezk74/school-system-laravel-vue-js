<?php

use App\Models\User;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Promotion\DTOs\PromotionDecisionDTO;
use Modules\Promotion\Enums\PromotionStatus;
use Modules\Promotion\Exceptions\PromotionOperationException;
use Modules\Promotion\Models\StudentPromotion;
use Modules\Promotion\Services\PromotionGuardService;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Setting\Models\SchoolSetting;
use Modules\Student\Models\StudentProfile;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    SchoolSetting::query()->create([
        'school_name' => 'Athr Test School',
        'school_code' => 'ATHR',
        'setup_completed' => true,
    ]);

    $this->year = AcademicYear::factory()->create([
        'name' => '2025/2026',
        'is_current' => true,
        'is_active' => true,
    ]);

    $this->nextYear = AcademicYear::factory()->create([
        'name' => '2026/2027',
        'is_current' => false,
        'is_active' => true,
    ]);

    $this->semester = Semester::query()->create([
        'academic_year_id' => $this->year->id,
        'name' => ['en' => 'First Term'],
        'sequence' => 1,
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->addMonths(2)->toDateString(),
        'is_active' => true,
        'is_current' => true,
    ]);

    // The term the promotion targets — belongs to the *next* year, which is
    // what the year/semester pairing rules are checked against.
    $this->nextYearSemester = Semester::query()->create([
        'academic_year_id' => $this->nextYear->id,
        'name' => ['en' => 'First Semester', 'ar' => 'الفصل الأول'],
        'sequence' => 1,
        'start_date' => now()->addMonths(6)->toDateString(),
        'end_date' => now()->addMonths(10)->toDateString(),
        'is_active' => true,
    ]);

    AcademicContextService::flush();

    $this->grade5 = GradeLevel::query()->create([
        'name' => ['en' => 'Grade 5'], 'display_order' => 5, 'is_active' => true,
    ]);
    $this->grade6 = GradeLevel::query()->create([
        'name' => ['en' => 'Grade 6'], 'display_order' => 6, 'is_active' => true,
    ]);

    $this->classA = promoClass($this->year, $this->grade5, '5A');
    $this->targetClass = promoClass($this->nextYear, $this->grade6, '6A');
});

function promoClass(AcademicYear $year, GradeLevel $grade, string $name, int $capacity = 30): SchoolClass
{
    return SchoolClass::query()->create([
        'grade_level_id' => $grade->id,
        'name' => $name,
        'capacity' => $capacity,
        'is_active' => true,
    ]);
}

function promoStudent(?SchoolClass $class = null, ?AcademicYear $year = null): StudentProfile
{
    $student = StudentProfile::factory()->create([
        'academic_year_id' => ($year ?? test()->year)->id,
        'current_class_id' => ($class ?? test()->classA)->id,
    ]);

    $student->user->update(['role' => UserRole::Student, 'is_active' => true]);
    $student->user->assignRole('student');

    return $student->refresh();
}

function promoActor(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user;
}

/**
 * @param  array<int, array<string, mixed>>  $decisions
 */
function promote(User $actor, array $decisions, ?int $targetYearId = null, ?int $targetSemesterId = null)
{
    return test()->actingAs($actor)->post(route('promotions.store'), [
        'to_academic_year_id' => $targetYearId ?? test()->nextYear->id,
        'to_semester_id' => $targetSemesterId ?? test()->nextYearSemester->id,
        'decisions' => $decisions,
    ]);
}

function decision(StudentProfile $student, string $status = 'promoted', ?int $classId = null): array
{
    return [
        'student_id' => $student->id,
        'status' => $status,
        'target_class_id' => $classId ?? ($status === 'promoted' ? test()->targetClass->id : null),
    ];
}

/*
|--------------------------------------------------------------------------
| Authorization
|--------------------------------------------------------------------------
*/

test('an admin can open the promotions screen', function () {
    $this->actingAs(promoActor(UserRole::Admin))
        ->get(route('promotions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Promotion::Promotions/Index'));
});

test('an owner can open the promotion wizard', function () {
    $this->actingAs(promoActor(UserRole::Owner))
        ->get(route('promotions.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Promotion::Promotions/Create'));
});

test('a teacher is forbidden from promotions', function () {
    $actor = promoActor(UserRole::Teacher);

    $this->actingAs($actor)->get(route('promotions.index'))->assertForbidden();
    $this->actingAs($actor)->get(route('promotions.create'))->assertForbidden();
});

test('a student is forbidden from promotions', function () {
    $this->actingAs(promoActor(UserRole::Student))
        ->get(route('promotions.index'))
        ->assertForbidden();
});

test('a guardian is forbidden from promotions', function () {
    $this->actingAs(promoActor(UserRole::Guardian))
        ->get(route('promotions.index'))
        ->assertForbidden();
});

test('a teacher cannot call the promotion endpoint directly', function () {
    $student = promoStudent();

    promote(promoActor(UserRole::Teacher), [decision($student)])->assertForbidden();

    expect(StudentPromotion::query()->count())->toBe(0)
        ->and($student->fresh()->academic_year_id)->toBe($this->year->id);
});

test('a guest is redirected to login', function () {
    $this->get(route('promotions.index'))->assertRedirect();
});

/*
|--------------------------------------------------------------------------
| Promoting
|--------------------------------------------------------------------------
*/

test('a single student is promoted into the target year and class', function () {
    $student = promoStudent();

    promote(promoActor(UserRole::Admin), [decision($student)])
        ->assertRedirect(route('promotions.index'));

    $student->refresh();

    expect($student->academic_year_id)->toBe($this->nextYear->id)
        ->and($student->current_class_id)->toBe($this->targetClass->id);

    $record = StudentPromotion::query()->first();

    expect($record->status)->toBe(PromotionStatus::Promoted)
        ->and($record->from_academic_year_id)->toBe($this->year->id)
        ->and($record->to_academic_year_id)->toBe($this->nextYear->id)
        ->and($record->to_semester_id)->toBe($this->nextYearSemester->id)
        ->and($record->from_class_id)->toBe($this->classA->id)
        ->and($record->to_class_id)->toBe($this->targetClass->id);
});

/*
|--------------------------------------------------------------------------
| Target academic context — year, semester and class must agree
|--------------------------------------------------------------------------
*/

test('the promotion record stores the selected target semester', function () {
    $student = promoStudent();

    promote(promoActor(UserRole::Admin), [decision($student)])->assertRedirect();

    expect(StudentPromotion::query()->first()->to_semester_id)
        ->toBe($this->nextYearSemester->id);
});

test('a semester from another academic year is accepted', function () {
    $student = promoStudent();

    /*
     * Terms are standing structures here, so one created under a different
     * year is still a valid destination — the school picks the year and the
     * term independently and both are recorded as chosen.
     */
    promote(promoActor(UserRole::Admin), [decision($student)], null, $this->semester->id)
        ->assertRedirect();

    expect(StudentPromotion::query()->first()->to_semester_id)->toBe($this->semester->id);
});

test('a class from another academic year is accepted', function () {
    $student = promoStudent();
    $otherYearClass = promoClass($this->year, $this->grade6, '6-current');

    // Classes recur every year too, so this is a valid destination.
    promote(promoActor(UserRole::Admin), [decision($student, 'promoted', $otherYearClass->id)])
        ->assertRedirect();

    expect($student->fresh()->current_class_id)->toBe($otherYearClass->id)
        ->and(StudentPromotion::query()->first()->to_class_id)->toBe($otherYearClass->id);
});

test('a missing target semester is rejected when students are being moved', function () {
    $student = promoStudent();

    $this->actingAs(promoActor(UserRole::Admin))
        ->post(route('promotions.store'), [
            'to_academic_year_id' => $this->nextYear->id,
            'decisions' => [decision($student)],
        ])
        ->assertStatus(422);

    expect(StudentPromotion::query()->count())->toBe(0);
});

test('an inactive target semester is rejected', function () {
    $student = promoStudent();

    $inactive = Semester::query()->create([
        'academic_year_id' => $this->nextYear->id,
        'name' => ['en' => 'Retired Term'],
        'sequence' => 9,
        'start_date' => now()->addMonths(6)->toDateString(),
        'end_date' => now()->addMonths(10)->toDateString(),
        'is_active' => false,
    ]);

    promote(promoActor(UserRole::Admin), [decision($student)], null, $inactive->id)
        ->assertStatus(422);

    expect(StudentPromotion::query()->count())->toBe(0);
});

test('the guard still refuses a missing or inactive target semester', function () {
    $guard = app(PromotionGuardService::class);

    $inactive = Semester::query()->create([
        'academic_year_id' => $this->nextYear->id,
        'name' => ['en' => 'Retired Term'],
        'sequence' => 9,
        'start_date' => now()->addMonths(6)->toDateString(),
        'end_date' => now()->addMonths(10)->toDateString(),
        'is_active' => false,
    ]);

    // Existence and active state are still enforced; only the year link went.
    expect(fn () => $guard->assertTargetSemester(null))
        ->toThrow(PromotionOperationException::class);

    expect(fn () => $guard->assertTargetSemester($inactive->id))
        ->toThrow(PromotionOperationException::class);

    expect($guard->assertTargetSemester($this->nextYearSemester->id)->id)
        ->toBe($this->nextYearSemester->id);
});

test('a retained only batch needs no target semester', function () {
    $student = promoStudent();

    $this->actingAs(promoActor(UserRole::Admin))
        ->post(route('promotions.store'), [
            'decisions' => [decision($student, 'retained')],
        ])
        ->assertRedirect();

    $record = StudentPromotion::query()->first();

    expect($record->status)->toBe(PromotionStatus::Retained)
        ->and($record->to_semester_id)->toBeNull()
        ->and($record->to_academic_year_id)->toBeNull();
});

test('a graduated decision records no target semester', function () {
    $student = promoStudent();

    promote(promoActor(UserRole::Admin), [decision($student, 'graduated')])->assertRedirect();

    expect(StudentPromotion::query()->first()->to_semester_id)->toBeNull();
});

test('a bulk promotion moves every selected student', function () {
    $students = collect(range(1, 3))->map(fn (): StudentProfile => promoStudent());

    promote(
        promoActor(UserRole::Admin),
        $students->map(fn (StudentProfile $s): array => decision($s))->all(),
    )->assertRedirect();

    expect(StudentPromotion::query()->count())->toBe(3);

    $students->each(function (StudentProfile $student): void {
        expect($student->fresh()->current_class_id)->toBe($this->targetClass->id);
    });
});

test('the promoting user is recorded on the history row', function () {
    $student = promoStudent();
    $admin = promoActor(UserRole::Admin);

    promote($admin, [decision($student)])->assertRedirect();

    expect(StudentPromotion::query()->first()->promoted_by)->toBe($admin->id);
});

test('a retained student stays where they are', function () {
    $student = promoStudent();

    promote(promoActor(UserRole::Admin), [decision($student, 'retained')])->assertRedirect();

    $student->refresh();

    expect($student->academic_year_id)->toBe($this->year->id)
        ->and($student->current_class_id)->toBe($this->classA->id);

    $record = StudentPromotion::query()->first();

    expect($record->status)->toBe(PromotionStatus::Retained)
        ->and($record->to_academic_year_id)->toBeNull()
        ->and($record->to_class_id)->toBeNull();
});

test('a graduated student is flagged and leaves their class', function () {
    $student = promoStudent();

    promote(promoActor(UserRole::Admin), [decision($student, 'graduated')])->assertRedirect();

    $student->refresh();

    expect($student->is_graduated)->toBeTrue()
        ->and($student->current_class_id)->toBeNull()
        ->and(StudentPromotion::query()->first()->status)->toBe(PromotionStatus::Graduated);
});

test('a transferred student is deactivated and recorded', function () {
    $student = promoStudent();

    promote(promoActor(UserRole::Admin), [decision($student, 'transferred')])->assertRedirect();

    $student->refresh();

    expect($student->is_active)->toBeFalse()
        ->and(StudentPromotion::query()->first()->status)->toBe(PromotionStatus::Transferred);
});

/*
|--------------------------------------------------------------------------
| Rules
|--------------------------------------------------------------------------
*/

test('a duplicate decision for the same year is rejected', function () {
    $student = promoStudent();
    $admin = promoActor(UserRole::Admin);

    promote($admin, [decision($student)])->assertRedirect();

    // The student now sits in the next year, so a second attempt is caught
    // by the source-year rule before the duplicate rule is even reached.
    promote($admin, [decision($student)])->assertStatus(422);

    expect(StudentPromotion::query()->count())->toBe(1);
});

test('a retained student cannot be decided twice in the same year', function () {
    $student = promoStudent();
    $admin = promoActor(UserRole::Admin);

    promote($admin, [decision($student, 'retained')])->assertRedirect();
    promote($admin, [decision($student, 'retained')])->assertStatus(409);

    expect(StudentPromotion::query()->count())->toBe(1);
});

test('an already graduated student cannot be promoted', function () {
    $student = promoStudent();
    $student->update(['is_graduated' => true]);

    promote(promoActor(UserRole::Admin), [decision($student)])->assertStatus(422);

    expect(StudentPromotion::query()->count())->toBe(0);
});

test('a student from another academic year cannot be promoted', function () {
    $otherYear = AcademicYear::factory()->create(['is_current' => false, 'is_active' => true]);
    $otherClass = promoClass($otherYear, $this->grade5, '5B');
    $student = promoStudent($otherClass, $otherYear);

    promote(promoActor(UserRole::Admin), [decision($student)])->assertStatus(422);

    expect(StudentPromotion::query()->count())->toBe(0);
});

test('an inactive target year is rejected', function () {
    $student = promoStudent();
    $inactive = AcademicYear::factory()->create(['is_current' => false, 'is_active' => false]);

    // The year itself must still be active; only the term/class links went.
    promote(promoActor(UserRole::Admin), [decision($student)], $inactive->id)
        ->assertStatus(422);

    expect(StudentPromotion::query()->count())->toBe(0);
});

test('promoting into the current year is rejected', function () {
    $student = promoStudent();

    // The same-as-source rule is unchanged by the relaxed term/class linking.
    promote(promoActor(UserRole::Admin), [decision($student)], $this->year->id)
        ->assertStatus(422);

    expect(StudentPromotion::query()->count())->toBe(0);
});

test('the guard still refuses an inactive target class', function () {
    $student = promoStudent();

    $inactiveClass = promoClass($this->nextYear, $this->grade6, '6-retired');
    $inactiveClass->update(['is_active' => false]);

    $guard = app(PromotionGuardService::class);

    $decision = new PromotionDecisionDTO(
        studentId: $student->id,
        status: PromotionStatus::Promoted,
        targetClassId: $inactiveClass->id,
        remarks: null,
    );

    expect(fn () => $guard->assertTargetClass($decision, $student))
        ->toThrow(PromotionOperationException::class);
});

test('moving a student to a lower grade level is rejected', function () {
    $grade3 = GradeLevel::query()->create([
        'name' => ['en' => 'Grade 3'], 'display_order' => 3, 'is_active' => true,
    ]);
    $lower = promoClass($this->nextYear, $grade3, '3A');
    $student = promoStudent();

    promote(promoActor(UserRole::Admin), [decision($student, 'promoted', $lower->id)])
        ->assertStatus(422);

    expect(StudentPromotion::query()->count())->toBe(0);
});

test('a promotion without a target class is rejected', function () {
    $student = promoStudent();

    promote(promoActor(UserRole::Admin), [[
        'student_id' => $student->id,
        'status' => 'promoted',
        'target_class_id' => null,
    ]])->assertStatus(422);

    expect(StudentPromotion::query()->count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Capacity
|--------------------------------------------------------------------------
*/

test('a promotion that would exceed target capacity is rejected', function () {
    $small = promoClass($this->nextYear, $this->grade6, '6-small', 2);

    $students = collect(range(1, 3))->map(fn (): StudentProfile => promoStudent());

    promote(
        promoActor(UserRole::Admin),
        $students->map(fn (StudentProfile $s): array => decision($s, 'promoted', $small->id))->all(),
    )->assertStatus(422);

    // All-or-nothing: not even the first two are written.
    expect(StudentPromotion::query()->count())->toBe(0);
});

test('capacity counting ignores soft deleted students', function () {
    $small = promoClass($this->nextYear, $this->grade6, '6-small', 1);

    // Occupies the only seat, then withdraws.
    $withdrawn = promoStudent();
    $withdrawn->update(['current_class_id' => $small->id]);
    $withdrawn->delete();

    $student = promoStudent();

    promote(promoActor(UserRole::Admin), [decision($student, 'promoted', $small->id)])
        ->assertRedirect();

    expect($student->fresh()->current_class_id)->toBe($small->id);
});

/*
|--------------------------------------------------------------------------
| Atomicity
|--------------------------------------------------------------------------
*/

test('one invalid student rolls back the entire bulk promotion', function () {
    $valid = promoStudent();
    $graduated = promoStudent();
    $graduated->update(['is_graduated' => true]);

    promote(promoActor(UserRole::Admin), [
        decision($valid),
        decision($graduated),
    ])->assertStatus(422);

    // No history rows, and the valid student was never moved.
    expect(StudentPromotion::query()->count())->toBe(0)
        ->and($valid->fresh()->academic_year_id)->toBe($this->year->id)
        ->and($valid->fresh()->current_class_id)->toBe($this->classA->id);
});

test('an unknown student id aborts the whole batch', function () {
    $valid = promoStudent();

    promote(promoActor(UserRole::Admin), [
        decision($valid),
        ['student_id' => 999999, 'status' => 'promoted', 'target_class_id' => $this->targetClass->id],
    ])->assertSessionHasErrors('decisions.1.student_id');

    expect(StudentPromotion::query()->count())->toBe(0)
        ->and($valid->fresh()->academic_year_id)->toBe($this->year->id);
});

/*
|--------------------------------------------------------------------------
| Academic context
|--------------------------------------------------------------------------
*/

test('the source year is taken from the academic context not the request', function () {
    $student = promoStudent();

    /*
     * A crafted source year must be ignored: the student belongs to the
     * current year, so the promotion succeeds and records that year as the
     * source regardless of what was posted.
     */
    $this->actingAs(promoActor(UserRole::Admin))
        ->post(route('promotions.store'), [
            'to_academic_year_id' => $this->nextYear->id,
            'to_semester_id' => $this->nextYearSemester->id,
            'from_academic_year_id' => $this->nextYear->id,
            'academic_year_id' => $this->nextYear->id,
            'decisions' => [decision($student)],
        ])
        ->assertRedirect();

    expect(StudentPromotion::query()->first()->from_academic_year_id)->toBe($this->year->id);
});

test('the wizard reports the current academic year as the source', function () {
    $this->actingAs(promoActor(UserRole::Admin))
        ->get(route('promotions.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('context.academic_year_id', $this->year->id));
});

/*
|--------------------------------------------------------------------------
| Eligibility
|--------------------------------------------------------------------------
*/

test('students already decided are excluded from the eligible list', function () {
    $decided = promoStudent();
    $pending = promoStudent();
    $admin = promoActor(UserRole::Admin);

    promote($admin, [decision($decided, 'retained')])->assertRedirect();

    $this->actingAs($admin)
        ->get(route('promotions.create'))
        ->assertOk()
        ->assertInertia(function ($page) use ($decided, $pending) {
            $ids = collect($page->toArray()['props']['students'])->pluck('student_id');

            expect($ids)->toContain($pending->id)
                ->and($ids)->not->toContain($decided->id);
        });
});

test('the wizard shows an empty list when nobody is eligible', function () {
    $this->actingAs(promoActor(UserRole::Admin))
        ->get(route('promotions.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('students', []));
});

test('an inactive class is not offered in the wizard', function () {
    promoStudent();

    $inactiveClass = promoClass($this->nextYear, $this->grade6, '6-retired');
    $inactiveClass->update(['is_active' => false]);

    /*
     * Target classes are no longer gated on the target year, but they are
     * still limited to active ones.
     */
    $this->actingAs(promoActor(UserRole::Admin))
        ->get(route('promotions.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(
            collect($page->toArray()['props']['targetClasses'])->pluck('class_id'),
        )->not->toContain($inactiveClass->id));
});

/*
|--------------------------------------------------------------------------
| Revert
|--------------------------------------------------------------------------
*/

test('a promotion can be reverted and restores the previous state', function () {
    $student = promoStudent();
    $admin = promoActor(UserRole::Admin);

    promote($admin, [decision($student)])->assertRedirect();

    $promotion = StudentPromotion::query()->first();

    $this->actingAs($admin)
        ->delete(route('promotions.revert', $promotion))
        ->assertRedirect(route('promotions.index'));

    $student->refresh();

    expect($student->academic_year_id)->toBe($this->year->id)
        ->and($student->current_class_id)->toBe($this->classA->id)
        ->and(StudentPromotion::query()->count())->toBe(0);
});

test('reverting restores the previous state from the record without guessing a semester', function () {
    $student = promoStudent();
    $admin = promoActor(UserRole::Admin);

    promote($admin, [decision($student)])->assertRedirect();

    $promotion = StudentPromotion::query()->first();

    expect($promotion->to_semester_id)->toBe($this->nextYearSemester->id);

    $this->actingAs($admin)
        ->delete(route('promotions.revert', $promotion))
        ->assertRedirect();

    $student->refresh();

    /*
     * student_profiles stores no semester at all, so a revert restores year
     * and class from the promotion row and has nothing to infer. The target
     * semester lives only in the history, which the revert removes with it.
     */
    expect($student->academic_year_id)->toBe($this->year->id)
        ->and($student->current_class_id)->toBe($this->classA->id)
        ->and(StudentPromotion::query()->count())->toBe(0);
});

test('the wizard offers every active semester regardless of year', function () {
    promoStudent();

    $this->actingAs(promoActor(UserRole::Admin))
        ->get(route('promotions.create'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $ids = collect($page->toArray()['props']['targetSemesters'])->pluck('value');

            // Both years' terms are offered — they are standing structures.
            expect($ids)->toContain($this->nextYearSemester->id)
                ->and($ids)->toContain($this->semester->id);
        });
});

test('the wizard offers every active class regardless of year', function () {
    promoStudent();

    $this->actingAs(promoActor(UserRole::Admin))
        ->get(route('promotions.create'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $ids = collect($page->toArray()['props']['targetClasses'])->pluck('class_id');

            expect($ids)->toContain($this->targetClass->id)
                ->and($ids)->toContain($this->classA->id);
        });
});

test('an inactive semester is not offered in the wizard', function () {
    promoStudent();

    $inactive = Semester::query()->create([
        'academic_year_id' => $this->nextYear->id,
        'name' => ['en' => 'Retired Term'],
        'sequence' => 9,
        'start_date' => now()->addMonths(6)->toDateString(),
        'end_date' => now()->addMonths(10)->toDateString(),
        'is_active' => false,
    ]);

    $this->actingAs(promoActor(UserRole::Admin))
        ->get(route('promotions.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(
            collect($page->toArray()['props']['targetSemesters'])->pluck('value'),
        )->not->toContain($inactive->id));
});

test('reverting a graduation restores the previous graduated flag', function () {
    $student = promoStudent();
    $admin = promoActor(UserRole::Admin);

    promote($admin, [decision($student, 'graduated')])->assertRedirect();
    expect($student->fresh()->is_graduated)->toBeTrue();

    $this->actingAs($admin)
        ->delete(route('promotions.revert', StudentPromotion::query()->first()))
        ->assertRedirect();

    $student->refresh();

    expect($student->is_graduated)->toBeFalse()
        ->and($student->current_class_id)->toBe($this->classA->id);
});

test('reverting a transfer reactivates the student', function () {
    $student = promoStudent();
    $admin = promoActor(UserRole::Admin);

    promote($admin, [decision($student, 'transferred')])->assertRedirect();
    expect($student->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->delete(route('promotions.revert', StudentPromotion::query()->first()))
        ->assertRedirect();

    expect($student->fresh()->is_active)->toBeTrue();
});

test('an older promotion cannot be reverted once a newer one exists', function () {
    $student = promoStudent();
    $admin = promoActor(UserRole::Admin);

    // Year 1 -> year 2.
    promote($admin, [decision($student)])->assertRedirect();
    $first = StudentPromotion::query()->first();

    // Year 2 -> year 3, recorded directly so the context stays on year 1.
    $thirdYear = AcademicYear::factory()->create(['is_current' => false, 'is_active' => true]);
    $thirdClass = promoClass($thirdYear, $this->grade6, '6B');

    StudentPromotion::query()->create([
        'student_id' => $student->id,
        'from_academic_year_id' => $this->nextYear->id,
        'to_academic_year_id' => $thirdYear->id,
        'to_semester_id' => null,
        'from_class_id' => $this->targetClass->id,
        'to_class_id' => $thirdClass->id,
        'status' => PromotionStatus::Promoted,
        'was_graduated' => false,
        'promoted_by' => $admin->id,
        'promoted_at' => now()->addDay(),
    ]);

    $this->actingAs($admin)
        ->delete(route('promotions.revert', $first))
        ->assertStatus(409);

    expect(StudentPromotion::query()->whereKey($first->id)->exists())->toBeTrue();
});

test('a teacher cannot revert a promotion', function () {
    $student = promoStudent();

    promote(promoActor(UserRole::Admin), [decision($student)])->assertRedirect();

    $this->actingAs(promoActor(UserRole::Teacher))
        ->delete(route('promotions.revert', StudentPromotion::query()->first()))
        ->assertForbidden();

    expect(StudentPromotion::query()->count())->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

test('at least one decision is required', function () {
    $this->actingAs(promoActor(UserRole::Admin))
        ->post(route('promotions.store'), [
            'to_academic_year_id' => $this->nextYear->id,
            'decisions' => [],
        ])
        ->assertSessionHasErrors('decisions');
});

test('an unknown decision status is rejected', function () {
    $student = promoStudent();

    $this->actingAs(promoActor(UserRole::Admin))
        ->post(route('promotions.store'), [
            'to_academic_year_id' => $this->nextYear->id,
            'decisions' => [['student_id' => $student->id, 'status' => 'expelled']],
        ])
        ->assertSessionHasErrors('decisions.0.status');
});
