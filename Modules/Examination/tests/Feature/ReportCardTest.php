<?php

use App\Models\User;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamGrade;
use Modules\Examination\Models\ExamResult;
use Modules\Examination\Models\ExamSubject;
use Modules\Examination\Queries\ReportCardQuery;
use Modules\Examination\Services\GradeBandResolver;
use Modules\Examination\Services\ResultRankCalculator;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Models\GuardianStudent;
use Modules\SchoolClass\Models\ClassSubject;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Setting\Models\SchoolSetting;
use Modules\Student\Models\StudentProfile;
use Modules\Subject\Models\Subject;
use Modules\Teacher\Models\TeacherProfile;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    SchoolSetting::query()->create([
        'school_name' => 'Athr Test School',
        'school_code' => 'ATHR',
        'setup_completed' => true,
    ]);

    $this->year = AcademicYear::factory()->create(['is_current' => true, 'is_active' => true]);

    $this->semester = Semester::query()->create([
        'academic_year_id' => $this->year->id,
        'name' => ['en' => 'First Term', 'ar' => 'الفصل الأول'],
        'sequence' => 1,
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->addMonths(2)->toDateString(),
        'is_active' => true,
        'is_current' => true,
    ]);

    AcademicContextService::flush();

    $this->gradeLevel = GradeLevel::query()->create([
        'name' => ['en' => 'Grade 5'],
        'display_order' => 1,
        'is_active' => true,
    ]);

    $this->class = SchoolClass::query()->create([
        'grade_level_id' => $this->gradeLevel->id,
        'academic_year_id' => $this->year->id,
        'name' => '5A',
        'capacity' => 30,
        'is_active' => true,
    ]);

    $this->maths = Subject::query()->create([
        'name' => ['en' => 'Mathematics', 'ar' => 'الرياضيات'],
        'is_active' => true,
    ]);

    $this->science = Subject::query()->create([
        'name' => ['en' => 'Science', 'ar' => 'العلوم'],
        'is_active' => true,
    ]);

    /*
     * Bands for the grade level, with no subject_id, so they apply to every
     * subject and to the overall average.
     */
    foreach ([['A', 90, 100, true], ['B', 75, 89.99, true], ['C', 50, 74.99, true], ['F', 0, 49.99, false]] as [$name, $min, $max, $passing]) {
        ExamGrade::query()->create([
            'grade_level_id' => $this->gradeLevel->id,
            'subject_id' => null,
            'grade_name' => $name,
            'grade_point' => $passing ? 3.0 : 0.0,
            'min_percentage' => $min,
            'max_percentage' => $max,
            'is_passing' => $passing,
        ]);
    }
});

function rcStudent(string $name = 'Pupil'): StudentProfile
{
    $student = StudentProfile::factory()->create([
        'academic_year_id' => test()->year->id,
        'current_class_id' => test()->class->id,
    ]);

    $student->user->update(['name' => $name, 'role' => UserRole::Student, 'is_active' => true]);
    $student->user->assignRole('student');

    return $student->refresh();
}

function rcStaff(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user;
}

function rcGuardianFor(StudentProfile ...$children): User
{
    $user = User::factory()->create(['role' => UserRole::Guardian, 'is_active' => true]);
    $user->assignRole('guardian');

    $guardian = GuardianProfile::query()->create(['user_id' => $user->id, 'is_active' => true]);

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

function rcExam(bool $published, string $name = 'Midterm'): Exam
{
    return Exam::query()->create([
        'academic_year_id' => test()->year->id,
        'semester_id' => test()->semester->id,
        'name' => ['en' => $name],
        'exam_type' => 'midterm',
        'start_date' => now()->subWeek()->toDateString(),
        'end_date' => now()->subDays(5)->toDateString(),
        'total_marks' => 100,
        'class_id' => test()->class->id,
        'is_published' => $published,
    ]);
}

function rcExamSubject(Exam $exam, Subject $subject, float $total = 100, float $passing = 50): ExamSubject
{
    return ExamSubject::query()->create([
        'exam_id' => $exam->id,
        'subject_id' => $subject->id,
        'class_id' => test()->class->id,
        'total_marks' => $total,
        'passing_marks' => $passing,
        'exam_date' => now()->subWeek()->toDateString(),
    ]);
}

function rcResult(Exam $exam, StudentProfile $student, Subject $subject, ?float $marks, ?float $total = 100): ExamResult
{
    $percentage = $marks !== null ? round($marks / $total * 100, 2) : null;

    return ExamResult::query()->create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'class_id' => test()->class->id,
        'subject_id' => $subject->id,
        'obtained_marks' => $marks,
        'percentage' => $percentage,
        'is_passed' => $marks !== null ? $marks >= 50 : null,
    ]);
}

