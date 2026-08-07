<?php

namespace Modules\Finance\Repositories;

use Modules\Finance\DTOs\CreateFeeCategoryDTO;
use Modules\Finance\DTOs\UpdateFeeCategoryDTO;
use Modules\Finance\Models\FeeCategory;

class FeeCategoryRepository implements FeeCategoryRepositoryInterface
{
    public function findById(int $id): ?FeeCategory
    {
        return FeeCategory::query()->find($id);
    }

    public function findByIdOrFail(int $id): FeeCategory
    {
        return FeeCategory::query()->findOrFail($id);
    }

    public function existsByCode(string $code, ?int $excludeId = null): bool
    {
        return FeeCategory::query()
            ->where('code', $code)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function create(CreateFeeCategoryDTO $dto): FeeCategory
    {
        return FeeCategory::query()->create($dto->toArray());
    }

    public function update(FeeCategory $feeCategory, UpdateFeeCategoryDTO $dto): FeeCategory
    {
        $feeCategory->update($dto->toArray());

        return $feeCategory->refresh();
    }

    public function delete(FeeCategory $feeCategory): bool
    {
        return (bool) $feeCategory->delete();
    }

    public function restore(FeeCategory $feeCategory): bool
    {
        return (bool) $feeCategory->restore();
    }
}
