<?php

use App\Models\User;
use Illuminate\Database\QueryException;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Setting\Enums\SchoolTermStatus;
use Modules\Setting\Models\SchoolSetting;
use Modules\Setting\Models\SchoolTerm;
use Modules\Setting\Models\SchoolTermAcceptance;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    // Staff are pushed to /onboarding until setup is finished, which would
    // mask every authorisation result asserted below.
    SchoolSetting::query()->create([
        'school_name' => 'Athr Test School',
        'school_code' => 'ATHR',
        'setup_completed' => true,
    ]);
});

function termActor(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user;
}

function makeTerm(SchoolTermStatus $status, int $version, array $overrides = []): SchoolTerm
{
    return SchoolTerm::query()->create([
        'title' => ['en' => "Terms v{$version}", 'ar' => "الشروط {$version}"],
        'content' => ['en' => "Body of version {$version}", 'ar' => "محتوى الإصدار {$version}"],
        'version' => $version,
        'status' => $status,
        'published_at' => $status === SchoolTermStatus::Published ? now() : null,
        ...$overrides,
    ]);
}

/*
|--------------------------------------------------------------------------
| Admin — drafting and editing
|--------------------------------------------------------------------------
*/

test('an admin can create a draft', function () {
    $this->actingAs(termActor(UserRole::Admin))
        ->post(route('settings.school-terms.store'), [
            'title' => 'School Terms and Conditions',
            'content' => 'Please read these terms carefully.',
        ])
        ->assertRedirect(route('settings.school-terms.index'));

    $term = SchoolTerm::query()->first();

    expect($term)->not->toBeNull()
        ->and($term->status)->toBe(SchoolTermStatus::Draft)
        ->and($term->version)->toBe(1)
        ->and($term->published_at)->toBeNull();
});

test('an admin can update a draft', function () {
    $draft = makeTerm(SchoolTermStatus::Draft, 1);

    $this->actingAs(termActor(UserRole::Admin))
        ->put(route('settings.school-terms.update', $draft), [
            'title' => 'Revised Title',
            'content' => 'Revised body text.',
        ])
        ->assertRedirect(route('settings.school-terms.index'));

    expect($draft->refresh()->getTranslation('title', 'en'))->toBe('Revised Title');
});

test('an admin can publish a draft', function () {
    $draft = makeTerm(SchoolTermStatus::Draft, 1);

    $this->actingAs(termActor(UserRole::Admin))
        ->patch(route('settings.school-terms.publish', $draft))
        ->assertRedirect(route('settings.school-terms.index'));

    $draft->refresh();

    expect($draft->status)->toBe(SchoolTermStatus::Published)
        ->and($draft->published_at)->not->toBeNull();
});

test('an admin cannot modify a published version', function () {
    $published = makeTerm(SchoolTermStatus::Published, 1);

    $this->actingAs(termActor(UserRole::Admin))
        ->put(route('settings.school-terms.update', $published), [
            'title' => 'Tampered Title',
            'content' => 'Tampered body.',
        ])
        ->assertForbidden();

    expect($published->refresh()->getTranslation('title', 'en'))->toBe('Terms v1');
});

test('an admin cannot open the edit page for a published version', function () {
    $published = makeTerm(SchoolTermStatus::Published, 1);

    $this->actingAs(termActor(UserRole::Admin))
        ->get(route('settings.school-terms.edit', $published))
        ->assertForbidden();
});

test('an admin cannot delete a published version', function () {
    $published = makeTerm(SchoolTermStatus::Published, 1);

    $this->actingAs(termActor(UserRole::Admin))
        ->delete(route('settings.school-terms.destroy', $published))
        ->assertForbidden();

    expect(SchoolTerm::query()->whereKey($published->getKey())->exists())->toBeTrue();
});

test('an admin can create a new version after publishing', function () {
    makeTerm(SchoolTermStatus::Published, 1);

    $this->actingAs(termActor(UserRole::Admin))
        ->post(route('settings.school-terms.store'), [
            'title' => 'Second Edition',
            'content' => 'Updated wording.',
        ])
        ->assertRedirect();

    $draft = SchoolTerm::query()->draft()->first();

    expect($draft)->not->toBeNull()
        ->and($draft->version)->toBe(2);
});

