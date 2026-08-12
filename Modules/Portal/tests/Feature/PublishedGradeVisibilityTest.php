<?php

use App\Models\User;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamResult;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Models\GuardianStudent;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Setting\Models\SchoolSetting;
use Modules\Student\Models\StudentProfile;
use Modules\Subject\Models\Subject;

/**
 * Guards the rule that exam marks reach a family only once the school has
 * published the exam.
 *
 * The student and guardian grade pages previously queried ExamResult with the
 * ownership scope alone, so a result attached to an unpublished exam — marks
 * still being entered and reviewed — was visible as soon as a teacher saved
 * it. ExamResultQuery::visibleToFamily() now applies both rules together.
 */
beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    SchoolSetting::query()->create([
        'school_name' => 'Athr Test School',
        'school_code' => 'ATHR',
        'setup_completed' => true,
    ]);

    $this->academicYear = AcademicYear::factory()->create([
        'is_current' => true,
        'is_active' => true,
    ]);

    $this->subject = Subject::query()->create([
        'name' => ['en' => 'Mathematics', 'ar' => 'الرياضيات'],
        'is_active' => true,
    ]);

    $this->schoolClass = gradeClass($this->academicYear);
});

/**
 * classes is unique on (academic_year_id, grade_level_id), so every class
 * needs its own grade level within the year.
 */
function gradeClass(AcademicYear $year): SchoolClass
{
    $gradeLevel = GradeLevel::query()->create([
        'name' => ['en' => 'Grade '.fake()->unique()->numberBetween(1, 9999)],
        'display_order' => 1,
        'is_active' => true,
    ]);

    return SchoolClass::query()->create([
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Class '.$gradeLevel->id,
        'capacity' => 30,
        'is_active' => true,
    ]);
}

function gradeStudent(AcademicYear $year, ?SchoolClass $class = null): StudentProfile
{
    $student = StudentProfile::factory()->create([
        'academic_year_id' => $year->id,
        'current_class_id' => $class?->id,
    ]);

    $student->user->update(['role' => UserRole::Student, 'is_active' => true]);
    $student->user->assignRole('student');

    return $student->refresh();
}

function gradeGuardianFor(StudentProfile ...$children): User
{
    $user = User::factory()->create(['role' => UserRole::Guardian, 'is_active' => true]);
    $user->assignRole('guardian');

    $guardian = GuardianProfile::query()->create([
        'user_id' => $user->id,
        'is_active' => true,
    ]);

    foreach ($children as $child) {
        GuardianStudent::query()->create([
            'parent_id' => $guardian->id,
            'student_id' => $child->id,
            'relationship' => 'father',
            'is_primary_contact' => true,
        ]);
    }

    return $user;
}

function gradeExam(AcademicYear $year, bool $isPublished): Exam
{
    return Exam::query()->create([
        'academic_year_id' => $year->id,
        'name' => ['en' => $isPublished ? 'Published Exam' : 'Draft Exam'],
        'exam_type' => 'midterm',
        'start_date' => now()->subWeek()->toDateString(),
        'end_date' => now()->subDays(5)->toDateString(),
        'total_marks' => 100,
        'is_published' => $isPublished,
    ]);
}

function gradeResult(
    Exam $exam,
    StudentProfile $student,
    Subject $subject,
    float $marks,
    ?SchoolClass $class = null,
): ExamResult {
    return ExamResult::query()->create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'class_id' => $class?->id ?? $student->current_class_id,
        'subject_id' => $subject->id,
        'obtained_marks' => $marks,
        'percentage' => $marks,
        'is_passed' => $marks >= 50,
    ]);
}

/**
 * @return array<int, int>
 */
function gradeResultIds(array $props): array
{
    return collect($props['results']['data'])->pluck('id')->all();
}

/*
|--------------------------------------------------------------------------
| Student — published only
|--------------------------------------------------------------------------
*/

