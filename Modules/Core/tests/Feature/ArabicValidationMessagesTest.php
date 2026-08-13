<?php

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;

/**
 * Arabic is the default locale, but the project shipped without a root lang/
 * directory, so every rule fell back to the framework's built-in English text
 * while attributes() supplied an Arabic field name. Password confirmation on
 * the create-student form read:
 *
 *   "The كلمة المرور field confirmation does not match."
 *
 * These lock the messages to Arabic so a future `lang:publish` or an upgrade
 * cannot silently reintroduce the half-translated text.
 */
beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    app()->setLocale('ar');
});

function assertNoLatinLetters(string $message): void
{
    expect($message)->not->toMatch('/[A-Za-z]/');
}

/*
|--------------------------------------------------------------------------
| The reported case
|--------------------------------------------------------------------------
*/

test('the password confirmation message is fully arabic', function () {
    $validator = Validator::make(
        ['password' => 'secret123', 'password_confirmation' => 'different'],
        ['password' => ['required', 'string', 'min:8', 'confirmed']],
        [],
        ['password' => __('student::student.fields.password')],
    );

    $validator->fails();
    $message = $validator->errors()->first('password');

    expect($message)->toBe('تأكيد كلمة المرور غير مطابق.');
    assertNoLatinLetters($message);
});

test('a too-short student password returns an arabic error from the real endpoint', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $admin->assignRole(UserRole::Admin->value);

    /*
     * Only the password matters here; the other fields are left out on
     * purpose and their own errors are ignored. Going through the real
     * endpoint is what proves the message the form receives is translated,
     * rather than only the message a hand-built Validator produces.
     */
    $this->actingAs($admin)
        ->from(route('students.create'))
        ->post(route('students.store'), ['password' => 'short'])
        ->assertSessionHasErrors('password');

    $message = session('errors')->first('password');

    assertNoLatinLetters($message);
    expect($message)->toContain('كلمة المرور');
});

/*
|--------------------------------------------------------------------------
| The rest of the rule set
|--------------------------------------------------------------------------
*/

test('common rules all render in arabic', function (array $data, array $rules, string $expected) {
    $validator = Validator::make(
        $data,
        $rules,
        [],
        [
            'password' => __('student::student.fields.password'),
            // :other renders a field name too, so it needs a label of its own
            // or the message would carry a raw snake_case key.
            'other' => 'الحقل الآخر',
        ],
    );

    $validator->fails();
    $message = $validator->errors()->first('password');

    expect($message)->toBe($expected);
    assertNoLatinLetters($message);
})->with([
    'required' => [[], ['password' => ['required']], 'حقل كلمة المرور مطلوب.'],
    'min' => [['password' => 'abc'], ['password' => ['string', 'min:8']], 'يجب أن يتكون حقل كلمة المرور من 8 أحرف على الأقل.'],
    'numeric' => [['password' => 'abc'], ['password' => ['numeric']], 'يجب أن يكون حقل كلمة المرور رقمًا.'],
    'same' => [['password' => 'a', 'other' => 'b'], ['password' => ['same:other']], 'يجب أن يتطابق حقل كلمة المرور مع الحقل الآخر.'],
]);

/*
|--------------------------------------------------------------------------
| Structure
|--------------------------------------------------------------------------
*/

test('the arabic file covers every rule the framework ships', function () {
    $en = require base_path('lang/en/validation.php');
    $ar = require base_path('lang/ar/validation.php');

    $missing = [];

    foreach ($en as $key => $value) {
        if (! array_key_exists($key, $ar)) {
            $missing[] = $key;

            continue;
        }

        if (! is_array($value)) {
            continue;
        }

        foreach (array_keys($value) as $subKey) {
            if (! array_key_exists($subKey, $ar[$key])) {
                $missing[] = "{$key}.{$subKey}";
            }
        }
    }

    expect($missing)->toBe([]);
});

test('auth and password broker lines are arabic too', function () {
    assertNoLatinLetters(__('auth.failed'));
    assertNoLatinLetters(__('passwords.token'));

    expect(__('auth.failed'))->toBe('بيانات الدخول غير صحيحة.')
        ->and(__('passwords.sent'))->toContain('إعادة تعيين كلمة المرور');
});

test('english is untouched for english speakers', function () {
    app()->setLocale('en');

    $validator = Validator::make(
        ['password' => 'secret123', 'password_confirmation' => 'different'],
        ['password' => ['confirmed']],
    );

    $validator->fails();

    expect($validator->errors()->first('password'))
        ->toBe('The password field confirmation does not match.');
});
