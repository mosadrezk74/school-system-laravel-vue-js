<?php

use App\Models\User;
use Illuminate\Support\Str;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Services\AcademicYearService;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Meeting\Models\Meeting;
use Modules\SchoolClass\Models\ClassSubject;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;
use Modules\Subject\Models\Subject;
use Modules\Teacher\Models\TeacherProfile;
use Modules\Timetable\Models\Timetable;
use Modules\Timetable\Models\TimetableEntry;

function isolationTeacher(): User
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

function isolationClass(AcademicYear $year): SchoolClass
{
    return SchoolClass::create([
        'name' => fake()->unique()->bothify('Class-##'),
        'grade_level_id' => GradeLevel::create([
            'name' => fake()->unique()->bothify('Grade-##'),
            'level' => fake()->unique()->numberBetween(1, 900),
            'is_active' => true,
        ])->id,
        'capacity' => 30,
        'is_active' => true,
    ]);
}

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->year = AcademicYear::factory()->current()->create();
    AcademicYearService::flush();

    $this->teacherA = isolationTeacher();
    $this->teacherB = isolationTeacher();

    $this->subjectA = Subject::create(['name' => fake()->unique()->words(2, true), 'is_active' => true]);
    $this->subjectB = Subject::create(['name' => fake()->unique()->words(2, true), 'is_active' => true]);

    $this->classA = isolationClass($this->year);
    $this->classB = isolationClass($this->year);

    ClassSubject::create([
        'class_id' => $this->classA->id,
        'subject_id' => $this->subjectA->id,
        'teacher_id' => $this->teacherA->id,
        'academic_year_id' => $this->year->id,
    ]);

    ClassSubject::create([
        'class_id' => $this->classB->id,
        'subject_id' => $this->subjectB->id,
        'teacher_id' => $this->teacherB->id,
        'academic_year_id' => $this->year->id,
    ]);
});

it('shows a teacher only their own classes', function (): void {
    $this->actingAs($this->teacherA)
        ->get(route('teacher.classes'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('classes', 1)
            ->where('classes.0.id', $this->classA->id)
        );
});

it('shows a teacher only students from their own classes', function (): void {
    $mine = StudentProfile::factory()->create([
        'current_class_id' => $this->classA->id,
        'academic_year_id' => $this->year->id,
        'is_active' => true,
    ]);

    StudentProfile::factory()->create([
        'current_class_id' => $this->classB->id,
        'academic_year_id' => $this->year->id,
        'is_active' => true,
    ]);

    $this->actingAs($this->teacherA)
        ->get(route('teacher.classes'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('classes.0.students_count', 1)
            ->where('classes.0.students.0.id', $mine->id)
        );
});

it('shows a teacher only their own timetable entries', function (): void {
    $timetable = Timetable::create([
        'class_id' => $this->classA->id,
        'academic_year_id' => $this->year->id,
        'is_active' => true,
    ]);

    $mine = TimetableEntry::create([
        'timetable_id' => $timetable->id,
        'day_of_week' => 1,
        'period_number' => 1,
        'start_time' => '08:00',
        'end_time' => '08:45',
        'subject_id' => $this->subjectA->id,
        'teacher_id' => $this->teacherA->id,
    ]);

    TimetableEntry::create([
        'timetable_id' => $timetable->id,
        'day_of_week' => 1,
        'period_number' => 2,
        'start_time' => '09:00',
        'end_time' => '09:45',
        'subject_id' => $this->subjectB->id,
        'teacher_id' => $this->teacherB->id,
    ]);

    $this->actingAs($this->teacherA)
        ->get(route('teacher.schedule'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('entries', 1)
            ->where('entries.0.id', $mine->id)
        );
});

it('lists only the meetings a teacher owns', function (): void {
    Meeting::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'B private',
        'room_name' => 'roomb',
        'meeting_url' => 'https://meet.etqan.cloud/roomb',
        'scheduled_at' => now()->addDay(),
        'duration_minutes' => 45,
        'status' => 'scheduled',
        'teacher_id' => $this->teacherB->id,
        'school_class_id' => $this->classB->id,
        'academic_year_id' => $this->year->id,
    ]);

    $this->actingAs($this->teacherA)
        ->get(route('teacher.meetings'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('meetings.data', 0));
});

it('forbids a teacher from starting another teachers meeting', function (): void {
    $meeting = Meeting::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'B private',
        'room_name' => 'roombb',
        'meeting_url' => 'https://meet.etqan.cloud/roombb',
        'scheduled_at' => now()->addDay(),
        'duration_minutes' => 45,
        'status' => 'scheduled',
        'teacher_id' => $this->teacherB->id,
        'school_class_id' => $this->classB->id,
        'academic_year_id' => $this->year->id,
    ]);

    $this->actingAs($this->teacherA)
        ->patch(route('teacher.meetings.start', $meeting))
        ->assertForbidden();
});

