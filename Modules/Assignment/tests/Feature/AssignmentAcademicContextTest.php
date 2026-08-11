<?php

use App\Models\User;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;
use Modules\Assignment\Models\Assignment;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\SchoolClass\Models\ClassSubject;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Subject\Models\Subject;
use Modules\Teacher\Models\TeacherProfile;

function assignmentClass(AcademicYear $year, string $name = 'Grade 1 - A'): SchoolClass
{
    $grade = GradeLevel::query()->firstOr(fn () => GradeLevel::create([
        'name' => ['en' => 'Grade 1'],
        'is_active' => true,
    ]));

    return SchoolClass::create([
        'grade_level_id' => $grade->id,
        'academic_year_id' => $year->id,
        'name' => $name,
        'capacity' => 30,
        'is_active' => true,
    ]);
}

function assignmentTeacher(string $name = 'Assigned Teacher'): User
{
    $user = User::factory()->create(['role' => UserRole::Teacher, 'name' => $name, 'is_active' => true]);
    TeacherProfile::create(['user_id' => $user->id, 'is_active' => true]);

    return $user;
}

function assignmentPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Homework 1',
        'description' => 'Do the exercises.',
        'max_score' => 100,
        'deadline' => now()->addWeek()->toDateTimeString(),
        'assignment_type' => 'homework',
        'is_group_work' => false,
        'is_published' => true,
    ], $overrides);
}

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $this->admin->assignRole('admin');

    $this->year = AcademicYear::factory()->current()->create();

    $this->semester = Semester::create([
        'academic_year_id' => $this->year->id,
        'name' => ['en' => 'First Term'],
        'sequence' => 1,
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => now()->addMonths(3)->toDateString(),
        'is_current' => true,
        'is_active' => true,
    ]);

    AcademicContextService::flush();

    $this->class = assignmentClass($this->year);
    $this->otherClass = assignmentClass($this->year, 'Grade 1 - B');

    $this->math = Subject::create(['name' => ['en' => 'Mathematics'], 'is_active' => true]);
    $this->art = Subject::create(['name' => ['en' => 'Art'], 'is_active' => true]);

    $this->teacher = assignmentTeacher();
    $this->otherTeacher = assignmentTeacher('Other Teacher');

    // The real teaching assignment: math in this class, taught by $this->teacher.
    ClassSubject::create([
        'class_id' => $this->class->id,
        'subject_id' => $this->math->id,
        'teacher_id' => $this->teacher->id,
        'academic_year_id' => $this->year->id,
    ]);
});

it('stamps the current academic year and semester onto a new assignment', function (): void {
    $this->actingAs($this->admin)
        ->post(route('assignments.store'), assignmentPayload([
            'class_id' => $this->class->id,
            'subject_id' => $this->math->id,
            'teacher_id' => $this->teacher->id,
        ]))
        ->assertSessionHasNoErrors();

    $assignment = Assignment::query()->latest('id')->first();

    expect($assignment)->not->toBeNull()
        ->and($assignment->academic_year_id)->toBe($this->year->id)
        ->and($assignment->semester_id)->toBe($this->semester->id);
});

