<?php

use App\Models\User;
use Illuminate\Support\Str;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Meeting\Models\Meeting;
use Modules\Meeting\Services\MeetingMediaDefaults;
use Modules\Setting\Models\SchoolSetting;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function controlUser(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    return $user;
}

function liveMeeting(User $teacher): Meeting
{
    return Meeting::query()->create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Algebra',
        'room_name' => 'algebra-'.Str::lower(Str::random(6)),
        'meeting_url' => 'https://meet.etqan.cloud/algebra',
        'scheduled_at' => now(),
        'duration_minutes' => 45,
        'status' => 'live',
        'teacher_id' => $teacher->id,
    ]);
}

it('lets the owning teacher finish their meeting', function (): void {
    $teacher = controlUser(UserRole::Teacher);
    $meeting = liveMeeting($teacher);

    expect($teacher->can('finish', $meeting))->toBeTrue()
        ->and($teacher->can('cancel', $meeting))->toBeTrue();
});

it('lets an admin finish any meeting', function (): void {
    $meeting = liveMeeting(controlUser(UserRole::Teacher));
    $admin = controlUser(UserRole::Admin);

    expect($admin->can('finish', $meeting))->toBeTrue()
        ->and($admin->can('cancel', $meeting))->toBeTrue();
});

it('lets the owner finish any meeting', function (): void {
    $meeting = liveMeeting(controlUser(UserRole::Teacher));
    $owner = controlUser(UserRole::Owner);

    expect($owner->can('finish', $meeting))->toBeTrue()
        ->and($owner->can('cancel', $meeting))->toBeTrue();
});

it('never lets a student or guardian control a meeting', function (UserRole $role): void {
    $meeting = liveMeeting(controlUser(UserRole::Teacher));
    $user = controlUser($role);

    expect($user->can('finish', $meeting))->toBeFalse()
        ->and($user->can('cancel', $meeting))->toBeFalse()
        ->and($user->can('start', $meeting))->toBeFalse()
        ->and($user->can('delete', $meeting))->toBeFalse();
})->with([
    'student' => UserRole::Student,
    'guardian' => UserRole::Guardian,
]);

it('does not let one teacher control another teacher\'s meeting', function (): void {
    $meeting = liveMeeting(controlUser(UserRole::Teacher));
    $other = controlUser(UserRole::Teacher);

    expect($other->can('finish', $meeting))->toBeFalse()
        ->and($other->can('cancel', $meeting))->toBeFalse();
});

it('rejects a student posting to the finish endpoint', function (): void {
    $meeting = liveMeeting(controlUser(UserRole::Teacher));

    $this->actingAs(controlUser(UserRole::Student))
        ->patch(route('meetings.finish', $meeting))
        ->assertForbidden();

    expect($meeting->fresh()->status)->toBe('live');
});

it('reports the meeting status to a participant so the room can close', function (): void {
    $teacher = controlUser(UserRole::Teacher);
    $meeting = liveMeeting($teacher);

    $this->actingAs($teacher)
        ->getJson(route('meetings.status', $meeting))
        ->assertOk()
        ->assertJson(['status' => 'live']);

    $meeting->update(['status' => 'finished']);

    $this->actingAs($teacher)
        ->getJson(route('meetings.status', $meeting))
        ->assertOk()
        ->assertJson(['status' => 'finished']);
});

it('starts muted with the prejoin screen by default', function (): void {
    expect(app(MeetingMediaDefaults::class)->toArray())->toBe([
        'start_with_audio_muted' => true,
        'start_with_video_muted' => true,
        'show_prejoin' => true,
    ]);
});

it('honours the media defaults configured for the school', function (): void {
    SchoolSetting::query()->create([
        'school_name' => 'Athr',
        'school_code' => 'ATHR',
        'settings' => [
            'meeting_start_with_audio_muted' => false,
            'meeting_start_with_video_muted' => true,
            'meeting_show_prejoin' => false,
        ],
    ]);

    expect(app(MeetingMediaDefaults::class)->toArray())->toBe([
        'start_with_audio_muted' => false,
        'start_with_video_muted' => true,
        'show_prejoin' => false,
    ]);
});
