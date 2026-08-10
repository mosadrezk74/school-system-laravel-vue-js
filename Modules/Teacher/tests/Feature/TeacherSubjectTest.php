<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Subject\Models\Subject;
use Modules\Teacher\Interfaces\Repositories\TeacherRepositoryInterface;
use Modules\Teacher\Models\TeacherProfile;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
        'is_active' => true,
    ]);
    $this->admin->assignRole('admin');
});

/**
 * Creates a subject directly; the Subject module ships no factory.
 */
function makeSubject(array $name): Subject
{
    return Subject::create([
        'name' => $name,
        'is_active' => true,
    ]);
}

test('a teacher can be created with subjects attached', function () {
    $math = makeSubject(['en' => 'Mathematics', 'ar' => 'الرياضيات']);
    $arabic = makeSubject(['en' => 'Arabic', 'ar' => 'العربية']);

    $this->actingAs($this->admin)
        ->post(route('teachers.store'), [
            'name' => 'Sara Ahmed',
            'email' => 'sara@example.com',
            'username' => 'sara',
            'password' => 'password123',
            'gender' => 'female',
            'is_active' => true,
            'subject_ids' => [$math->id, $arabic->id],
        ])
        ->assertRedirect(route('teachers.index'));

    $teacher = TeacherProfile::query()->latest('id')->first();

    expect($teacher->subjects->pluck('id')->sort()->values()->all())
        ->toBe(collect([$math->id, $arabic->id])->sort()->values()->all());
});

test('updating a teacher replaces the attached subjects', function () {
    $math = makeSubject(['en' => 'Mathematics']);
    $science = makeSubject(['en' => 'Science']);

    $user = User::factory()->create(['role' => UserRole::Teacher]);
    $teacher = TeacherProfile::create(['user_id' => $user->id, 'is_active' => true]);
    $teacher->subjects()->sync([$math->id]);

    $this->actingAs($this->admin)
        ->put(route('teachers.update', $teacher), [
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'gender' => $user->gender ?? 'male',
            'is_active' => true,
            'subject_ids' => [$science->id],
        ])
        ->assertRedirect(route('teachers.index'));

    expect($teacher->fresh()->subjects->pluck('id')->all())->toBe([$science->id]);
});

test('a teacher cannot be attached to a subject that does not exist', function () {
    $this->actingAs($this->admin)
        ->post(route('teachers.store'), [
            'name' => 'Bad Subject',
            'email' => 'bad@example.com',
            'username' => 'badsubject',
            'password' => 'password123',
            'gender' => 'male',
            'is_active' => true,
            'subject_ids' => [99999],
        ])
        ->assertSessionHasErrors('subject_ids.0');
});

test('the same subject cannot be attached twice to one teacher', function () {
    $math = makeSubject(['en' => 'Mathematics']);

    $user = User::factory()->create(['role' => UserRole::Teacher]);
    $teacher = TeacherProfile::create(['user_id' => $user->id, 'is_active' => true]);

    $teacher->subjects()->sync([$math->id, $math->id]);

    expect($teacher->fresh()->subjects)->toHaveCount(1);
});