function rcQuery(): ReportCardQuery
{
    return app(ReportCardQuery::class);
}

/*
|--------------------------------------------------------------------------
| Report card content
|--------------------------------------------------------------------------
*/

test('the report card returns the correct subjects and results', function () {
    $student = rcStudent();
    $exam = rcExam(true);

    rcExamSubject($exam, $this->maths);
    rcExamSubject($exam, $this->science);
    rcResult($exam, $student, $this->maths, 80.0);
    rcResult($exam, $student, $this->science, 60.0);

    $card = rcQuery()->forStudent($student, $this->year->id, $this->semester->id);

    expect($card->subjects)->toHaveCount(2)
        ->and($card->totalObtained)->toBe(140.0)
        ->and($card->totalMax)->toBe(200.0)
        ->and($card->averagePercentage)->toBe(70.0);
});

test('the report card carries the student class year and term', function () {
    $student = rcStudent('Sara');

    $card = rcQuery()->forStudent($student, $this->year->id, $this->semester->id);

    expect($card->studentName)->toBe('Sara')
        ->and($card->classId)->toBe($this->class->id)
        ->and($card->className)->toBe('5A')
        ->and($card->academicYearId)->toBe($this->year->id)
        ->and($card->semesterId)->toBe($this->semester->id)
        ->and($card->semesterName)->toBe('First Term');
});

test('an empty report card reports no results rather than a zero', function () {
    $student = rcStudent();

    $card = rcQuery()->forStudent($student, $this->year->id, $this->semester->id);

    expect($card->subjects)->toBe([])
        ->and($card->totalObtained)->toBeNull()
        ->and($card->averagePercentage)->toBeNull()
        ->and($card->overallGrade)->toBeNull()
        ->and($card->toArray()['has_results'])->toBeFalse();
});

test('a subject with no marks entered is reported as pending', function () {
    $student = rcStudent();
    $exam = rcExam(true);

    rcExamSubject($exam, $this->maths);
    rcResult($exam, $student, $this->maths, null);

    $card = rcQuery()->forStudent($student, $this->year->id, $this->semester->id);

    expect($card->subjects)->toHaveCount(1)
        ->and($card->subjects[0]->obtainedMarks)->toBeNull()
        ->and($card->subjects[0]->isPassed)->toBeNull()
        ->and($card->totalObtained)->toBeNull();
});

test('results from another academic context are excluded', function () {
    $student = rcStudent();

    $otherYear = AcademicYear::factory()->create(['is_current' => false, 'is_active' => true]);
    $otherSemester = Semester::query()->create([
        'academic_year_id' => $otherYear->id,
        'name' => ['en' => 'Old Term'],
        'sequence' => 1,
        'start_date' => now()->subYears(2)->toDateString(),
        'end_date' => now()->subYear()->toDateString(),
        'is_active' => true,
    ]);

    $oldExam = Exam::query()->create([
        'academic_year_id' => $otherYear->id,
        'semester_id' => $otherSemester->id,
        'name' => ['en' => 'Last Year'],
        'exam_type' => 'final',
        'start_date' => now()->subYear()->toDateString(),
        'end_date' => now()->subYear()->toDateString(),
        'total_marks' => 100,
        'class_id' => $this->class->id,
        'is_published' => true,
    ]);

    rcExamSubject($oldExam, $this->maths);
    rcResult($oldExam, $student, $this->maths, 95.0);

    $card = rcQuery()->forStudent($student, $this->year->id, $this->semester->id);

    expect($card->subjects)->toBe([]);
});

