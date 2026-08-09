<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Modules\Academic\Models\AcademicYear;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('lists academic years', function (): void {
    AcademicYear::factory()->count(3)->create();

    $this->get(route('academic.academic-years.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Academic::AcademicYears/Index')
            ->has('years.data', 3));
});

it('requires authentication', function (): void {
    auth()->logout();

    $this->get(route('academic.academic-years.index'))
        ->assertRedirect(route('login'));
});

it('creates an academic year', function (): void {
    $this->post(route('academic.academic-years.store'), [
        'name' => '2025/2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-06-30',
    ])->assertRedirect();

    // Flags must fall back to their defaults, not NULL, when omitted.
    $year = AcademicYear::firstWhere('name', '2025/2026');
    expect($year->is_active)->toBeTrue()
        ->and($year->is_current)->toBeFalse();
});

it('rejects an end date on or before the start date', function (): void {
    $this->post(route('academic.academic-years.store'), [
        'name' => 'Bad range',
        'start_date' => '2026-06-30',
        'end_date' => '2025-09-01',
    ])->assertSessionHasErrors('end_date');
});

it('keeps exactly one year current when switching', function (): void {
    $old = AcademicYear::factory()->current()->create();
    $new = AcademicYear::factory()->create();

    $this->patch(route('academic.academic-years.set-current', $new))
        ->assertRedirect();

    expect(AcademicYear::current()->count())->toBe(1)
        ->and($new->refresh()->is_current)->toBeTrue()
        ->and($old->refresh()->is_current)->toBeFalse();
});

it('will not make an inactive year current', function (): void {
    $year = AcademicYear::factory()->inactive()->create();

    $this->patch(route('academic.academic-years.set-current', $year))
        ->assertStatus(400);

    expect($year->refresh()->is_current)->toBeFalse();
});

it('preserves untouched fields on a partial update', function (): void {
    $year = AcademicYear::factory()->current()->create(['name' => 'Original']);

    $this->put(route('academic.academic-years.update', $year), ['name' => 'Renamed'])
        ->assertRedirect();

    expect($year->refresh()->name)->toBe('Renamed')
        ->and($year->is_current)->toBeTrue()
        ->and($year->is_active)->toBeTrue();
});

it('allows a year to be renamed on update', function (): void {
    $year = AcademicYear::factory()->create();

    $this->put(route('academic.academic-years.update', $year), [
        'name' => 'Renamed',
    ])->assertRedirect()->assertSessionHasNoErrors();
});

it('refuses to delete the current year', function (): void {
    $year = AcademicYear::factory()->current()->create();

    $this->delete(route('academic.academic-years.destroy', $year))
        ->assertStatus(400);

    expect(AcademicYear::whereKey($year->getKey())->exists())->toBeTrue();
});

it('soft deletes a non-current year', function (): void {
    $year = AcademicYear::factory()->create();

    $this->delete(route('academic.academic-years.destroy', $year))
        ->assertRedirect();

    expect(AcademicYear::whereKey($year->getKey())->exists())->toBeFalse()
        ->and(AcademicYear::withTrashed()->whereKey($year->getKey())->exists())->toBeTrue();
});

it('searches by name', function (): void {
    AcademicYear::factory()->create(['name' => 'Findable']);
    AcademicYear::factory()->create(['name' => 'Other']);

    $this->get(route('academic.academic-years.index', ['search' => 'Findable']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->has('years.data', 1));

    $this->get(route('academic.academic-years.index', ['search' => 'XYZ']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->has('years.data', 1));
});

it('caps per_page so a client cannot request the whole table', function (): void {
    AcademicYear::factory()->count(20)->create();

    $this->get(route('academic.academic-years.index', ['per_page' => 10_000]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('years.meta.per_page', 100));
});
