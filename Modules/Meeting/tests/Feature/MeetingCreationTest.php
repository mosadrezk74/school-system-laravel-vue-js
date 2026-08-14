<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Models\Semester;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Meeting\Models\Meeting;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Subject\Models\Subject;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function meetingFormUser(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    return $user;
}

function meetingFormYear(): AcademicYear
{
    return AcademicYear::query()->create([
        'name' => '2026/2027',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_current' => true,
    ]);
}

function meetingFormClass(string $name = 'Class 3-A'): SchoolClass
{
    $grade = GradeLevel::query()->create(['name' => ['en' => 'Grade 3'], 'level_order' => 3]);

    return SchoolClass::query()->create([
        'name' => $name,
        'grade_level_id' => $grade->id,
    ]);
}

/**
 * The create form submits teacher, class and subject, all of which the request
 * validates. Without the matching lookups the selects render empty and the
 * meeting can never be saved (teacher_id is required) or scoped.
 */
test('the create page supplies every lookup the form needs', function () {
    $year = meetingFormYear();
    $teacher = meetingFormUser(UserRole::Teacher);
    $class = meetingFormClass();
    $subject = Subject::query()->create(['name' => ['en' => 'Algebra'], 'is_active' => true]);

    Semester::query()->create([
        'academic_year_id' => $year->id,
        'name' => ['en' => 'First term'],
        'sequence' => 1,
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
    ]);

    $this->actingAs(meetingFormUser(UserRole::Admin))
        ->get('/meetings/meetings/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Meeting::Meetings/Create')
            ->has('teachers', 1, fn ($option) => $option->where('id', $teacher->id)->etc())
            ->has('semesters', 1)
            ->has('classes', 1, fn ($option) => $option->where('id', $class->id)->where('name', 'Class 3-A'))
            ->has('subjects', 1, fn ($option) => $option->where('id', $subject->id)->where('name', 'Algebra'))
        );
});

test('an admin can create a meeting scoped to a teacher, class and subject', function () {
    meetingFormYear();
    $teacher = meetingFormUser(UserRole::Teacher);
    $class = meetingFormClass();
    $subject = Subject::query()->create(['name' => ['en' => 'Algebra'], 'is_active' => true]);

    $this->actingAs(meetingFormUser(UserRole::Admin))
        ->post('/meetings/meetings', [
            'title' => 'Algebra revision',
            'description' => 'Revision before the exam',
            'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'duration_minutes' => 45,
            'teacher_id' => $teacher->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
        ])
        ->assertRedirect(route('meetings.index'))
        ->assertSessionHasNoErrors();

    $meeting = Meeting::query()->latest('id')->first();

    expect($meeting->title)->toBe('Algebra revision')
        ->and($meeting->teacher_id)->toBe($teacher->id)
        ->and($meeting->school_class_id)->toBe($class->id)
        ->and($meeting->subject_id)->toBe($subject->id)
        ->and($meeting->status)->toBe('scheduled');
});

/**
 * The requests and DTOs still carry `password`, but `meetings` has no such
 * column, so it is discarded on write. The form must not offer an input that
 * silently loses what the user types.
 */
test('a meeting password cannot be stored yet', function () {
    expect(Schema::hasColumn('meetings', 'password'))->toBeFalse();
});

test('a meeting cannot be created without a teacher', function () {
    meetingFormYear();

    $this->actingAs(meetingFormUser(UserRole::Admin))
        ->post('/meetings/meetings', [
            'title' => 'Unhosted meeting',
            'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'duration_minutes' => 45,
        ])
        ->assertSessionHasErrors('teacher_id');

    expect(Meeting::query()->count())->toBe(0);
});