/*
|--------------------------------------------------------------------------
| Grade bands
|--------------------------------------------------------------------------
*/

test('the overall grade comes from the configured bands', function () {
    $student = rcStudent();
    $exam = rcExam(true);

    rcExamSubject($exam, $this->maths);
    rcResult($exam, $student, $this->maths, 92.0);

    $card = rcQuery()->forStudent($student, $this->year->id, $this->semester->id);

    expect($card->averagePercentage)->toBe(92.0)
        ->and($card->overallGrade)->toBe('A')
        ->and($card->subjects[0]->grade)->toBe('A');
});

test('a subject specific band overrides the grade level band', function () {
    // Mathematics is marked on a stricter scale than the rest of the school.
    ExamGrade::query()->create([
        'grade_level_id' => $this->gradeLevel->id,
        'subject_id' => $this->maths->id,
        'grade_name' => 'MATH-A',
        'grade_point' => 4.0,
        'min_percentage' => 95,
        'max_percentage' => 100,
        'is_passing' => true,
    ]);

    $resolver = app(GradeBandResolver::class);

    expect($resolver->resolveName($this->gradeLevel->id, $this->maths->id, 97.0))->toBe('MATH-A')
        ->and($resolver->resolveName($this->gradeLevel->id, $this->science->id, 97.0))->toBe('A');
});

