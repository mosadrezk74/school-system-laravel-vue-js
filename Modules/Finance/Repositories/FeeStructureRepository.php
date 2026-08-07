<?php

namespace Modules\Finance\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Finance\DTOs\CreateFeeStructureDTO;
use Modules\Finance\DTOs\UpdateFeeStructureDTO;
use Modules\Finance\Models\FeeStructure;

class FeeStructureRepository implements FeeStructureRepositoryInterface
{
    public function findById(int $id): ?FeeStructure
    {
        return FeeStructure::query()->find($id);
    }

    public function findByIdOrFail(int $id): FeeStructure
    {
        return FeeStructure::query()->findOrFail($id);
    }

    public function create(CreateFeeStructureDTO $dto, ?int $actorId = null): FeeStructure
    {
        return FeeStructure::query()->create([
            ...$dto->toArray(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    public function update(FeeStructure $feeStructure, UpdateFeeStructureDTO $dto, ?int $actorId = null): FeeStructure
    {
        $feeStructure->update([
            ...$dto->toArray(),
            'updated_by' => $actorId,
        ]);

        return $feeStructure->refresh();
    }

    public function delete(FeeStructure $feeStructure): bool
    {
        return (bool) $feeStructure->delete();
    }

    public function restore(FeeStructure $feeStructure): bool
    {
        return (bool) $feeStructure->restore();
    }

    public function applicableTo(?int $gradeLevelId, ?int $classroomId): Collection
    {
        return FeeStructure::query()
            ->where(function (Builder $query) use ($gradeLevelId, $classroomId) {
                $query->where(function (Builder $scoped) {
                    $scoped->whereNull('grade_level_id')->whereNull('classroom_id');
                });

                if ($gradeLevelId) {
                    $query->orWhere('grade_level_id', $gradeLevelId);
                }

                if ($classroomId) {
                    $query->orWhere('classroom_id', $classroomId);
                }
            })
            ->with('feeCategory')
            ->get();
    }
}
