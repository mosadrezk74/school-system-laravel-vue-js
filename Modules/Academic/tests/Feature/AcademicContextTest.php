<?php

use Modules\Academic\Exceptions\AcademicYearNotSetException;
use Modules\Academic\Exceptions\SemesterNotSetException;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;

/**
 * Covers the "current year + current term" invariant: the observers keep
 * exactly one row flagged on each side, and AcademicContextService resolves
 * that pair (or fails loudly when it cannot).
 */
beforeEach(function () {
    AcademicContextService::flush();
});

it('demotes the previous current year when another is promoted', function () {
    $first = AcademicYear::factory()->current()->create();
    $second = AcademicYear::factory()->create();

    $second->update(['is_current' => true]);

    expect($first->refresh()->is_current)->toBeFalse()
        ->and($second->refresh()->is_current)->toBeTrue()
        ->and(AcademicYear::query()->where('is_current', true)->count())->toBe(1);
});

it('demotes the previous current year when a new one is created as current', function () {
    $first = AcademicYear::factory()->current()->create();

    AcademicYear::factory()->current()->create();

    expect($first->refresh()->is_current)->toBeFalse()
        ->and(AcademicYear::query()->where('is_current', true)->count())->toBe(1);
});

it('demotes the previous current semester when another is promoted', function () {
    $year = AcademicYear::factory()->current()->create();
    $first = Semester::factory()->current()->create(['academic_year_id' => $year->id]);
    $second = Semester::factory()->create(['academic_year_id' => $year->id]);

    $second->update(['is_current' => true]);

    expect($first->refresh()->is_current)->toBeFalse()
        ->and($second->refresh()->is_current)->toBeTrue()
        ->and(Semester::query()->where('is_current', true)->count())->toBe(1);
});

it('keeps only one current semester across different academic years', function () {
    $oldYear = AcademicYear::factory()->create();
    $oldTerm = Semester::factory()->current()->create(['academic_year_id' => $oldYear->id]);

    $newYear = AcademicYear::factory()->current()->create();
    Semester::factory()->current()->create(['academic_year_id' => $newYear->id]);

    expect($oldTerm->refresh()->is_current)->toBeFalse()
        ->and(Semester::query()->where('is_current', true)->count())->toBe(1);
});

it('does not demote anything when saving an unrelated field', function () {
    $year = AcademicYear::factory()->current()->create();
    $term = Semester::factory()->current()->create(['academic_year_id' => $year->id]);

    $term->update(['sequence' => 99]);

    expect($term->refresh()->is_current)->toBeTrue()
        ->and($year->refresh()->is_current)->toBeTrue();
});

it('does not demote another semester when only the name changes', function () {
    $year = AcademicYear::factory()->current()->create();
    $current = Semester::factory()->current()->create(['academic_year_id' => $year->id]);
    $other = Semester::factory()->create(['academic_year_id' => $year->id]);

    $other->update(['name' => ['en' => 'Renamed', 'ar' => 'معاد تسميته']]);

    expect($current->refresh()->is_current)->toBeTrue()
        ->and($other->refresh()->is_current)->toBeFalse();
});

it('does not demote another semester when only the dates change', function () {
    $year = AcademicYear::factory()->current()->create();
    $current = Semester::factory()->current()->create(['academic_year_id' => $year->id]);
    $other = Semester::factory()->create(['academic_year_id' => $year->id]);

    $other->update(['start_date' => now()->addYear()->toDateString()]);

    expect($current->refresh()->is_current)->toBeTrue();
});

it('clears the flag when the current semester is soft deleted', function () {
    $year = AcademicYear::factory()->current()->create();
    $term = Semester::factory()->current()->create(['academic_year_id' => $year->id]);

    $term->delete();

    /* Restoring must not resurrect a second current semester. */
    expect(Semester::withTrashed()->whereKey($term->getKey())->value('is_current'))
        ->toEqual(false);
});

it('does not leave two current semesters after restoring a deleted one', function () {
    $year = AcademicYear::factory()->current()->create();
    $first = Semester::factory()->current()->create(['academic_year_id' => $year->id]);

    $first->delete();
    $second = Semester::factory()->current()->create(['academic_year_id' => $year->id]);
    $first->restore();

    expect(Semester::query()->where('is_current', true)->count())->toBe(1)
        ->and($second->refresh()->is_current)->toBeTrue();
});

it('clears the flag when the current academic year is soft deleted', function () {
    $year = AcademicYear::factory()->current()->create();

    $year->delete();

    expect(AcademicYear::withTrashed()->whereKey($year->getKey())->value('is_current'))
        ->toEqual(false);
});

it('resolves the flagged current year and semester', function () {
    $year = AcademicYear::factory()->current()->create();
    $term = Semester::factory()->current()->create(['academic_year_id' => $year->id]);

    AcademicContextService::flush();
    $context = app(AcademicContextService::class);

    expect($context->currentYearId())->toBe($year->id)
        ->and($context->currentSemesterId())->toBe($term->id)
        ->and($context->requireContext())->toBe([
            'academic_year_id' => $year->id,
            'semester_id' => $term->id,
        ]);
});

it('falls back to the semester whose date range covers today', function () {
    $year = AcademicYear::factory()->current()->create();

    $past = Semester::factory()->create([
        'academic_year_id' => $year->id,
        'sequence' => 1,
        'start_date' => now()->subMonths(6)->toDateString(),
        'end_date' => now()->subMonths(4)->toDateString(),
    ]);
    $ongoing = Semester::factory()->create([
        'academic_year_id' => $year->id,
        'sequence' => 2,
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
    ]);

    AcademicContextService::flush();

    expect(app(AcademicContextService::class)->currentSemesterId())
        ->toBe($ongoing->id)
        ->not->toBe($past->id);
});

it('ignores semesters belonging to another academic year', function () {
    $otherYear = AcademicYear::factory()->create();
    Semester::factory()->create(['academic_year_id' => $otherYear->id]);

    AcademicYear::factory()->current()->create();

    AcademicContextService::flush();

    expect(app(AcademicContextService::class)->currentSemesterId())->toBeNull();
});

it('ignores inactive semesters', function () {
    $year = AcademicYear::factory()->current()->create();
    Semester::factory()->inactive()->create(['academic_year_id' => $year->id]);

    AcademicContextService::flush();

    expect(app(AcademicContextService::class)->currentSemesterId())->toBeNull();
});

it('throws when no academic year is set', function () {
    AcademicYear::query()->update(['is_active' => false, 'is_current' => false]);
    AcademicContextService::flush();

    app(AcademicContextService::class)->requireCurrentYearId();
})->throws(AcademicYearNotSetException::class);

it('throws when no semester is set', function () {
    AcademicYear::factory()->current()->create();
    AcademicContextService::flush();

    app(AcademicContextService::class)->requireCurrentSemesterId();
})->throws(SemesterNotSetException::class);