it('ignores an academic year and semester supplied by the client', function (): void {
    $otherYear = AcademicYear::factory()->create(['is_current' => false]);

    $otherSemester = Semester::create([
        'academic_year_id' => $otherYear->id,
        'name' => ['en' => 'Foreign Term'],
        'sequence' => 1,
        'start_date' => now()->subYear()->toDateString(),
        'end_date' => now()->subMonths(9)->toDateString(),
        'is_current' => false,
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->post(route('assignments.store'), assignmentPayload([
            'class_id' => $this->class->id,
            'subject_id' => $this->math->id,
            'teacher_id' => $this->teacher->id,
            'academic_year_id' => $otherYear->id,
            'semester_id' => $otherSemester->id,
        ]))
        ->assertSessionHasNoErrors();

    $assignment = Assignment::query()->latest('id')->first();

    expect($assignment->academic_year_id)->toBe($this->year->id)
        ->and($assignment->semester_id)->toBe($this->semester->id);
});

it('rejects a subject the selected class does not study', function (): void {
    $this->actingAs($this->admin)
        ->post(route('assignments.store'), assignmentPayload([
            'class_id' => $this->class->id,
            'subject_id' => $this->art->id,
            'teacher_id' => $this->teacher->id,
        ]))
        ->assertSessionHasErrors('subject_id');

    expect(Assignment::query()->count())->toBe(0);
});

it('rejects a teacher who is not the one assigned to that class subject', function (): void {
    $this->actingAs($this->admin)
        ->post(route('assignments.store'), assignmentPayload([
            'class_id' => $this->class->id,
            'subject_id' => $this->math->id,
            'teacher_id' => $this->otherTeacher->id,
        ]))
        ->assertSessionHasErrors('teacher_id');

    expect(Assignment::query()->count())->toBe(0);
});

it('rejects a subject taught in a different class', function (): void {
    $this->actingAs($this->admin)
        ->post(route('assignments.store'), assignmentPayload([
            'class_id' => $this->otherClass->id,
            'subject_id' => $this->math->id,
            'teacher_id' => $this->teacher->id,
        ]))
        ->assertSessionHasErrors('subject_id');

    expect(Assignment::query()->count())->toBe(0);
});

it('accepts a valid class, subject and teacher combination', function (): void {
    $this->actingAs($this->admin)
        ->post(route('assignments.store'), assignmentPayload([
            'class_id' => $this->class->id,
            'subject_id' => $this->math->id,
            'teacher_id' => $this->teacher->id,
        ]))
        ->assertSessionHasNoErrors();

    expect(Assignment::query()->count())->toBe(1);
});

it('sends the current semester and real teaching assignments to the create form', function (): void {
    $this->actingAs($this->admin)
        ->get(route('assignments.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('currentSemester.id', $this->semester->id)
            ->has('classSubjects', 1)
            ->where('classSubjects.0.class_id', $this->class->id)
            ->where('classSubjects.0.subject_id', $this->math->id)
            ->where('classSubjects.0.teacher_id', $this->teacher->id)
        );
});

it('keeps the original academic context when an assignment is edited', function (): void {
    $this->actingAs($this->admin)
        ->post(route('assignments.store'), assignmentPayload([
            'class_id' => $this->class->id,
            'subject_id' => $this->math->id,
            'teacher_id' => $this->teacher->id,
        ]))
        ->assertSessionHasNoErrors();

    $assignment = Assignment::query()->latest('id')->first();
    $originalYear = $assignment->academic_year_id;
    $originalSemester = $assignment->semester_id;

    $otherYear = AcademicYear::factory()->create(['is_current' => false]);

    $this->actingAs($this->admin)
        ->put(route('assignments.update', $assignment), assignmentPayload([
            'class_id' => $this->class->id,
            'subject_id' => $this->math->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Renamed',
            'academic_year_id' => $otherYear->id,
            'semester_id' => 9999,
        ]))
        ->assertSessionHasNoErrors();

    $assignment->refresh();

    expect($assignment->academic_year_id)->toBe($originalYear)
        ->and($assignment->semester_id)->toBe($originalSemester);
});

it('rejects an invalid combination on update too', function (): void {
    $this->actingAs($this->admin)
        ->post(route('assignments.store'), assignmentPayload([
            'class_id' => $this->class->id,
            'subject_id' => $this->math->id,
            'teacher_id' => $this->teacher->id,
        ]))
        ->assertSessionHasNoErrors();

    $assignment = Assignment::query()->latest('id')->first();

    $this->actingAs($this->admin)
        ->put(route('assignments.update', $assignment), assignmentPayload([
            'class_id' => $this->class->id,
            'subject_id' => $this->math->id,
            'teacher_id' => $this->otherTeacher->id,
        ]))
        ->assertSessionHasErrors('teacher_id');
});
