<?php

use App\Models\User;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Services\AcademicYearService;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamResult;
use Modules\Portal\Services\PortalScopeService;
use Modules\SchoolClass\Models\ClassSubject;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;
use Modules\Subject\Models\Subject;
use Modules\Teacher\Models\TeacherProfile;

function teacherPortalClass(AcademicYear $year): SchoolClass
{
    return SchoolClass::create([
        'name' => fake()->unique()->bothify('Class-##'),
        'academic_year_id' => $year->id,
        'grade_level_id' => GradeLevel::create([
            'name' => fake()->unique()->bothify('Grade-##'),
            'level' => fake()->unique()->numberBetween(1, 900),
            'is_active' => true,
        ])->id,
        'capacity' => 30,
        'is_active' => true,
    ]);
}

function teacherPortalSubject(): Subject
{
    return Subject::create([
        'name' => fake()->unique()->words(2, true),
        'is_active' => true,
    ]);
}

function teacherPortalUser(): User
{
    $user = User::factory()->create(['role' => UserRole::Teacher, 'is_active' => true]);
    $user->assignRole('teacher');

    TeacherProfile::create([
        'user_id' => $user->id,
        'employee_id' => fake()->unique()->bothify('EMP-####'),
        'joining_date' => now()->toDateString(),
        'is_active' => true,
    ]);

    return $user;
}

function teacherPortalScope(User $user): PortalScopeService
{
    auth()->login($user);

    $scope = app(PortalScopeService::class);
    $scope->flush();

    return $scope;
}

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->year = AcademicYear::factory()->current()->create();
    AcademicYearService::flush();

    $this->teacher = teacherPortalUser();
    $this->otherTeacher = teacherPortalUser();

    $this->math = teacherPortalSubject();
    $this->arabic = teacherPortalSubject();

    $this->myClass = teacherPortalClass($this->year);
    $this->otherClass = teacherPortalClass($this->year);

    ClassSubject::create([
        'class_id' => $this->myClass->id,
        'subject_id' => $this->math->id,
        'teacher_id' => $this->teacher->id,
        'academic_year_id' => $this->year->id,
    ]);

    ClassSubject::create([
        'class_id' => $this->myClass->id,
        'subject_id' => $this->arabic->id,
        'teacher_id' => $this->otherTeacher->id,
        'academic_year_id' => $this->year->id,
    ]);
});

it('scopes a teacher to classes reached through class_subjects', function (): void {
    $scope = teacherPortalScope($this->teacher);

    expect($scope->accessibleClassIds())->toBe([$this->myClass->id])
        ->and($scope->canAccessClass($this->otherClass->id))->toBeFalse();
});

it('scopes a teacher to only the subjects they teach', function (): void {
    $scope = teacherPortalScope($this->teacher);

    expect($scope->canAccessSubject($this->math->id))->toBeTrue()
        ->and($scope->canAccessSubject($this->arabic->id))->toBeFalse();
});

it('requires the class and subject pair to belong to the teacher', function (): void {
    $scope = teacherPortalScope($this->teacher);

    expect($scope->canAccessClassSubject($this->myClass->id, $this->math->id))->toBeTrue()
        ->and($scope->canAccessClassSubject($this->myClass->id, $this->arabic->id))->toBeFalse()
        ->and($scope->canAccessClassSubject($this->otherClass->id, $this->math->id))->toBeFalse();
});

it('leaves staff unrestricted by subject', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $admin->assignRole('admin');

    $scope = teacherPortalScope($admin);

    expect($scope->accessibleSubjectIds())->toBeNull()
        ->and($scope->canAccessSubject($this->arabic->id))->toBeTrue();
});

it('forbids a teacher from editing a result for a subject they do not teach', function (): void {
    $student = StudentProfile::factory()->create([
        'current_class_id' => $this->myClass->id,
        'academic_year_id' => $this->year->id,
        'is_active' => true,
    ]);

    $exam = Exam::create([
        'name' => 'Midterm',
        'academic_year_id' => $this->year->id,
        'exam_type' => 'midterm',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
    ]);

    $arabicResult = ExamResult::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'class_id' => $this->myClass->id,
        'subject_id' => $this->arabic->id,
    ]);

    $mathResult = ExamResult::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'class_id' => $this->myClass->id,
        'subject_id' => $this->math->id,
    ]);

    teacherPortalScope($this->teacher);

    expect($this->teacher->can('update', $arabicResult))->toBeFalse()
        ->and($this->teacher->can('update', $mathResult))->toBeTrue();
});

it('lets a teacher list exams without a fatal error', function (): void {
    teacherPortalScope($this->teacher);

    expect($this->teacher->can('viewAny', Exam::class))->toBeTrue();
});

it('rejects attendance posted for a class the teacher does not teach', function (): void {
    $outsider = StudentProfile::factory()->create([
        'current_class_id' => $this->otherClass->id,
        'academic_year_id' => $this->year->id,
        'is_active' => true,
    ]);

    $this->actingAs($this->teacher)
        ->post(route('teacher.attendance.store'), [
            'class_id' => $this->otherClass->id,
            'date' => now()->toDateString(),
            'session' => 1,
            'students' => [['student_id' => $outsider->id, 'status' => 'present']],
        ])
        ->assertForbidden();
});

it('rejects attendance posted for a subject the teacher does not teach', function (): void {
    $student = StudentProfile::factory()->create([
        'current_class_id' => $this->myClass->id,
        'academic_year_id' => $this->year->id,
        'is_active' => true,
    ]);

    $this->actingAs($this->teacher)
        ->post(route('teacher.attendance.store'), [
            'class_id' => $this->myClass->id,
            'subject_id' => $this->arabic->id,
            'date' => now()->toDateString(),
            'session' => 1,
            'students' => [['student_id' => $student->id, 'status' => 'present']],
        ])
        ->assertForbidden();
});

it('rejects attendance carrying a student from another class', function (): void {
    $mine = StudentProfile::factory()->create([
        'current_class_id' => $this->myClass->id,
        'academic_year_id' => $this->year->id,
        'is_active' => true,
    ]);

    $outsider = StudentProfile::factory()->create([
        'current_class_id' => $this->otherClass->id,
        'academic_year_id' => $this->year->id,
        'is_active' => true,
    ]);

    $this->actingAs($this->teacher)
        ->post(route('teacher.attendance.store'), [
            'class_id' => $this->myClass->id,
            'subject_id' => $this->math->id,
            'date' => now()->toDateString(),
            'session' => 1,
            'students' => [
                ['student_id' => $mine->id, 'status' => 'present'],
                ['student_id' => $outsider->id, 'status' => 'present'],
            ],
        ])
        ->assertForbidden();
});

it('keeps a teacher out of another teacher subject scope', function (): void {
    $scope = teacherPortalScope($this->otherTeacher);

    expect($scope->canAccessSubject($this->math->id))->toBeFalse()
        ->and($scope->canAccessSubject($this->arabic->id))->toBeTrue();
});