it('forbids a teacher from opening another teachers meeting room', function (): void {
    $meeting = Meeting::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'B private',
        'room_name' => 'roombbb',
        'meeting_url' => 'https://meet.etqan.cloud/roombbb',
        'scheduled_at' => now()->addDay(),
        'duration_minutes' => 45,
        'status' => 'live',
        'teacher_id' => $this->teacherB->id,
        'school_class_id' => $this->classB->id,
        'academic_year_id' => $this->year->id,
    ]);

    $this->actingAs($this->teacherA)
        ->get(route('meetings.join', $meeting))
        ->assertForbidden();
});

it('keeps a teacher out of admin only pages', function (): void {
    $this->actingAs($this->teacherA)
        ->get(route('teachers.index'))
        ->assertForbidden();
});

it('keeps a student out of teacher endpoints', function (): void {
    $student = User::factory()->create(['role' => UserRole::Student, 'is_active' => true]);
    $student->assignRole('student');

    $this->actingAs($student)
        ->get(route('teacher.classes'))
        ->assertForbidden();

    $this->actingAs($student)
        ->get(route('teacher.attendance'))
        ->assertForbidden();
});

it('keeps a guardian out of teacher endpoints', function (): void {
    $guardian = User::factory()->create(['role' => UserRole::Guardian, 'is_active' => true]);
    $guardian->assignRole('guardian');

    $this->actingAs($guardian)
        ->get(route('teacher.schedule'))
        ->assertForbidden();
});

it('forbids a teacher from viewing another teachers meeting detail page', function (): void {
    $meeting = Meeting::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'B private',
        'room_name' => 'roomshow',
        'meeting_url' => 'https://meet.etqan.cloud/roomshow',
        'scheduled_at' => now()->addDay(),
        'duration_minutes' => 45,
        'status' => 'scheduled',
        'teacher_id' => $this->teacherB->id,
        'school_class_id' => $this->classB->id,
        'academic_year_id' => $this->year->id,
    ]);

    $this->actingAs($this->teacherA)
        ->get(route('meetings.show', $meeting))
        ->assertForbidden();
});

it('still lets an admin view any meeting', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $admin->assignRole('admin');

    $meeting = Meeting::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'B private',
        'room_name' => 'roomadmin',
        'meeting_url' => 'https://meet.etqan.cloud/roomadmin',
        'scheduled_at' => now()->addDay(),
        'duration_minutes' => 45,
        'status' => 'scheduled',
        'teacher_id' => $this->teacherB->id,
        'school_class_id' => $this->classB->id,
        'academic_year_id' => $this->year->id,
    ]);

    $this->actingAs($admin)->get(route('meetings.show', $meeting))->assertOk();
});

it('still lets an owning teacher view their own meeting', function (): void {
    $meeting = Meeting::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'A own',
        'room_name' => 'roomown',
        'meeting_url' => 'https://meet.etqan.cloud/roomown',
        'scheduled_at' => now()->addDay(),
        'duration_minutes' => 45,
        'status' => 'scheduled',
        'teacher_id' => $this->teacherA->id,
        'school_class_id' => $this->classA->id,
        'academic_year_id' => $this->year->id,
    ]);

    $this->actingAs($this->teacherA)->get(route('meetings.show', $meeting))->assertOk();
});

it('still lets an enrolled student view their class meeting', function (): void {
    $student = User::factory()->create(['role' => UserRole::Student, 'is_active' => true]);
    $student->assignRole('student');

    StudentProfile::factory()->create([
        'user_id' => $student->id,
        'current_class_id' => $this->classA->id,
        'academic_year_id' => $this->year->id,
        'is_active' => true,
    ]);

    $meeting = Meeting::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'A class',
        'room_name' => 'roomstu',
        'meeting_url' => 'https://meet.etqan.cloud/roomstu',
        'scheduled_at' => now()->addDay(),
        'duration_minutes' => 45,
        'status' => 'scheduled',
        'teacher_id' => $this->teacherA->id,
        'school_class_id' => $this->classA->id,
        'academic_year_id' => $this->year->id,
    ]);

    $this->actingAs($student)->get(route('meetings.show', $meeting))->assertOk();
});
