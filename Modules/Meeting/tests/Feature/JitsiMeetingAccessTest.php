<?php

use App\Models\User;
use Illuminate\Support\Str;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Meeting\Models\Meeting;
use Modules\Meeting\Services\JitsiRoomFactory;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function meetingUser(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    return $user;
}

function meetingClass(string $name = 'Class 3-A'): SchoolClass
{
    $year = AcademicYear::query()->create([
        'name' => '2026/2027',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_current' => true,
    ]);

    $grade = GradeLevel::query()->create(['name' => ['en' => 'Grade 3'], 'level_order' => 3]);

    return SchoolClass::query()->create([
        'name' => $name,
        'grade_level_id' => $grade->id,
    ]);
}

function meetingFor(User $teacher, ?SchoolClass $class = null, string $status = 'live'): Meeting
{
    $room = app(JitsiRoomFactory::class)->make();

    return Meeting::query()->create([
        'uuid' => $room['uuid'],
        'title' => 'Algebra lesson',
        'room_name' => $room['room_name'],
        'meeting_url' => $room['meeting_url'],
        'scheduled_at' => now(),
        'duration_minutes' => 45,
        'status' => $status,
        'teacher_id' => $teacher->id,
        'school_class_id' => $class?->id,
    ]);
}

function enrolStudent(User $student, SchoolClass $class): StudentProfile
{
    return StudentProfile::query()->create([
        'user_id' => $student->id,
        'admission_number' => (string) Str::random(8),
        'current_class_id' => $class->id,
        'academic_year_id' => AcademicYear::query()->value('id'),
        'admission_date' => now()->subYear(),
        'status' => 'active',
    ]);
}

test('the room name and url are generated on the server, never supplied by the client', function () {
    $teacher = meetingUser(UserRole::Teacher);
    $class = meetingClass();

    $this->actingAs(meetingUser(UserRole::Admin))
        ->post('/meetings/meetings', [
            'title' => 'Injected meeting',
            'description' => 'Attempting to choose a room',
            'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'duration_minutes' => 45,
            'teacher_id' => $teacher->id,
            'semester_id' => null,
            // Both should be ignored entirely.
            'room_name' => 'secret-principal-room',
            'meeting_url' => 'https://evil.example.com/secret-principal-room',
        ])
        ->assertRedirect();

    $meeting = Meeting::query()->latest('id')->first();

    expect($meeting->room_name)->not->toBe('secret-principal-room')
        ->and($meeting->meeting_url)->not->toContain('evil.example.com')
        ->and($meeting->room_name)->toMatch('/^[a-z0-9]{12}$/');
});

test('the meeting url is built from the configured jitsi domain', function () {
    config()->set('meeting.jitsi.domain', 'meet.etqan.cloud');

    $room = app(JitsiRoomFactory::class)->make();

    expect($room['meeting_url'])->toStartWith('https://meet.etqan.cloud/')
        ->and($room['meeting_url'])->toEndWith($room['room_name'])
        ->and(app(JitsiRoomFactory::class)->domain())->toBe('meet.etqan.cloud');
});

test('generated room names are unique', function () {
    $factory = app(JitsiRoomFactory::class);

    $rooms = collect(range(1, 20))->map(fn () => $factory->make()['room_name']);

    expect($rooms->unique())->toHaveCount(20);
});

test('the owning teacher can join and receives the embed configuration', function () {
    $teacher = meetingUser(UserRole::Teacher);
    $meeting = meetingFor($teacher, meetingClass());

    $this->actingAs($teacher)
        ->get(route('meetings.join', $meeting))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Meeting::Meetings/Join')
            ->where('jitsi.room_name', $meeting->room_name)
            ->where('jitsi.domain', 'meet.etqan.cloud')
            ->where('jitsi.display_name', $teacher->name)
            ->where('can.manage', true)
        );
});

