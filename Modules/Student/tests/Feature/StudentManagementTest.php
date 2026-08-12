<?php

use App\Models\User;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function actingAdmin(): User
{
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $admin->assignRole('admin');

    return $admin;
}

function studentManagementPayload(AcademicYear $year, ?SchoolClass $class = null, array $overrides = []): array
{
    return array_merge([
        'name' => 'Sara Ahmed',
        'email' => 'sara.ahmed@example.com',
        'username' => 'sara.ahmed',
        'password' => 'password123',
        'gender' => 'female',
        'birth_date' => '2012-05-01',
        'national_id' => '12345678901234',
        'phone' => '0500000000',
        'is_active' => true,
        'academic_year_id' => $year->id,
        'current_class_id' => $class?->id,
        'admission_number' => null,
        'roll_number' => '1',
        'admission_date' => now()->toDateString(),
        'is_graduated' => false,
        'father_name' => 'Ahmed Ali',
        'father_phone' => '0511111111',
        'mother_name' => 'Mona Ali',
        'mother_phone' => '0522222222',
        'guardian_name' => 'Ahmed Ali',
        'guardian_phone' => '0511111111',
        'guardian_relation' => 'father',
        'address' => '123 street',
        'city' => 'Riyadh',
        'blood_group' => 'O+',
        'allergies' => null,
        'medical_conditions' => null,
        'emergency_contact_name' => 'Ahmed Ali',
        'emergency_contact_phone' => '0511111111',
        'emergency_contact_relation' => 'father',
    ], $overrides);
}

it('lets an authorized admin view the students index', function (): void {
    $admin = actingAdmin();

    $this->actingAs($admin)
        ->get('/user-management/students')
        ->assertOk();
});

it('creates both a user and a student profile', function (): void {
    $admin = actingAdmin();
    $year = AcademicYear::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->post('/user-management/students', studentManagementPayload($year))
        ->assertRedirect();

    $user = User::where('email', 'sara.ahmed@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::Student)
        ->and($user->fresh()->hasRole('student'))->toBeTrue();

    $profile = StudentProfile::where('user_id', $user->id)->first();

    expect($profile)->not->toBeNull()
        ->and($profile->academic_year_id)->toBe($year->id);
});

it('cannot create two students with the same email', function (): void {
    $admin = actingAdmin();
    $year = AcademicYear::factory()->create(['is_active' => true]);

    User::factory()->create(['email' => 'sara.ahmed@example.com']);

    $this->actingAs($admin)
        ->post('/user-management/students', studentManagementPayload($year))
        ->assertSessionHasErrors('email');

    expect(StudentProfile::count())->toBe(0);
});

it('cannot create two students with the same admission number', function (): void {
    $admin = actingAdmin();
    $year = AcademicYear::factory()->create(['is_active' => true]);

    StudentProfile::factory()->create(['admission_number' => 'AD-0001', 'academic_year_id' => $year->id]);

    $this->actingAs($admin)
        ->post('/user-management/students', studentManagementPayload($year, null, [
            'email' => 'other@example.com',
            'username' => 'other',
            'admission_number' => 'AD-0001',
        ]))
        ->assertSessionHasErrors('admission_number');
});

it('rolls back user creation when the academic year is inactive', function (): void {
    $admin = actingAdmin();
    $year = AcademicYear::factory()->create(['is_active' => false]);

    $this->actingAs($admin)
        ->post('/user-management/students', studentManagementPayload($year));

    expect(User::where('email', 'sara.ahmed@example.com')->exists())->toBeFalse();
});

it('updates a student and persists the changes', function (): void {
    $admin = actingAdmin();
    $year = AcademicYear::factory()->create(['is_active' => true]);
    $student = StudentProfile::factory()->create(['academic_year_id' => $year->id]);
    $student->load('user');

    $payload = studentManagementPayload($year, null, [
        'email' => $student->user->email,
        'username' => $student->user->username,
        'name' => 'Updated Name',
        'admission_number' => $student->admission_number,
    ]);

    $this->actingAs($admin)
        ->put("/user-management/students/{$student->id}", $payload)
        ->assertRedirect();

    expect($student->user->fresh()->name)->toBe('Updated Name');
});

it('rejects updating a student to a class from a different academic year', function (): void {
    $admin = actingAdmin();
    $year = AcademicYear::factory()->create(['is_active' => true]);
    $otherYear = AcademicYear::factory()->create(['is_active' => true]);

    $gradeLevel = GradeLevel::create([
        'code' => 'G-'.uniqid(),
        'name' => ['en' => 'Grade 1', 'ar' => 'الصف الأول'],
        'is_active' => true,
    ]);

    $class = SchoolClass::create([
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Class 1',
        'is_active' => true,
    ]);

    $student = StudentProfile::factory()->create(['academic_year_id' => $year->id]);
    $student->load('user');

    $payload = studentManagementPayload($year, null, [
        'email' => $student->user->email,
        'username' => $student->user->username,
        'admission_number' => $student->admission_number,
        'current_class_id' => $class->id,
    ]);

    $this->actingAs($admin)
        ->put("/user-management/students/{$student->id}", $payload);

    expect($student->fresh()->current_class_id)->toBeNull();
});

it('shows the student profile page', function (): void {
    $admin = actingAdmin();
    $year = AcademicYear::factory()->create(['is_active' => true]);
    $student = StudentProfile::factory()->create(['academic_year_id' => $year->id]);

    $this->actingAs($admin)
        ->get("/user-management/students/{$student->id}")
        ->assertInertia(fn ($page) => $page->component('Student::Students/Show'));
});

it('denies index and show to a user without student.view permission', function (): void {
    $user = User::factory()->create(['role' => UserRole::Student, 'is_active' => true]);
    $user->assignRole('student');

    $year = AcademicYear::factory()->create(['is_active' => true]);
    $student = StudentProfile::factory()->create(['academic_year_id' => $year->id]);

    // The 'student' Spatie role is granted only limited permissions, none of
    // which is student.view (see RolePermissionSeeder ROLE_GRANTS).
    $this->actingAs($user)
        ->get('/user-management/students')
        ->assertForbidden();

    $this->actingAs($user)
        ->get("/user-management/students/{$student->id}")
        ->assertForbidden();
});

it('binds the {student} route parameter to StudentProfile, not User', function (): void {
    $admin = actingAdmin();
    $year = AcademicYear::factory()->create(['is_active' => true]);
    $student = StudentProfile::factory()->create(['academic_year_id' => $year->id]);
    $student->load('user');

    // A coincidentally-numbered User must not resolve here.
    $this->actingAs($admin)
        ->get("/user-management/students/{$student->id}/edit")
        ->assertInertia(fn ($page) => $page->component('Student::Students/Edit')
            ->where('student.id', $student->id)
            ->where('student.user_id', $student->user_id));
});