test('a percentage outside every band resolves to no grade', function () {
    $resolver = app(GradeBandResolver::class);

    // Bands stop at 100; nothing covers 150.
    expect($resolver->resolveName($this->gradeLevel->id, null, 150.0))->toBeNull()
        ->and($resolver->resolveName(null, null, 80.0))->toBeNull()
        ->and($resolver->resolveName($this->gradeLevel->id, null, null))->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Ranking
|--------------------------------------------------------------------------
*/

test('the class rank orders students by their overall average', function () {
    $top = rcStudent('Top');
    $middle = rcStudent('Middle');
    $bottom = rcStudent('Bottom');

    $exam = rcExam(true);
    rcExamSubject($exam, $this->maths);

    rcResult($exam, $top, $this->maths, 95.0);
    rcResult($exam, $middle, $this->maths, 80.0);
    rcResult($exam, $bottom, $this->maths, 60.0);

    $rank = fn (StudentProfile $s): ?int => rcQuery()
        ->forStudentWithRank($s, $this->year->id, $this->semester->id)->classRank;

    expect($rank($top))->toBe(1)
        ->and($rank($middle))->toBe(2)
        ->and($rank($bottom))->toBe(3);
});

test('tied averages share a rank and the next distinct score skips positions', function () {
    $first = rcStudent('First');
    $tiedA = rcStudent('TiedA');
    $tiedB = rcStudent('TiedB');
    $last = rcStudent('Last');

    $exam = rcExam(true);
    rcExamSubject($exam, $this->maths);

    rcResult($exam, $first, $this->maths, 95.0);
    rcResult($exam, $tiedA, $this->maths, 80.0);
    rcResult($exam, $tiedB, $this->maths, 80.0);
    rcResult($exam, $last, $this->maths, 60.0);

    $rank = fn (StudentProfile $s): ?int => rcQuery()
        ->forStudentWithRank($s, $this->year->id, $this->semester->id)->classRank;

    // Competition ranking: 1, 2, 2, 4 — never 1, 2, 3, 4.
    expect($rank($first))->toBe(1)
        ->and($rank($tiedA))->toBe(2)
        ->and($rank($tiedB))->toBe(2)
        ->and($rank($last))->toBe(4);
});

test('a student with no marks is counted in the class but left unranked', function () {
    $marked = rcStudent('Marked');
    $unmarked = rcStudent('Unmarked');

    $exam = rcExam(true);
    rcExamSubject($exam, $this->maths);
    rcResult($exam, $marked, $this->maths, 70.0);

    $markedCard = rcQuery()->forStudentWithRank($marked, $this->year->id, $this->semester->id);
    $unmarkedCard = rcQuery()->forStudentWithRank($unmarked, $this->year->id, $this->semester->id);

    expect($markedCard->classRank)->toBe(1)
        ->and($markedCard->classSize)->toBe(2)
        ->and($unmarkedCard->classRank)->toBeNull();
});

test('the rank calculator is deterministic for equal scores', function () {
    $calculator = app(ResultRankCalculator::class);

    $entries = collect([
        10 => ['student_id' => 3, 'average' => 80.0],
        11 => ['student_id' => 1, 'average' => 80.0],
        12 => ['student_id' => 2, 'average' => 90.0],
    ]);

    $ranks = $calculator->rank(
        $entries,
        fn (array $row): ?float => $row['average'],
        fn (array $row): int => $row['student_id'],
    );

    expect($ranks[12])->toBe(1)
        ->and($ranks[10])->toBe(2)
        ->and($ranks[11])->toBe(2);
});

/*
|--------------------------------------------------------------------------
| Published-only rule for families
|--------------------------------------------------------------------------
*/

test('a published only report card excludes unpublished results', function () {
    $student = rcStudent();

    $published = rcExam(true, 'Published');
    $draft = rcExam(false, 'Draft');

    rcExamSubject($published, $this->maths);
    rcExamSubject($draft, $this->science);
    rcResult($published, $student, $this->maths, 80.0);
    rcResult($draft, $student, $this->science, 95.0);

    $card = rcQuery()->publishedOnly()->forStudent($student, $this->year->id, $this->semester->id);

    expect($card->subjects)->toHaveCount(1)
        ->and($card->subjects[0]->subjectId)->toBe($this->maths->id)
        ->and($card->totalObtained)->toBe(80.0);
});

test('staff see unpublished results on the report card', function () {
    $student = rcStudent();
    $draft = rcExam(false, 'Draft');

    rcExamSubject($draft, $this->maths);
    rcResult($draft, $student, $this->maths, 95.0);

    $card = rcQuery()->forStudent($student, $this->year->id, $this->semester->id);

    expect($card->subjects)->toHaveCount(1)
        ->and($card->totalObtained)->toBe(95.0);
});

test('the student portal report card hides unpublished results', function () {
    $student = rcStudent();

    $published = rcExam(true, 'Published');
    $draft = rcExam(false, 'Draft');
    rcExamSubject($published, $this->maths);
    rcExamSubject($draft, $this->science);
    rcResult($published, $student, $this->maths, 80.0);
    rcResult($draft, $student, $this->science, 95.0);

    $this->actingAs($student->user)
        ->get(route('student.report-card'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $subjects = $page->toArray()['props']['card']['subjects'];

            expect($subjects)->toHaveCount(1)
                ->and($subjects[0]['subject_name'])->toBe('Mathematics');
        });
});

test('the guardian portal report card hides unpublished results', function () {
    $child = rcStudent();
    $guardian = rcGuardianFor($child);

    $published = rcExam(true, 'Published');
    $draft = rcExam(false, 'Draft');
    rcExamSubject($published, $this->maths);
    rcExamSubject($draft, $this->science);
    rcResult($published, $child, $this->maths, 80.0);
    rcResult($draft, $child, $this->science, 95.0);

    $this->actingAs($guardian)
        ->get(route('parent.report-card'))
        ->assertOk()
        ->assertInertia(function ($page) {
            expect($page->toArray()['props']['card']['subjects'])->toHaveCount(1);
        });
});

/*
|--------------------------------------------------------------------------
| Authorization
|--------------------------------------------------------------------------
*/

test('an admin can open the report card screen', function () {
    $this->actingAs(rcStaff(UserRole::Admin))
        ->get(route('report-cards.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Examination::ReportCards/Index'));
});

test('an admin can read any student report card', function () {
    $student = rcStudent();

    $this->actingAs(rcStaff(UserRole::Admin))
        ->get(route('report-cards.index', ['student_id' => $student->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('card.student_id', $student->id));
});

test('a student sees only their own report card', function () {
    $student = rcStudent('Mine');
    $other = rcStudent('Theirs');

    $this->actingAs($student->user)
        ->get(route('student.report-card'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('card.student_id', $student->id));

    expect($other->id)->not->toBe($student->id);
});

test('a student cannot reach another student report card through a query parameter', function () {
    $student = rcStudent('Mine');
    $other = rcStudent('Theirs');

    /*
     * The student portal takes no student id at all — the profile comes from
     * the signed-in user — so a crafted parameter is simply ignored.
     */
    $this->actingAs($student->user)
        ->get(route('student.report-card', ['student_id' => $other->id, 'child' => $other->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('card.student_id', $student->id));
});

test('a guardian cannot reach a report card for a child that is not theirs', function () {
    $child = rcStudent('Mine');
    $stranger = rcStudent('Stranger');
    $guardian = rcGuardianFor($child);

    /*
     * resolveChild() matches the requested id against the guardian's own
     * children and returns null for anything else, so an outside id yields an
     * empty card rather than the stranger's marks.
     */
    $this->actingAs($guardian)
        ->get(route('parent.report-card', ['child' => $stranger->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('card', null));
});

test('a guardian with no child selected sees their first child', function () {
    $child = rcStudent('Mine');
    $guardian = rcGuardianFor($child);

    $this->actingAs($guardian)
        ->get(route('parent.report-card'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('card.student_id', $child->id));
});

test('a student cannot open the admin report card screen', function () {
    $student = rcStudent();

    $this->actingAs($student->user)
        ->get(route('report-cards.index'))
        ->assertForbidden();
});

test('a guardian cannot open the admin report card screen', function () {
    $guardian = rcGuardianFor(rcStudent());

    $this->actingAs($guardian)
        ->get(route('report-cards.index'))
        ->assertForbidden();
});

test('a teacher can only read a report card for a student they teach', function () {
    $mine = rcStudent('Mine');

    $otherClass = SchoolClass::query()->create([
        'grade_level_id' => GradeLevel::query()->create([
            'name' => ['en' => 'Grade 6'],
            'display_order' => 2,
            'is_active' => true,
        ])->id,
        'academic_year_id' => $this->year->id,
        'name' => '6A',
        'capacity' => 30,
        'is_active' => true,
    ]);

    $theirs = StudentProfile::factory()->create([
        'academic_year_id' => $this->year->id,
        'current_class_id' => $otherClass->id,
    ]);
    $theirs->user->update(['role' => UserRole::Student, 'is_active' => true]);

    $teacherUser = rcStaff(UserRole::Teacher);
    TeacherProfile::query()->create(['user_id' => $teacherUser->id, 'is_active' => true]);

    // The teacher is assigned to this class + subject, and only this one.
    ClassSubject::query()->create([
        'class_id' => $this->class->id,
        'subject_id' => $this->maths->id,
        'teacher_id' => $teacherUser->id,
        'academic_year_id' => $this->year->id,
        'semester_id' => $this->semester->id,
        'weekly_hours' => 4,
        'is_mandatory' => true,
    ]);

    $this->actingAs($teacherUser)
        ->get(route('report-cards.index', ['student_id' => $mine->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('card.student_id', $mine->id));

    $this->actingAs($teacherUser)
        ->get(route('report-cards.index', ['student_id' => $theirs->id]))
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Academic context
|--------------------------------------------------------------------------
*/

test('the admin screen reports the current academic context', function () {
    $this->actingAs(rcStaff(UserRole::Admin))
        ->get(route('report-cards.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('context.academic_year_id', $this->year->id)
            ->where('context.semester_id', $this->semester->id));
});

test('the academic context is not taken from the query string', function () {
    $student = rcStudent();
    $exam = rcExam(true);
    rcExamSubject($exam, $this->maths);
    rcResult($exam, $student, $this->maths, 80.0);

    $otherYear = AcademicYear::factory()->create(['is_current' => false, 'is_active' => true]);

    /*
     * Passing another year must not change what is read: the context comes
     * from AcademicContextService, so the card still shows the current year.
     */
    $this->actingAs(rcStaff(UserRole::Admin))
        ->get(route('report-cards.index', [
            'student_id' => $student->id,
            'academic_year_id' => $otherYear->id,
            'semester_id' => 99999,
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('card.academic_year_id', $this->year->id)
            ->where('card.semester_id', $this->semester->id)
            ->where('context.academic_year_id', $this->year->id));
});