test('a teacher may open their own meeting before it goes live', function () {
    $teacher = meetingUser(UserRole::Teacher);
    $meeting = meetingFor($teacher, meetingClass(), 'scheduled');

    $this->actingAs($teacher)
        ->get(route('meetings.join', $meeting))
        ->assertOk();
});

test('a teacher cannot join a meeting belonging to another teacher', function () {
    $owner = meetingUser(UserRole::Teacher);
    $intruder = meetingUser(UserRole::Teacher);

    $meeting = meetingFor($owner, meetingClass());

    $this->actingAs($intruder)
        ->get(route('meetings.join', $meeting))
        ->assertForbidden();
});

test('an enrolled student can join their class meeting', function () {
    $teacher = meetingUser(UserRole::Teacher);
    $class = meetingClass();
    $meeting = meetingFor($teacher, $class);

    $student = meetingUser(UserRole::Student);
    enrolStudent($student, $class);

    $this->actingAs($student)
        ->get(route('meetings.join', $meeting))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('jitsi.room_name', $meeting->room_name)
            // Students never receive moderator controls.
            ->where('can.manage', false)
        );
});

test('a student from another class cannot join the meeting', function () {
    $teacher = meetingUser(UserRole::Teacher);
    $meeting = meetingFor($teacher, meetingClass('Class 3-A'));

    $outsider = meetingUser(UserRole::Student);
    enrolStudent($outsider, meetingClass('Class 4-B'));

    $this->actingAs($outsider)
        ->get(route('meetings.join', $meeting))
        ->assertForbidden();
});

test('a student cannot join a meeting that is only scheduled', function () {
    $teacher = meetingUser(UserRole::Teacher);
    $class = meetingClass();
    $meeting = meetingFor($teacher, $class, 'scheduled');

    $student = meetingUser(UserRole::Student);
    enrolStudent($student, $class);

    $this->actingAs($student)
        ->get(route('meetings.join', $meeting))
        ->assertForbidden();
});

test('nobody may join a finished or cancelled meeting', function (string $status) {
    $teacher = meetingUser(UserRole::Teacher);
    $meeting = meetingFor($teacher, meetingClass(), $status);

    $this->actingAs($teacher)
        ->get(route('meetings.join', $meeting))
        ->assertForbidden();
})->with(['finished', 'cancelled']);

test('a guardian does not receive teacher controls for a meeting', function () {
    $teacher = meetingUser(UserRole::Teacher);
    $meeting = meetingFor($teacher, meetingClass());

    $response = $this->actingAs(meetingUser(UserRole::Guardian))
        ->get(route('meetings.join', $meeting));

    // Either refused outright, or admitted strictly as a viewer.
    if ($response->status() === 200) {
        $response->assertInertia(fn ($page) => $page->where('can.manage', false));
    } else {
        $response->assertForbidden();
    }
});

test('a guest is redirected to login rather than into the room', function () {
    $meeting = meetingFor(meetingUser(UserRole::Teacher), meetingClass());

    $this->get(route('meetings.join', $meeting))
        ->assertRedirect(route('login'));
});

test('an admin can join any meeting', function () {
    $meeting = meetingFor(meetingUser(UserRole::Teacher), meetingClass());

    $this->actingAs(meetingUser(UserRole::Admin))
        ->get(route('meetings.join', $meeting))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('can.manage', true));
});

test('an unauthorised user receives no meeting data at all', function () {
    $teacher = meetingUser(UserRole::Teacher);
    $meeting = meetingFor($teacher, meetingClass('Class 3-A'));

    $outsider = meetingUser(UserRole::Student);
    enrolStudent($outsider, meetingClass('Class 4-B'));

    $response = $this->actingAs($outsider)->get(route('meetings.join', $meeting));

    $response->assertForbidden();

    expect($response->getContent())->not->toContain($meeting->room_name)
        ->and($response->getContent())->not->toContain($meeting->meeting_url);
});