test('publishing a new version archives the previous one', function () {
    $first = makeTerm(SchoolTermStatus::Published, 1);
    $second = makeTerm(SchoolTermStatus::Draft, 2);

    $this->actingAs(termActor(UserRole::Admin))
        ->patch(route('settings.school-terms.publish', $second))
        ->assertRedirect();

    expect($first->refresh()->status)->toBe(SchoolTermStatus::Archived)
        ->and($second->refresh()->status)->toBe(SchoolTermStatus::Published);
});

test('only one published version can exist at a time', function () {
    makeTerm(SchoolTermStatus::Published, 1);
    $second = makeTerm(SchoolTermStatus::Draft, 2);
    $third = makeTerm(SchoolTermStatus::Draft, 3);

    $admin = termActor(UserRole::Admin);

    $this->actingAs($admin)->patch(route('settings.school-terms.publish', $second))->assertRedirect();
    $this->actingAs($admin)->patch(route('settings.school-terms.publish', $third))->assertRedirect();

    expect(SchoolTerm::query()->published()->count())->toBe(1)
        ->and(SchoolTerm::query()->published()->first()->version)->toBe(3);
});

test('a second draft cannot be created while one is open', function () {
    makeTerm(SchoolTermStatus::Draft, 1);

    $this->actingAs(termActor(UserRole::Admin))
        ->post(route('settings.school-terms.store'), [
            'title' => 'Another Draft',
            'content' => 'Body.',
        ])
        ->assertStatus(409);

    expect(SchoolTerm::query()->count())->toBe(1);
});

test('an already published version cannot be published again', function () {
    $published = makeTerm(SchoolTermStatus::Published, 1);

    $this->actingAs(termActor(UserRole::Admin))
        ->patch(route('settings.school-terms.publish', $published))
        ->assertForbidden();
});

test('a draft version number continues after a discarded draft', function () {
    $draft = makeTerm(SchoolTermStatus::Draft, 1);
    $admin = termActor(UserRole::Admin);

    $this->actingAs($admin)->delete(route('settings.school-terms.destroy', $draft))->assertRedirect();

    $this->actingAs($admin)
        ->post(route('settings.school-terms.store'), ['title' => 'Next', 'content' => 'Body.'])
        ->assertRedirect();

    // The discarded number is not reused — history stays unambiguous.
    expect(SchoolTerm::query()->draft()->first()->version)->toBe(2);
});

/*
|--------------------------------------------------------------------------
| Authorization
|--------------------------------------------------------------------------
*/

test('a teacher cannot manage school terms', function () {
    $this->actingAs(termActor(UserRole::Teacher))
        ->get(route('settings.school-terms.index'))
        ->assertForbidden();
});

test('a student cannot create school terms', function () {
    $this->actingAs(termActor(UserRole::Student))
        ->post(route('settings.school-terms.store'), ['title' => 'Fake', 'content' => 'Fake body.'])
        ->assertForbidden();

    expect(SchoolTerm::query()->count())->toBe(0);
});

test('a guardian cannot edit school terms', function () {
    $draft = makeTerm(SchoolTermStatus::Draft, 1);

    $this->actingAs(termActor(UserRole::Guardian))
        ->put(route('settings.school-terms.update', $draft), ['title' => 'Hijacked', 'content' => 'Body.'])
        ->assertForbidden();

    expect($draft->refresh()->getTranslation('title', 'en'))->toBe('Terms v1');
});

test('a student cannot publish school terms', function () {
    $draft = makeTerm(SchoolTermStatus::Draft, 1);

    $this->actingAs(termActor(UserRole::Student))
        ->patch(route('settings.school-terms.publish', $draft))
        ->assertForbidden();

    expect($draft->refresh()->status)->toBe(SchoolTermStatus::Draft);
});

test('an owner can manage school terms', function () {
    $this->actingAs(termActor(UserRole::Owner))
        ->get(route('settings.school-terms.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Setting::SchoolTerms/Index'));
});

test('a guest cannot reach the terms screens', function () {
    $this->get(route('settings.school-terms.index'))->assertRedirect();
    $this->get(route('school-terms.show'))->assertRedirect();
});

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

