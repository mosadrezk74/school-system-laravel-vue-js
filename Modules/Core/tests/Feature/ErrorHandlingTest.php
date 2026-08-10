<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Models\ErrorReport;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function errorTestUser(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $user->assignRole($role->value);

    return $user;
}

/*
 * A route that blows up on purpose. Registered per-test so the failure is
 * genuinely raised through the framework's exception pipeline rather than
 * simulated.
 */
function registerExplodingRoute(string $uri = '/boom'): void
{
    Route::middleware('web')->get($uri, function (): never {
        throw new RuntimeException('Database password is hunter2 at /var/secret/path.php');
    });
}

test('an unknown web route renders the custom 404 page', function () {
    $this->actingAs(errorTestUser(UserRole::Admin))
        ->get('/a-route-that-does-not-exist')
        ->assertStatus(404)
        ->assertInertia(fn ($page) => $page
            ->component('Errors/ErrorPage')
            ->where('status', 404)
            ->where('statusKey', '404')
        );
});

test('a guest hitting an unknown route also gets the custom 404 page', function () {
    $this->get('/no-such-page-here')
        ->assertStatus(404)
        ->assertInertia(fn ($page) => $page->component('Errors/ErrorPage')->where('status', 404));
});

test('the 404 page carries copy in the active locale', function () {
    app()->setLocale('ar');

    $this->get('/missing-in-arabic')
        ->assertStatus(404)
        ->assertInertia(fn ($page) => $page
            ->where('title', 'الصفحة غير موجودة')
            ->where('description', 'يبدو أن الصفحة التي تبحث عنها غير موجودة أو تم نقلها.')
        );
});

test('a server error shows the friendly page and never leaks exception details', function () {
    config()->set('app.debug', false);
    registerExplodingRoute();

    $response = $this->actingAs(errorTestUser(UserRole::Teacher))->get('/boom');

    $response->assertStatus(500)
        ->assertInertia(fn ($page) => $page->component('Errors/ErrorPage')->where('status', 500));

    $body = $response->getContent();

    expect($body)->not->toContain('hunter2')
        ->and($body)->not->toContain('/var/secret/path.php')
        ->and($body)->not->toContain('RuntimeException');
});

test('a forbidden action renders the 403 page rather than a raw error', function () {
    config()->set('app.debug', false);

    Route::middleware('web')->get('/forbidden-area', function (): never {
        abort(403);
    });

    $this->actingAs(errorTestUser(UserRole::Teacher))
        ->get('/forbidden-area')
        ->assertStatus(403)
        ->assertInertia(fn ($page) => $page->component('Errors/ErrorPage')->where('status', 403));
});

test('validation still behaves normally and is not turned into an error page', function () {
    $admin = errorTestUser(UserRole::Admin);

    $this->actingAs($admin)
        ->post(route('subjects.store'), ['name' => []])
        ->assertSessionHasErrors();
});

test('api requests receive json for a 404, never html', function () {
    $response = $this->getJson('/api/v1/does-not-exist');

    $response->assertStatus(404)
        ->assertJson([
            'status' => 'error',
            'error_code' => 'NOT_FOUND',
        ]);

    expect($response->headers->get('content-type'))->toContain('application/json');
});

test('api requests receive json for a server error without leaking internals', function () {
    config()->set('app.debug', false);

    Route::middleware('api')->get('/api/v1/explode', function (): never {
        throw new RuntimeException('Secret token abc123 leaked from /var/www/app.php');
    });

    $response = $this->getJson('/api/v1/explode');

    $response->assertStatus(500)
        ->assertJson([
            'status' => 'error',
            'error_code' => 'INTERNAL_SERVER_ERROR',
        ]);

    $body = $response->getContent();

    expect($body)->not->toContain('abc123')
        ->and($body)->not->toContain('/var/www/app.php')
        ->and($body)->not->toContain('RuntimeException');
});

test('an unexpected error is recorded for the admin error centre', function () {
    registerExplodingRoute('/boom-recorded');

    $this->actingAs(errorTestUser(UserRole::Admin))->get('/boom-recorded');

    $report = ErrorReport::query()->latest('id')->first();

    expect($report)->not->toBeNull()
        ->and($report->exception)->toBe(RuntimeException::class)
        ->and($report->status_code)->toBe(500)
        ->and($report->error_code)->toBe('INTERNAL_SERVER_ERROR')
        ->and($report->method)->toBe('GET');
});

test('expected client errors like a 404 are not recorded as incidents', function () {
    $this->get('/some-missing-page');

    expect(ErrorReport::query()->count())->toBe(0);
});

test('repeated identical failures collapse into one grouped report', function () {
    registerExplodingRoute('/boom-repeat');

    $admin = errorTestUser(UserRole::Admin);

    $this->actingAs($admin)->get('/boom-repeat');
    $this->actingAs($admin)->get('/boom-repeat');
    $this->actingAs($admin)->get('/boom-repeat');

    expect(ErrorReport::query()->count())->toBe(1)
        ->and(ErrorReport::query()->first()->occurrences)->toBe(3);
});

test('recorded reports never contain credentials or payment details', function () {
    config()->set('app.debug', false);

    Route::middleware('web')->post('/checkout-boom', function (): never {
        throw new RuntimeException('payment failed');
    });

    $this->actingAs(errorTestUser(UserRole::Admin))->post('/checkout-boom', [
        'password' => 'super-secret-pw',
        'card_number' => '4111111111111111',
        'cvv' => '123',
        'iban' => 'SA0380000000608010167519',
        'access_token' => 'tok_live_abcdef',
        'amount' => 500,
    ]);

    $stored = json_encode(ErrorReport::query()->latest('id')->first()?->toArray());

    expect($stored)->not->toContain('super-secret-pw')
        ->and($stored)->not->toContain('4111111111111111')
        ->and($stored)->not->toContain('tok_live_abcdef')
        ->and($stored)->not->toContain('SA0380000000608010167519')
        ->and($stored)->not->toContain('123');
});
