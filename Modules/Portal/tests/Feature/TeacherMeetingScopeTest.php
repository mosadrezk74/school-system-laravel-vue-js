<?php

use App\Models\User;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Meeting\Models\Meeting;
use Modules\SchoolClass\Models\ClassSubject;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Subject\Models\Subject;
use Modules\Teacher\Models\TeacherProfile;
use Spatie\Permission\PermissionRegistrar;

/**
 * A teacher meeting must be filed against a class/subject the teacher is
 * actually assigned to teach. Before this was enforced the form's class and
 * subject were dropped on the floor and every meeting was stored unscoped.
 */
beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function meetingTeacher(string $email): User
{
    $user = User::factory()->create(['role' => UserRole::Teacher->value, 'email' => $email, 'is_active' => true]);
    $user->assignRole(UserRole::Teacher->value);
    TeacherProfile::create(['user_id' => $user->id, 'is_active' => true]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user->refresh();
}

function meetingClassSubject(AcademicYear $year, User $teacher): array
{
    $grade = GradeLevel::create(['name' => ['en' => 'G'.uniqid()], 'is_active' => true]);
    $class = SchoolClass::create([
        'grade_level_id' => $grade->id,
        'academic_year_id' => $year->id,
        'name' => 'Class '.uniqid(),
        'capacity' => 30,
        'is_active' => true,
    ]);
    $subject = Subject::create(['name' => ['en' => 'Subj'.uniqid()], 'is_active' => true]);
    ClassSubject::create([
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'academic_year_id' => $year->id,
    ]);

    return [$class, $subject];
}

function meetingPayload(array $o = []): array
{
    return array_merge([
        'title' => 'Lesson',
        'description' => 'desc',
        'scheduled_at' => now()->addDay()->toDateTimeString(),
        'duration_minutes' => 45,
    ], $o);
}

it('stores the class and subject a teacher selected', function () {
    $year = AcademicYear::factory()->current()->create();
    $teacher = meetingTeacher('own@example.com');
    [$class, $subject] = meetingClassSubject($year, $teacher);

    $this->actingAs($teacher)
        ->post('/teacher/meetings', meetingPayload([
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
        ]))
        ->assertRedirect();

    $meeting = Meeting::query()->latest('id')->first();

    expect($meeting)->not->toBeNull()
        ->and($meeting->school_class_id)->toBe($class->id)
        ->and($meeting->subject_id)->toBe($subject->id)
        ->and($meeting->teacher_id)->toBe($teacher->id);
});

it('rejects a class the teacher is not assigned to', function () {
    $year = AcademicYear::factory()->current()->create();
    $teacher = meetingTeacher('a@example.com');
    $other = meetingTeacher('b@example.com');
    meetingClassSubject($year, $teacher);
    [$foreignClass, $foreignSubject] = meetingClassSubject($year, $other);

    $this->actingAs($teacher)
        ->post('/teacher/meetings', meetingPayload([
            'school_class_id' => $foreignClass->id,
            'subject_id' => $foreignSubject->id,
        ]))
        ->assertSessionHasErrors('subject_id');

    expect(Meeting::query()->count())->toBe(0);
});

it('rejects a subject the teacher does not teach in their own class', function () {
    $year = AcademicYear::factory()->current()->create();
    $teacher = meetingTeacher('c@example.com');
    $other = meetingTeacher('d@example.com');
    [$ownClass] = meetingClassSubject($year, $teacher);
    [, $foreignSubject] = meetingClassSubject($year, $other);

    $this->actingAs($teacher)
        ->post('/teacher/meetings', meetingPayload([
            'school_class_id' => $ownClass->id,
            'subject_id' => $foreignSubject->id,
        ]))
        ->assertSessionHasErrors('subject_id');

    expect(Meeting::query()->count())->toBe(0);
});

it('keeps a student out of the teacher meeting endpoint', function () {
    $student = User::factory()->create(['role' => UserRole::Student->value, 'is_active' => true]);
    $student->assignRole(UserRole::Student->value);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->actingAs($student->refresh())
        ->post('/teacher/meetings', meetingPayload())
        ->assertForbidden();
});
