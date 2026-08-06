<?php

namespace Modules\Timetable\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Timetable\DTOs\CreatePeriodDTO;
use Modules\Timetable\DTOs\UpdatePeriodDTO;
use Modules\Timetable\Models\Period;

interface PeriodRepositoryInterface
{
    public function all(): Collection;

    public function findById(int $id): ?Period;

    public function findByIdOrFail(int $id): Period;

    public function existsByPeriodNumber(int $periodNumber, ?int $excludeId = null): bool;

    public function create(CreatePeriodDTO $dto, ?int $actorId = null): Period;

    public function update(Period $period, UpdatePeriodDTO $dto, ?int $actorId = null): Period;

    public function delete(Period $period): bool;

    public function restore(Period $period): bool;
}