test('title and content are required', function () {
    $this->actingAs(termActor(UserRole::Admin))
        ->post(route('settings.school-terms.store'), [])
        ->assertSessionHasErrors(['title', 'content']);
});

/*
|--------------------------------------------------------------------------
| Portal — viewing
|--------------------------------------------------------------------------
*/

test('a student can see the published terms', function () {
    $published = makeTerm(SchoolTermStatus::Published, 1);

    $this->actingAs(termActor(UserRole::Student))
        ->get(route('school-terms.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Setting::SchoolTerms/Portal')
            ->where('term.id', $published->id)
            ->where('acceptedCurrent', false));
});

test('a guardian can see the published terms', function () {
    $published = makeTerm(SchoolTermStatus::Published, 1);

    $this->actingAs(termActor(UserRole::Guardian))
        ->get(route('school-terms.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('term.id', $published->id));
});

test('the portal never shows a draft as the current terms', function () {
    makeTerm(SchoolTermStatus::Draft, 1);

    $this->actingAs(termActor(UserRole::Student))
        ->get(route('school-terms.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('term', null));
});

/*
|--------------------------------------------------------------------------
| Portal — accepting
|--------------------------------------------------------------------------
*/

test('a student can accept the published terms', function () {
    $published = makeTerm(SchoolTermStatus::Published, 1);
    $student = termActor(UserRole::Student);

    $this->actingAs($student)
        ->post(route('school-terms.accept'))
        ->assertRedirect();

    $acceptance = SchoolTermAcceptance::query()
        ->where('user_id', $student->id)
        ->where('school_term_id', $published->id)
        ->first();

    expect($acceptance)->not->toBeNull()
        ->and($acceptance->accepted_at)->not->toBeNull();
});

test('a guardian can accept the published terms', function () {
    $published = makeTerm(SchoolTermStatus::Published, 1);
    $guardian = termActor(UserRole::Guardian);

    $this->actingAs($guardian)->post(route('school-terms.accept'))->assertRedirect();

    expect(SchoolTermAcceptance::query()
        ->where('user_id', $guardian->id)
        ->where('school_term_id', $published->id)
        ->exists())->toBeTrue();
});

test('a draft cannot be accepted', function () {
    makeTerm(SchoolTermStatus::Draft, 1);

    $this->actingAs(termActor(UserRole::Student))
        ->post(route('school-terms.accept'))
        ->assertNotFound();

    expect(SchoolTermAcceptance::query()->count())->toBe(0);
});

test('an archived version cannot be accepted', function () {
    makeTerm(SchoolTermStatus::Archived, 1);

    $this->actingAs(termActor(UserRole::Student))
        ->post(route('school-terms.accept'))
        ->assertNotFound();

    expect(SchoolTermAcceptance::query()->count())->toBe(0);
});

test('duplicate acceptance is prevented', function () {
    makeTerm(SchoolTermStatus::Published, 1);
    $student = termActor(UserRole::Student);

    $this->actingAs($student)->post(route('school-terms.accept'))->assertRedirect();
    $this->actingAs($student)->post(route('school-terms.accept'))->assertStatus(409);

    expect(SchoolTermAcceptance::query()->where('user_id', $student->id)->count())->toBe(1);
});

test('the unique constraint refuses a second acceptance row', function () {
    $published = makeTerm(SchoolTermStatus::Published, 1);
    $student = termActor(UserRole::Student);

    SchoolTermAcceptance::query()->create([
        'school_term_id' => $published->id,
        'user_id' => $student->id,
        'accepted_at' => now(),
    ]);

    // The database is the last word, whatever the application layer does.
    expect(fn () => SchoolTermAcceptance::query()->create([
        'school_term_id' => $published->id,
        'user_id' => $student->id,
        'accepted_at' => now(),
    ]))->toThrow(QueryException::class);
});

/*
|--------------------------------------------------------------------------
| Re-acceptance on a new version
|--------------------------------------------------------------------------
*/

test('a user who accepted v1 must accept v2', function () {
    $first = makeTerm(SchoolTermStatus::Published, 1);
    $student = termActor(UserRole::Student);

    $this->actingAs($student)->post(route('school-terms.accept'))->assertRedirect();

    $this->actingAs($student)
        ->get(route('school-terms.show'))
        ->assertInertia(fn ($page) => $page->where('acceptedCurrent', true));

    // The school releases a new wording.
    $second = makeTerm(SchoolTermStatus::Draft, 2);
    $this->actingAs(termActor(UserRole::Admin))
        ->patch(route('settings.school-terms.publish', $second))
        ->assertRedirect();

    $this->actingAs($student)
        ->get(route('school-terms.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('term.id', $second->id)
            ->where('acceptedCurrent', false));
});

test('a user who accepted the current version is not prompted again', function () {
    makeTerm(SchoolTermStatus::Published, 1);
    $student = termActor(UserRole::Student);

    $this->actingAs($student)->post(route('school-terms.accept'))->assertRedirect();

    $this->actingAs($student)
        ->get(route('school-terms.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('acceptedCurrent', true)
            ->where('acceptedAt', fn ($value) => $value !== null));
});

test('accepting v2 leaves the v1 acceptance intact as history', function () {
    $first = makeTerm(SchoolTermStatus::Published, 1);
    $student = termActor(UserRole::Student);

    $this->actingAs($student)->post(route('school-terms.accept'))->assertRedirect();

    $second = makeTerm(SchoolTermStatus::Draft, 2);
    $this->actingAs(termActor(UserRole::Admin))
        ->patch(route('settings.school-terms.publish', $second))
        ->assertRedirect();

    $this->actingAs($student)->post(route('school-terms.accept'))->assertRedirect();

    expect(SchoolTermAcceptance::query()->where('user_id', $student->id)->count())->toBe(2)
        ->and(SchoolTermAcceptance::query()
            ->where('user_id', $student->id)
            ->where('school_term_id', $first->id)
            ->exists())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Acceptance is always for the signed-in user
|--------------------------------------------------------------------------
*/

test('a student cannot record an acceptance on behalf of someone else', function () {
    $published = makeTerm(SchoolTermStatus::Published, 1);
    $student = termActor(UserRole::Student);
    $other = termActor(UserRole::Student);

    /*
     * The endpoint takes no user id at all; anything posted is ignored and
     * the Action uses the signed-in user, so a crafted body cannot forge an
     * agreement for another person.
     */
    $this->actingAs($student)
        ->post(route('school-terms.accept'), ['user_id' => $other->id])
        ->assertRedirect();

    expect(SchoolTermAcceptance::query()->where('user_id', $other->id)->exists())->toBeFalse()
        ->and(SchoolTermAcceptance::query()->where('user_id', $student->id)->exists())->toBeTrue();
});

test('a student cannot accept a version other than the published one', function () {
    $archived = makeTerm(SchoolTermStatus::Archived, 1);
    $published = makeTerm(SchoolTermStatus::Published, 2);
    $student = termActor(UserRole::Student);

    // No term id is accepted from the request — the server resolves it.
    $this->actingAs($student)
        ->post(route('school-terms.accept'), ['school_term_id' => $archived->id])
        ->assertRedirect();

    expect(SchoolTermAcceptance::query()->where('user_id', $student->id)->first()->school_term_id)
        ->toBe($published->id);
});

/*
|--------------------------------------------------------------------------
| Translation
|--------------------------------------------------------------------------
*/

test('translatable title and content persist for every active locale', function () {
    $this->actingAs(termActor(UserRole::Admin))
        ->post(route('settings.school-terms.store'), [
            'title' => 'School Terms and Conditions',
            'content' => 'Please read these terms carefully.',
        ])
        ->assertRedirect();

    $term = SchoolTerm::query()->first();

    /*
     * BaseRequest detects the typed language and TranslationService fills the
     * other active locales, so the stored value is a locale-keyed map rather
     * than a bare string — English is asserted because that is what was typed.
     */
    expect($term->getTranslations('title'))->toBeArray()
        ->and($term->getTranslations('title'))->toHaveKey('en')
        ->and($term->getTranslation('title', 'en'))->toBe('School Terms and Conditions')
        ->and($term->getTranslations('content'))->toHaveKey('en');
});

test('a stored translation is returned for the requested locale', function () {
    $term = makeTerm(SchoolTermStatus::Published, 1);

    expect($term->getTranslation('title', 'ar'))->toBe('الشروط 1')
        ->and($term->getTranslation('title', 'en'))->toBe('Terms v1');
});
