<?php

namespace Modules\Timetable\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Timetable\DTOs\CreatePeriodDTO;
use Modules\Timetable\DTOs\UpdatePeriodDTO;
use Modules\Timetable\Models\Period;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class PeriodRepository implements PeriodRepositoryInterface
{
    public function all(): Collection
    {
        return Period::query()->orderBy('period_number')->get();
    }

    public function findById(int $id): ?Period
    {
        return Period::query()->find($id);
    }

    public function findByIdOrFail(int $id): Period
    {
        return Period::query()->findOrFail($id);
    }

    public function existsByPeriodNumber(int $periodNumber, ?int $excludeId = null): bool
    {
        return Period::query()
            ->where('period_number', $periodNumber)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function create(CreatePeriodDTO $dto, ?int $actorId = null): Period
    {
        return Period::create($dto->toArray());
    }

    public function update(Period $period, UpdatePeriodDTO $dto, ?int $actorId = null): Period
    {
        $period->update($dto->toArray());

        return $period->refresh();
    }

    public function delete(Period $period): bool
    {
        return (bool) $period->delete();
    }

    public function restore(Period $period): bool
    {
        return (bool) $period->restore();
    }
}
