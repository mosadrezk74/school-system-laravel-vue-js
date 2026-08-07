<?php

namespace Modules\Finance\Repositories;

use Modules\Finance\DTOs\CreateFeeCategoryDTO;
use Modules\Finance\DTOs\UpdateFeeCategoryDTO;
use Modules\Finance\Models\FeeCategory;

interface FeeCategoryRepositoryInterface
{
    public function findById(int $id): ?FeeCategory;

    public function findByIdOrFail(int $id): FeeCategory;

    public function existsByCode(string $code, ?int $excludeId = null): bool;

    public function create(CreateFeeCategoryDTO $dto): FeeCategory;

    public function update(FeeCategory $feeCategory, UpdateFeeCategoryDTO $dto): FeeCategory;

    public function delete(FeeCategory $feeCategory): bool;

    public function restore(FeeCategory $feeCategory): bool;
}
