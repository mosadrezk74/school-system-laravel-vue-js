<?php

namespace Modules\Finance\Repositories;

use Modules\Finance\DTOs\CreateFeeDiscountDTO;
use Modules\Finance\DTOs\UpdateFeeDiscountDTO;
use Modules\Finance\Models\FeeDiscount;

interface FeeDiscountRepositoryInterface
{
    public function findById(int $id): ?FeeDiscount;

    public function findByIdOrFail(int $id): FeeDiscount;

    public function existsByCode(string $code, ?int $excludeId = null): bool;

    public function create(CreateFeeDiscountDTO $dto, ?int $actorId = null): FeeDiscount;

    public function update(FeeDiscount $feeDiscount, UpdateFeeDiscountDTO $dto): FeeDiscount;

    public function delete(FeeDiscount $feeDiscount): bool;

    public function restore(FeeDiscount $feeDiscount): bool;

    public function incrementUsage(FeeDiscount $feeDiscount): FeeDiscount;
}
