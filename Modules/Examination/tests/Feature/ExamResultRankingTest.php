<?php

use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Examination\Actions\EnterExamResultsAction;
use Modules\Examination\DTOs\EnterExamResultsDTO;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamGrade;
use Modules\Examination\Models\ExamResult;
use Modules\Examination\Models\ExamSubject;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Setting\Models\SchoolSetting;
use Modules\Student\Models\StudentProfile;
use Modules\Subject\Models\Subject;

/**
 * Guards the per-exam ranking stored on exam_results.rank.
 *
 * The original implementation sorted by percentage and wrote index + 1, so two
 * students on the same mark received different ranks decided by row order.
 * Ranking is now competition style and deterministic.
 */
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
        'name' => ['en' => 'First Term'],
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
        'name' => '5A',
        'capacity' => 30,
        'is_active' => true,
    ]);

    $this->subject = Subject::query()->create([
        'name' => ['en' => 'Mathematics'],
        'is_active' => true,
    ]);

    $this->exam = Exam::query()->create([
        'academic_year_id' => $this->year->id,
        'semester_id' => $this->semester->id,
        'name' => ['en' => 'Midterm'],
        'exam_type' => 'midterm',
        'start_date' => now()->subWeek()->toDateString(),
        'end_date' => now()->subDays(5)->toDateString(),
        'total_marks' => 100,
        'class_id' => $this->class->id,
        'is_published' => false,
    ]);

    ExamSubject::query()->create([
        'exam_id' => $this->exam->id,
        'subject_id' => $this->subject->id,
        'class_id' => $this->class->id,
        'total_marks' => 100,
        'passing_marks' => 50,
        'exam_date' => now()->subWeek()->toDateString(),
    ]);
});

function rankStudent(): StudentProfile
{
    $student = StudentProfile::factory()->create([
        'academic_year_id' => test()->year->id,
        'current_class_id' => test()->class->id,
    ]);

    $student->user->update(['role' => UserRole::Student, 'is_active' => true]);

    return $student->refresh();
}

/**
 * @param  array<int, array{student_id: int, obtained_marks: float|null}>  $rows
 */
function enterResults(array $rows): void
{
    app(EnterExamResultsAction::class)->execute(new EnterExamResultsDTO(
        examId: test()->exam->id,
        subjectId: test()->subject->id,
        classId: test()->class->id,
        results: array_map(static fn (array $row): array => [
            'student_id' => $row['student_id'],
            'obtained_marks' => $row['obtained_marks'],
            'remarks' => null,
        ], $rows),
    ));
}

function rankOf(StudentProfile $student): ?int
{
    return ExamResult::query()
        ->where('exam_id', test()->exam->id)
        ->where('subject_id', test()->subject->id)
        ->where('student_id', $student->id)
        ->value('rank');
}

test('distinct marks receive sequential ranks', function () {
    $top = rankStudent();
    $middle = rankStudent();
    $bottom = rankStudent();

    enterResults([
        ['student_id' => $bottom->id, 'obtained_marks' => 55.0],
        ['student_id' => $top->id, 'obtained_marks' => 91.0],
        ['student_id' => $middle->id, 'obtained_marks' => 72.0],
    ]);

    expect(rankOf($top))->toBe(1)
        ->and(rankOf($middle))->toBe(2)
        ->and(rankOf($bottom))->toBe(3);
});

test('tied marks share a rank and the next mark skips a position', function () {
    $first = rankStudent();
    $tiedA = rankStudent();
    $tiedB = rankStudent();
    $last = rankStudent();

    enterResults([
        ['student_id' => $first->id, 'obtained_marks' => 95.0],
        ['student_id' => $tiedA->id, 'obtained_marks' => 80.0],
        ['student_id' => $tiedB->id, 'obtained_marks' => 80.0],
        ['student_id' => $last->id, 'obtained_marks' => 60.0],
    ]);

    // Competition ranking: 1, 2, 2, 4.
    expect(rankOf($first))->toBe(1)
        ->and(rankOf($tiedA))->toBe(2)
        ->and(rankOf($tiedB))->toBe(2)
        ->and(rankOf($last))->toBe(4);
});

test('ranking is identical whatever order the marks arrive in', function () {
    $a = rankStudent();
    $b = rankStudent();

    enterResults([
        ['student_id' => $a->id, 'obtained_marks' => 70.0],
        ['student_id' => $b->id, 'obtained_marks' => 70.0],
    ]);

    $firstPass = [rankOf($a), rankOf($b)];

    // Re-enter the same marks with the rows reversed.
    enterResults([
        ['student_id' => $b->id, 'obtained_marks' => 70.0],
        ['student_id' => $a->id, 'obtained_marks' => 70.0],
    ]);

    expect([rankOf($a), rankOf($b)])->toBe($firstPass)
        ->and($firstPass)->toBe([1, 1]);
});

test('an unmarked student is left unranked', function () {
    $marked = rankStudent();
    $unmarked = rankStudent();

    enterResults([
        ['student_id' => $marked->id, 'obtained_marks' => 70.0],
        ['student_id' => $unmarked->id, 'obtained_marks' => null],
    ]);

    expect(rankOf($marked))->toBe(1)
        ->and(rankOf($unmarked))->toBeNull();
});

test('the grade is taken from the configured bands when results are entered', function () {
    foreach ([['A', 90, 100, true], ['C', 50, 89.99, true], ['F', 0, 49.99, false]] as [$name, $min, $max, $passing]) {
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

    $high = rankStudent();
    $low = rankStudent();

    enterResults([
        ['student_id' => $high->id, 'obtained_marks' => 95.0],
        ['student_id' => $low->id, 'obtained_marks' => 30.0],
    ]);

    $gradeOf = fn (StudentProfile $s): ?string => ExamResult::query()
        ->where('exam_id', $this->exam->id)
        ->where('student_id', $s->id)
        ->value('grade');

    expect($gradeOf($high))->toBe('A')
        ->and($gradeOf($low))->toBe('F');
});

test('no grade is assigned when the school has configured no bands', function () {
    $student = rankStudent();

    enterResults([['student_id' => $student->id, 'obtained_marks' => 95.0]]);

    expect(ExamResult::query()->where('student_id', $student->id)->value('grade'))->toBeNull();
});
