<?php

namespace Modules\Finance\Repositories;

use Illuminate\Support\Collection;
use Modules\Finance\DTOs\CreateFeeStructureDTO;
use Modules\Finance\DTOs\UpdateFeeStructureDTO;
use Modules\Finance\Models\FeeStructure;

interface FeeStructureRepositoryInterface
{
    public function findById(int $id): ?FeeStructure;

    public function findByIdOrFail(int $id): FeeStructure;

    public function create(CreateFeeStructureDTO $dto, ?int $actorId = null): FeeStructure;

    public function update(FeeStructure $feeStructure, UpdateFeeStructureDTO $dto, ?int $actorId = null): FeeStructure;

    public function delete(FeeStructure $feeStructure): bool;

    public function restore(FeeStructure $feeStructure): bool;

    /**
     * Structures that apply to a student's grade level or classroom, plus the
     * school-wide ones scoped to neither.
     *
     * @return Collection<int, FeeStructure>
     */
    public function applicableTo(?int $gradeLevelId, ?int $classroomId): Collection;
}
