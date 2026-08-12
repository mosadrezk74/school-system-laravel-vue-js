<?php

use App\Models\User;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\News\Enums\NewsAudience;
use Modules\News\Enums\NewsStatus;
use Modules\News\Models\News;
use Modules\Setting\Models\SchoolSetting;
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

function newsActor(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user;
}

function makeNews(
    NewsStatus $status = NewsStatus::Published,
    NewsAudience $audience = NewsAudience::All,
    ?string $publishedAt = 'now',
    bool $pinned = false,
    string $title = 'Sports Day',
): News {
    return News::query()->create([
        'title' => ['en' => $title, 'ar' => 'خبر'],
        'body' => ['en' => "Body of {$title}", 'ar' => 'المحتوى'],
        'status' => $status,
        'audience' => $audience,
        'published_at' => match ($publishedAt) {
            null => null,
            'now' => now()->subMinute(),
            'future' => now()->addWeek(),
            default => $publishedAt,
        },
        'is_pinned' => $pinned,
    ]);
}

/**
 * @return array<int, int>
 */
function feedIds(array $props): array
{
    return collect($props['news']['data'])->pluck('id')->all();
}

/*
|--------------------------------------------------------------------------
| Admin CRUD
|--------------------------------------------------------------------------
*/

test('an admin can list news', function () {
    makeNews();

    $this->actingAs(newsActor(UserRole::Admin))
        ->get(route('news.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('News::News/Index')->has('news.data', 1));
});

test('an admin can create a news item', function () {
    $this->actingAs(newsActor(UserRole::Admin))
        ->post(route('news.store'), [
            'title' => 'Parents Evening',
            'body' => 'Please join us on Thursday.',
            'audience' => 'guardians',
            'status' => 'draft',
        ])
        ->assertRedirect(route('news.index'));

    $item = News::query()->first();

    expect($item)->not->toBeNull()
        ->and($item->status)->toBe(NewsStatus::Draft)
        ->and($item->audience)->toBe(NewsAudience::Guardians)
        ->and($item->published_at)->toBeNull();
});

test('a created item records its author and derives a slug', function () {
    $admin = newsActor(UserRole::Admin);

    $this->actingAs($admin)
        ->post(route('news.store'), [
            'title' => 'Sports Day',
            'body' => 'Body.',
            'audience' => 'all',
            'status' => 'draft',
        ])
        ->assertRedirect();

    $item = News::query()->first();

    expect($item->author_id)->toBe($admin->id)
        ->and($item->slug)->toBe('sports-day');
});

test('a duplicate title still produces a unique slug', function () {
    makeNews(title: 'Sports Day');

    $this->actingAs(newsActor(UserRole::Admin))
        ->post(route('news.store'), [
            'title' => 'Sports Day',
            'body' => 'Body.',
            'audience' => 'all',
            'status' => 'draft',
        ])
        ->assertRedirect();

    expect(News::query()->pluck('slug')->unique())->toHaveCount(2);
});

test('an admin can update a news item', function () {
    $item = makeNews(NewsStatus::Draft, publishedAt: null);

    $this->actingAs(newsActor(UserRole::Admin))
        ->put(route('news.update', $item), [
            'title' => 'Revised Title',
            'body' => 'Revised body.',
            'audience' => 'teachers',
            'status' => 'draft',
        ])
        ->assertRedirect(route('news.index'));

    $item->refresh();

    expect($item->getTranslation('title', 'en'))->toBe('Revised Title')
        ->and($item->audience)->toBe(NewsAudience::Teachers);
});

test('an admin can delete a news item', function () {
    $item = makeNews();

    $this->actingAs(newsActor(UserRole::Admin))
        ->delete(route('news.destroy', $item))
        ->assertRedirect(route('news.index'));

    expect(News::query()->whereKey($item->id)->exists())->toBeFalse()
        ->and(News::withTrashed()->whereKey($item->id)->exists())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Publishing and pinning
|--------------------------------------------------------------------------
*/

test('publishing a draft sets a publication date', function () {
    $item = makeNews(NewsStatus::Draft, publishedAt: null);

    $this->actingAs(newsActor(UserRole::Admin))
        ->patch(route('news.publish', $item))
        ->assertRedirect();

    $item->refresh();

    expect($item->status)->toBe(NewsStatus::Published)
        ->and($item->published_at)->not->toBeNull();
});

test('publishing an already published item is refused', function () {
    $item = makeNews();

    $this->actingAs(newsActor(UserRole::Admin))
        ->patch(route('news.publish', $item))
        ->assertStatus(409);
});

test('unpublishing returns an item to draft and clears its date', function () {
    $item = makeNews();

    $this->actingAs(newsActor(UserRole::Admin))
        ->patch(route('news.unpublish', $item))
        ->assertRedirect();

    $item->refresh();

    expect($item->status)->toBe(NewsStatus::Draft)
        ->and($item->published_at)->toBeNull();
});

test('unpublishing a draft is refused', function () {
    $item = makeNews(NewsStatus::Draft, publishedAt: null);

    $this->actingAs(newsActor(UserRole::Admin))
        ->patch(route('news.unpublish', $item))
        ->assertStatus(409);
});

test('an admin can pin and unpin an item', function () {
    $item = makeNews();
    $admin = newsActor(UserRole::Admin);

    $this->actingAs($admin)
        ->patch(route('news.pin', $item), ['is_pinned' => true])
        ->assertRedirect();

    expect($item->fresh()->is_pinned)->toBeTrue();

    $this->actingAs($admin)
        ->patch(route('news.pin', $item), ['is_pinned' => false])
        ->assertRedirect();

    expect($item->fresh()->is_pinned)->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Feed visibility
|--------------------------------------------------------------------------
*/

test('a draft never appears in the feed', function () {
    $draft = makeNews(NewsStatus::Draft, publishedAt: null);

    $this->actingAs(newsActor(UserRole::Student))
        ->get(route('news.feed'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(feedIds($page->toArray()['props']))
            ->not->toContain($draft->id));
});

test('an item dated in the future does not appear yet', function () {
    $scheduled = makeNews(publishedAt: 'future');

    $this->actingAs(newsActor(UserRole::Student))
        ->get(route('news.feed'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(feedIds($page->toArray()['props']))
            ->not->toContain($scheduled->id));
});

test('a published item appears in the feed', function () {
    $live = makeNews();

    $this->actingAs(newsActor(UserRole::Student))
        ->get(route('news.feed'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(feedIds($page->toArray()['props']))
            ->toContain($live->id));
});

test('pinned items are listed first', function () {
    makeNews(title: 'Ordinary');
    $pinned = makeNews(pinned: true, title: 'Pinned');

    $this->actingAs(newsActor(UserRole::Student))
        ->get(route('news.feed'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(feedIds($page->toArray()['props'])[0])
            ->toBe($pinned->id));
});

/*
|--------------------------------------------------------------------------
| Audience filtering — enforced server-side
|--------------------------------------------------------------------------
*/

test('a student does not see guardian-only news', function () {
    $guardianOnly = makeNews(audience: NewsAudience::Guardians);
    $forAll = makeNews(audience: NewsAudience::All);

    $this->actingAs(newsActor(UserRole::Student))
        ->get(route('news.feed'))
        ->assertOk()
        ->assertInertia(function ($page) use ($guardianOnly, $forAll) {
            $ids = feedIds($page->toArray()['props']);

            expect($ids)->not->toContain($guardianOnly->id)
                ->and($ids)->toContain($forAll->id);
        });
});

test('a guardian does not see teacher-only news', function () {
    $teacherOnly = makeNews(audience: NewsAudience::Teachers);
    $forGuardians = makeNews(audience: NewsAudience::Guardians);

    $this->actingAs(newsActor(UserRole::Guardian))
        ->get(route('news.feed'))
        ->assertOk()
        ->assertInertia(function ($page) use ($teacherOnly, $forGuardians) {
            $ids = feedIds($page->toArray()['props']);

            expect($ids)->not->toContain($teacherOnly->id)
                ->and($ids)->toContain($forGuardians->id);
        });
});

test('a teacher does not see student-only news', function () {
    $studentOnly = makeNews(audience: NewsAudience::Students);
    $forTeachers = makeNews(audience: NewsAudience::Teachers);

    $this->actingAs(newsActor(UserRole::Teacher))
        ->get(route('news.feed'))
        ->assertOk()
        ->assertInertia(function ($page) use ($studentOnly, $forTeachers) {
            $ids = feedIds($page->toArray()['props']);

            expect($ids)->not->toContain($studentOnly->id)
                ->and($ids)->toContain($forTeachers->id);
        });
});

test('a student sees news addressed to students', function () {
    $studentOnly = makeNews(audience: NewsAudience::Students);

    $this->actingAs(newsActor(UserRole::Student))
        ->get(route('news.feed'))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(feedIds($page->toArray()['props']))
            ->toContain($studentOnly->id));
});

test('an audience cannot be widened through a query parameter', function () {
    $guardianOnly = makeNews(audience: NewsAudience::Guardians);

    /*
     * The feed takes no audience input at all — it is derived from the
     * signed-in user — so a crafted parameter changes nothing.
     */
    $this->actingAs(newsActor(UserRole::Student))
        ->get(route('news.feed', ['audience' => 'guardians', 'status' => 'draft']))
        ->assertOk()
        ->assertInertia(fn ($page) => expect(feedIds($page->toArray()['props']))
            ->not->toContain($guardianOnly->id));
});

test('staff see every audience in the feed', function () {
    $studentOnly = makeNews(audience: NewsAudience::Students);
    $guardianOnly = makeNews(audience: NewsAudience::Guardians);

    $this->actingAs(newsActor(UserRole::Admin))
        ->get(route('news.feed'))
        ->assertOk()
        ->assertInertia(function ($page) use ($studentOnly, $guardianOnly) {
            $ids = feedIds($page->toArray()['props']);

            expect($ids)->toContain($studentOnly->id)
                ->and($ids)->toContain($guardianOnly->id);
        });
});

/*
|--------------------------------------------------------------------------
| Authorization
|--------------------------------------------------------------------------
*/

test('a student cannot open the admin news screen', function () {
    $this->actingAs(newsActor(UserRole::Student))
        ->get(route('news.index'))
        ->assertForbidden();
});

test('a guardian cannot create news', function () {
    $this->actingAs(newsActor(UserRole::Guardian))
        ->post(route('news.store'), [
            'title' => 'Fake', 'body' => 'Fake.', 'audience' => 'all', 'status' => 'published',
        ])
        ->assertForbidden();

    expect(News::query()->count())->toBe(0);
});

test('a teacher cannot create or publish news', function () {
    $item = makeNews(NewsStatus::Draft, publishedAt: null);
    $teacher = newsActor(UserRole::Teacher);

    $this->actingAs($teacher)->get(route('news.index'))->assertForbidden();
    $this->actingAs($teacher)->patch(route('news.publish', $item))->assertForbidden();

    expect($item->fresh()->status)->toBe(NewsStatus::Draft);
});

test('an owner can manage news', function () {
    $this->actingAs(newsActor(UserRole::Owner))
        ->get(route('news.index'))
        ->assertOk();
});

test('a guest cannot reach the feed or the admin screen', function () {
    $this->get(route('news.feed'))->assertRedirect();
    $this->get(route('news.index'))->assertRedirect();
});

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

test('title and body are required', function () {
    $this->actingAs(newsActor(UserRole::Admin))
        ->post(route('news.store'), ['audience' => 'all', 'status' => 'draft'])
        ->assertSessionHasErrors(['title', 'body']);
});

test('an unknown audience is rejected', function () {
    $this->actingAs(newsActor(UserRole::Admin))
        ->post(route('news.store'), [
            'title' => 'X', 'body' => 'Y', 'audience' => 'martians', 'status' => 'draft',
        ])
        ->assertSessionHasErrors('audience');
});

test('an unknown status is rejected', function () {
    $this->actingAs(newsActor(UserRole::Admin))
        ->post(route('news.store'), [
            'title' => 'X', 'body' => 'Y', 'audience' => 'all', 'status' => 'archived',
        ])
        ->assertSessionHasErrors('status');
});

/*
|--------------------------------------------------------------------------
| Translation
|--------------------------------------------------------------------------
*/

test('translatable title and body persist per locale', function () {
    $this->actingAs(newsActor(UserRole::Admin))
        ->post(route('news.store'), [
            'title' => 'Parents Evening',
            'body' => 'Please join us on Thursday.',
            'audience' => 'all',
            'status' => 'draft',
        ])
        ->assertRedirect();

    $item = News::query()->first();

    expect($item->getTranslations('title'))->toBeArray()
        ->and($item->getTranslations('title'))->toHaveKey('en')
        ->and($item->getTranslation('title', 'en'))->toBe('Parents Evening')
        ->and($item->getTranslations('body'))->toHaveKey('en');
});

test('a stored translation is returned for the requested locale', function () {
    $item = makeNews(title: 'Sports Day');

    expect($item->getTranslation('title', 'en'))->toBe('Sports Day')
        ->and($item->getTranslation('title', 'ar'))->toBe('خبر');
});
