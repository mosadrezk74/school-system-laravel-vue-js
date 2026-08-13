<?php

use Modules\Academic\Http\Requests\StoreGradeLevelRequest;
use Modules\Assignment\Enums\AssignmentType;
use Modules\Assignment\Http\Requests\StoreAssignmentRequest;
use Modules\Auth\Enums\Gender;
use Modules\Auth\Http\Requests\StoreAdminRequest;
use Modules\Behavior\Enums\BehaviorType;
use Modules\Classroom\Enums\RoomType;
use Modules\Classroom\Http\Requests\StoreClassroomRequest;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Examination\Enums\ExamType;
use Modules\Examination\Http\Requests\StoreExamRequest;
use Modules\Finance\Http\Requests\StorePaymentMethodRequest;
use Modules\GuardianProfile\Enums\RelationshipType;
use Modules\GuardianProfile\Http\Requests\StoreGuardianRequest;
use Modules\News\Http\Requests\StoreNewsRequest;
use Modules\Student\Enums\BloodGroup;
use Modules\Student\Http\Requests\StoreStudentRequest;
use Modules\Teacher\Http\Requests\StoreTeacherRequest;

/**
 * The invariants behind the project-wide validation pass. Each group below
 * covers a class of defect that was present across many requests at once, so
 * the tests assert the property rather than re-listing every field.
 */

/*
|--------------------------------------------------------------------------
| Every rule stays inside its database column
|--------------------------------------------------------------------------
|
| Three requests allowed a 255-character username into `users.username`, a
| varchar(100), and the payment-method URL allowed 2048 into a varchar(255).
| Validation passed and MySQL then truncated or rejected the row.
*/

/** Requests that write the shared `users` columns. */
dataset('account requests', [
    'student' => [StoreStudentRequest::class],
    'teacher' => [StoreTeacherRequest::class],
    'guardian' => [StoreGuardianRequest::class],
    'admin' => [StoreAdminRequest::class],
]);

/**
 * Pulls the numeric limit out of a rule list, e.g. `max:100` => 100.
 *
 * @param  array<int, mixed>  $rules
 */
function maxRule(array $rules): ?int
{
    foreach ($rules as $rule) {
        if (is_string($rule) && str_starts_with($rule, 'max:')) {
            return (int) substr($rule, 4);
        }
    }

    return null;
}

test('every account request caps the username at the column width', function (string $requestClass) {
    $rules = (new $requestClass)->rules();

    // Trait constants are read through a using class, not the trait name.
    expect(maxRule($rules['username']))->toBe(StoreStudentRequest::USERNAME_MAX)
        ->and(StoreStudentRequest::USERNAME_MAX)->toBe(100);
})->with('account requests');

test('every account request caps the phone at the column width', function (string $requestClass) {
    $rules = (new $requestClass)->rules();

    expect(maxRule($rules['phone']))->toBe(20);
})->with('account requests');

test('the payment url fits the column it is stored in', function () {
    $rules = (new StorePaymentMethodRequest)->rules();

    // payment_methods.payment_url is varchar(255), not the 2048 this allowed.
    expect(maxRule($rules['payment_url']))->toBe(255);
});

/*
|--------------------------------------------------------------------------
| Translatable fields are bounded
|--------------------------------------------------------------------------
|
| translatableRules() emitted only ['required', 'string'], leaving name,
| title, description, body and content uncapped across twenty-odd requests.
| Those are all JSON columns, so the database imposed no width either.
*/

test('translatableRules always emits a max', function () {
    $request = new class extends BaseRequest
    {
        public function rules(): array
        {
            return [
                ...$this->translatableRules('name'),
                ...$this->translatableRules('note', required: false, max: 42),
            ];
        }
    };

    $rules = $request->rules();

    expect(maxRule($rules['name']))->toBe(255)
        ->and($rules['name'])->toContain('required')
        ->and(maxRule($rules['note']))->toBe(42)
        ->and($rules['note'])->toContain('nullable');
});

test('translatable fields across modules carry a real limit', function (string $requestClass, string $field) {
    $rules = (new $requestClass)->rules();

    expect(maxRule($rules[$field]))->toBeGreaterThan(0);
})->with([
    'grade level name' => [StoreGradeLevelRequest::class, 'name'],
    'grade level description' => [StoreGradeLevelRequest::class, 'description'],
    'classroom name' => [StoreClassroomRequest::class, 'name'],
    'exam name' => [StoreExamRequest::class, 'name'],
    'assignment title' => [StoreAssignmentRequest::class, 'title'],
    'news title' => [StoreNewsRequest::class, 'title'],
    'news body' => [StoreNewsRequest::class, 'body'],
]);

/*
|--------------------------------------------------------------------------
| Fixed domains are whitelists, not free text
|--------------------------------------------------------------------------
*/

test('enums expose Vuetify-shaped options with translated labels', function (string $enum) {
    $options = $enum::selectOptions();

    expect($options)->not->toBeEmpty();

    foreach ($options as $option) {
        expect($option)->toHaveKeys(['title', 'value'])
            // An untranslated key would render as "guardian::guardian.relationships.father".
            ->and($option['title'])->not->toContain('::');
    }
})->with([
    Gender::class,
    BloodGroup::class,
    RelationshipType::class,
    RoomType::class,
    ExamType::class,
    AssignmentType::class,
    BehaviorType::class,
]);

test('the exam type dropdown still offers every value the old hard-coded list did', function () {
    // The form kept its own list; dropping one of these would silently
    // invalidate exams that had already been created with it.
    expect(ExamType::values())
        ->toContain('midterm', 'final', 'quiz', 'unit_test');
});

test('a blood group outside the eight ABO/Rh groups has no enum case', function () {
    expect(BloodGroup::tryFrom('A++'))->toBeNull()
        ->and(BloodGroup::tryFrom('A+'))->not->toBeNull()
        ->and(BloodGroup::values())->toHaveCount(8);
});

/*
|--------------------------------------------------------------------------
| Blank optional inputs do not defeat their own rules
|--------------------------------------------------------------------------
|
| An HTML form submits "" for an untouched optional input. Without
| normalisation `['nullable', 'regex:...']` runs the regex against "" and
| fails, so a field the user never touched blocks the save.
*/

test('blank strings are normalised to null before validation', function () {
    $request = StoreStudentRequest::create('/', 'POST', [
        'name' => '  Sara Ahmed  ',
        'phone' => '',
        'guardian' => ['relation' => '  '],
    ]);

    $method = new ReflectionMethod($request, 'prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($request);

    expect($request->input('name'))->toBe('Sara Ahmed')
        ->and($request->input('phone'))->toBeNull()
        // Nested rows (invoice items, attendance notes) get the same treatment.
        ->and($request->input('guardian.relation'))->toBeNull();
});
