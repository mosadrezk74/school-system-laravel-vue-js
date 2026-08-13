<?php

use App\Models\User;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Student\Http\Requests\StoreStudentRequest;
use Modules\Student\Http\Requests\UpdateStudentRequest;
use Modules\Student\Models\StudentProfile;

/**
 * Two fields on the student form, both fixed for the same reason: the form and
 * the rules had drifted apart.
 *
 * The password carried `confirmed` while only one box was ever rendered, so
 * every submission failed on a field the user could not see. The blood group
 * was a free-text input validated against a fixed list, so a typo was the
 * user's problem rather than the form's.
 */
beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function studentFormAdmin(): User
{
    $user = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $user->assignRole(UserRole::Admin->value);

    return $user;
}

/*
|--------------------------------------------------------------------------
| The password is entered once
|--------------------------------------------------------------------------
*/

test('the create rules do not require a confirmation field', function () {
    $rules = (new StoreStudentRequest)->rules();

    expect($rules['password'])->not->toContain('confirmed')
        ->and($rules['password'])->toContain('required')
        ->and($rules['password'])->toContain('min:8');
});

test('the update rules do not require a confirmation field', function () {
    $rules = (new UpdateStudentRequest)->rules();

    expect($rules['password'])->not->toContain('confirmed')
        ->and($rules['password'])->toContain('nullable');
});

test('a password submitted once is accepted', function () {
    $this->actingAs(studentFormAdmin())
        ->from(route('students.create'))
        ->post(route('students.store'), [
            'name' => 'طالب تجريبي',
            'email' => 'new.student@gmail.com',
            'username' => 'new.student',
            'password' => 'secret123',
            'gender' => 'male',
            'birth_date' => now()->subYears(12)->toDateString(),
            'admission_date' => now()->toDateString(),
        ])
        ->assertSessionDoesntHaveErrors('password');
});

test('a short password is still rejected', function () {
    $this->actingAs(studentFormAdmin())
        ->from(route('students.create'))
        ->post(route('students.store'), ['password' => 'short'])
        ->assertSessionHasErrors('password');
});

/*
|--------------------------------------------------------------------------
| Blood group is a dropdown
|--------------------------------------------------------------------------
*/

test('the create form is given the blood groups the rules accept', function () {
    $this->actingAs(studentFormAdmin())
        ->get(route('students.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Student::Students/Create')
            // The same list the rule validates against, so the dropdown can
            // never offer a value the request would reject.
            ->where('bloodGroups', StoreStudentRequest::BLOOD_GROUPS)
        );
});

test('the edit form is given them too', function () {
    $student = StudentProfile::factory()->create();

    $this->actingAs(studentFormAdmin())
        ->get(route('students.edit', $student))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Student::Students/Edit')
            ->where('bloodGroups', StoreStudentRequest::BLOOD_GROUPS)
        );
});

test('every offered blood group passes validation', function (string $group) {
    $this->actingAs(studentFormAdmin())
        ->from(route('students.create'))
        ->post(route('students.store'), ['blood_group' => $group])
        ->assertSessionDoesntHaveErrors('blood_group');
})->with(StoreStudentRequest::BLOOD_GROUPS);

test('a blood group outside the list is rejected', function () {
    $this->actingAs(studentFormAdmin())
        ->from(route('students.create'))
        ->post(route('students.store'), ['blood_group' => 'Z+'])
        ->assertSessionHasErrors('blood_group');
});

test('blood group stays optional', function () {
    $this->actingAs(studentFormAdmin())
        ->from(route('students.create'))
        ->post(route('students.store'), ['blood_group' => null])
        ->assertSessionDoesntHaveErrors('blood_group');
});