test('teachers can be filtered by subject', function () {
    $math = makeSubject(['en' => 'Mathematics']);
    $art = makeSubject(['en' => 'Art']);

    $mathUser = User::factory()->create(['role' => UserRole::Teacher, 'name' => 'Math Teacher']);
    $mathTeacher = TeacherProfile::create(['user_id' => $mathUser->id, 'is_active' => true]);
    $mathTeacher->subjects()->sync([$math->id]);

    $artUser = User::factory()->create(['role' => UserRole::Teacher, 'name' => 'Art Teacher']);
    $artTeacher = TeacherProfile::create(['user_id' => $artUser->id, 'is_active' => true]);
    $artTeacher->subjects()->sync([$art->id]);

    $this->actingAs($this->admin)
        ->get(route('teachers.index', ['subject_id' => $math->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('teachers.data', 1)
            ->where('teachers.data.0.id', $mathTeacher->id)
        );
});

test('the teacher form receives real subjects instead of departments', function () {
    makeSubject(['en' => 'Mathematics']);

    $this->actingAs($this->admin)
        ->get(route('teachers.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('subjects', 1)
            ->missing('departments')
        );
});

test('deleting a subject detaches it from teachers without deleting them', function () {
    $math = makeSubject(['en' => 'Mathematics']);

    $user = User::factory()->create(['role' => UserRole::Teacher]);
    $teacher = TeacherProfile::create(['user_id' => $user->id, 'is_active' => true]);
    $teacher->subjects()->sync([$math->id]);

    $math->forceDelete();

    expect($teacher->fresh())->not->toBeNull()
        ->and($teacher->fresh()->subjects)->toHaveCount(0);
});

test('the departments table and every department column are gone', function () {
    expect(Schema::hasTable('departments'))->toBeFalse()
        ->and(Schema::hasColumn('teacher_profiles', 'department_id'))->toBeFalse();
});

test('no foreign key anywhere still points at departments', function () {
    $orphans = DB::select(
        'SELECT TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME = ?',
        ['departments']
    );

    expect($orphans)->toBeEmpty();
});

test('no department permission is seeded', function () {
    expect(DB::table('permissions')->where('name', 'LIKE', '%department%')->count())->toBe(0);
});

test('subject names reach the form resolved to the active locale, not raw json', function () {
    makeSubject(['en' => 'Mathematics', 'ar' => 'الرياضيات']);

    app()->setLocale('ar');

    $this->actingAs($this->admin)
        ->get(route('teachers.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('subjects.0.name', 'الرياضيات'));
});

test('an employee id is generated as 7 digits when the admin leaves it blank', function () {
    $this->actingAs($this->admin)
        ->post(route('teachers.store'), [
            'name' => 'Auto Id',
            'email' => 'autoid@example.com',
            'username' => 'autoid',
            'password' => 'password123',
            'gender' => 'male',
            'is_active' => true,
        ])
        ->assertRedirect(route('teachers.index'));

    $employeeId = TeacherProfile::query()->latest('id')->first()->employee_id;

    expect($employeeId)->toMatch('/^\d{7}$/');
});

test('an employee id supplied by the admin is kept as given', function () {
    $this->actingAs($this->admin)
        ->post(route('teachers.store'), [
            'name' => 'Manual Id',
            'email' => 'manualid@example.com',
            'username' => 'manualid',
            'password' => 'password123',
            'gender' => 'male',
            'is_active' => true,
            'employee_id' => '1234567',
        ])
        ->assertRedirect(route('teachers.index'));

    expect(TeacherProfile::query()->latest('id')->first()->employee_id)->toBe('1234567');
});

test('an employee id that is not exactly 7 digits is rejected', function () {
    $this->actingAs($this->admin)
        ->post(route('teachers.store'), [
            'name' => 'Short Id',
            'email' => 'shortid@example.com',
            'username' => 'shortid',
            'password' => 'password123',
            'gender' => 'male',
            'is_active' => true,
            'employee_id' => '123',
        ])
        ->assertSessionHasErrors('employee_id');
});

test('generated employee ids do not collide, including with archived teachers', function () {
    $repository = app(TeacherRepositoryInterface::class);

    $user = User::factory()->create(['role' => UserRole::Teacher]);
    $taken = TeacherProfile::create([
        'user_id' => $user->id,
        'employee_id' => $repository->generateEmployeeId(),
        'is_active' => true,
    ]);
    $taken->delete();

    $generated = collect(range(1, 15))->map(fn () => $repository->generateEmployeeId());

    expect($generated->unique())->toHaveCount(15)
        ->and($generated)->not->toContain($taken->employee_id)
        ->and($generated->every(fn (string $id): bool => (bool) preg_match('/^\d{7}$/', $id)))->toBeTrue();
});

test('editing a teacher without touching the employee id keeps it', function () {
    $user = User::factory()->create(['role' => UserRole::Teacher]);
    $teacher = TeacherProfile::create([
        'user_id' => $user->id,
        'employee_id' => '7654321',
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->put(route('teachers.update', $teacher), [
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'gender' => $user->gender ?? 'male',
            'is_active' => true,
        ])
        ->assertRedirect(route('teachers.index'));

    expect($teacher->fresh()->employee_id)->toBe('7654321');
});

test('an out of range salary is a validation error, not a database crash', function () {
    $this->actingAs($this->admin)
        ->post(route('teachers.store'), [
            'name' => 'Rich Teacher',
            'email' => 'rich@example.com',
            'username' => 'richteacher',
            'password' => 'password123',
            'gender' => 'male',
            'is_active' => true,
            'salary_base' => '2.5666666666667E+32',
        ])
        ->assertSessionHasErrors('salary_base');

    expect(TeacherProfile::query()->where('employee_id', '!=', null)->whereHas('user', fn ($q) => $q->where('email', 'rich@example.com'))->exists())->toBeFalse();
});

test('a salary of five hundred thousand is accepted', function () {
    $this->actingAs($this->admin)
        ->post(route('teachers.store'), [
            'name' => 'Paid Teacher',
            'email' => 'paid@example.com',
            'username' => 'paidteacher',
            'password' => 'password123',
            'gender' => 'male',
            'is_active' => true,
            'salary_base' => 500000,
        ])
        ->assertRedirect(route('teachers.index'))
        ->assertSessionHasNoErrors();

    expect((float) TeacherProfile::query()->latest('id')->first()->salary_base)->toBe(500000.0);
});

test('a non numeric salary is rejected', function () {
    $this->actingAs($this->admin)
        ->post(route('teachers.store'), [
            'name' => 'Text Salary',
            'email' => 'text@example.com',
            'username' => 'textsalary',
            'password' => 'password123',
            'gender' => 'male',
            'is_active' => true,
            'salary_base' => 'not-a-number',
        ])
        ->assertSessionHasErrors('salary_base');
});