test('a student cannot see a result from an unpublished exam', function () {
    $student = gradeStudent($this->academicYear, $this->schoolClass);
    $hidden = gradeResult(gradeExam($this->academicYear, false), $student, $this->subject, 91);

    $this->actingAs($student->user)
        ->get(route('student.grades'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(gradeResultIds($page->toArray()['props']))
            ->not->toContain($hidden->id));
});

test('a student can see a result from a published exam', function () {
    $student = gradeStudent($this->academicYear, $this->schoolClass);
    $visible = gradeResult(gradeExam($this->academicYear, true), $student, $this->subject, 88);

    $this->actingAs($student->user)
        ->get(route('student.grades'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(gradeResultIds($page->toArray()['props']))
            ->toContain($visible->id));
});

test('a student sees published results and no unpublished ones alongside them', function () {
    $student = gradeStudent($this->academicYear, $this->schoolClass);

    $visible = gradeResult(gradeExam($this->academicYear, true), $student, $this->subject, 88);
    $hidden = gradeResult(gradeExam($this->academicYear, false), $student, $this->subject, 91);

    $this->actingAs($student->user)
        ->get(route('student.grades'))
        ->assertOk()
        ->assertInertia(function ($page) use ($visible, $hidden) {
            $ids = gradeResultIds($page->toArray()['props']);

            expect($ids)->toContain($visible->id)
                ->and($ids)->not->toContain($hidden->id);
        });
});

test('a student cannot see another student\'s published results', function () {
    $student = gradeStudent($this->academicYear, $this->schoolClass);
    $other = gradeStudent($this->academicYear, $this->schoolClass);

    $exam = gradeExam($this->academicYear, true);
    $othersResult = gradeResult($exam, $other, $this->subject, 77);

    $this->actingAs($student->user)
        ->get(route('student.grades'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(gradeResultIds($page->toArray()['props']))
            ->not->toContain($othersResult->id));
});

test('the student dashboard result count excludes unpublished exams', function () {
    $student = gradeStudent($this->academicYear, $this->schoolClass);

    gradeResult(gradeExam($this->academicYear, true), $student, $this->subject, 88);
    gradeResult(gradeExam($this->academicYear, false), $student, $this->subject, 91);

    // Two rows exist; only the published one may be counted.
    expect(ExamResult::query()->where('student_id', $student->id)->count())->toBe(2);

    $this->actingAs($student->user)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $widget = collect($page->toArray()['props']['widgets'])
                ->firstWhere('id', 'recent_results');

            expect($widget)->not->toBeNull()
                ->and($widget['value'])->toBe(1);
        });
});

/*
|--------------------------------------------------------------------------
| Guardian — published only, own children only
|--------------------------------------------------------------------------
*/

test('a guardian cannot see a result from an unpublished exam', function () {
    $child = gradeStudent($this->academicYear, $this->schoolClass);
    $guardian = gradeGuardianFor($child);

    $hidden = gradeResult(gradeExam($this->academicYear, false), $child, $this->subject, 91);

    $this->actingAs($guardian)
        ->get(route('parent.grades'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(gradeResultIds($page->toArray()['props']))
            ->not->toContain($hidden->id));
});

test('a guardian can see a result from a published exam', function () {
    $child = gradeStudent($this->academicYear, $this->schoolClass);
    $guardian = gradeGuardianFor($child);

    $visible = gradeResult(gradeExam($this->academicYear, true), $child, $this->subject, 88);

    $this->actingAs($guardian)
        ->get(route('parent.grades'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(gradeResultIds($page->toArray()['props']))
            ->toContain($visible->id));
});

test('a guardian cannot see the results of a student who is not their child', function () {
    $child = gradeStudent($this->academicYear, $this->schoolClass);
    $stranger = gradeStudent($this->academicYear, $this->schoolClass);
    $guardian = gradeGuardianFor($child);

    $exam = gradeExam($this->academicYear, true);
    $strangersResult = gradeResult($exam, $stranger, $this->subject, 77);

    $this->actingAs($guardian)
        ->get(route('parent.grades'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(gradeResultIds($page->toArray()['props']))
            ->not->toContain($strangersResult->id));
});

test('a guardian cannot reach a stranger\'s results by passing their student id', function () {
    $child = gradeStudent($this->academicYear, $this->schoolClass);
    $stranger = gradeStudent($this->academicYear, $this->schoolClass);
    $guardian = gradeGuardianFor($child);

    $exam = gradeExam($this->academicYear, true);
    $strangersResult = gradeResult($exam, $stranger, $this->subject, 77);
    $ownResult = gradeResult($exam, $child, $this->subject, 88);

    // The id is resolved against the guardian's own children, so an outside
    // id falls back to their first child rather than leaking the stranger.
    $this->actingAs($guardian)
        ->get(route('parent.grades', ['student_id' => $stranger->id]))
        ->assertOk()
        ->assertInertia(function ($page) use ($strangersResult, $ownResult) {
            $ids = gradeResultIds($page->toArray()['props']);

            expect($ids)->not->toContain($strangersResult->id)
                ->and($ids)->toContain($ownResult->id);
        });
});

/*
|--------------------------------------------------------------------------
| Staff behaviour is unchanged
|--------------------------------------------------------------------------
*/

test('an admin still sees unpublished results in the examination screens', function () {
    $student = gradeStudent($this->academicYear, $this->schoolClass);
    $exam = gradeExam($this->academicYear, false);
    gradeResult($exam, $student, $this->subject, 91);

    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $admin->assignRole('admin');

    /*
     * The admin path reads through ExamQuery, which is untouched by this fix —
     * entering and reviewing marks before publishing is the whole workflow.
     */
    $this->actingAs($admin)
        ->get(route('exams.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(collect($page->toArray()['props']['exams']['data'])->pluck('id'))
            ->toContain($exam->id));
});
