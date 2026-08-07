<?php

namespace Modules\Finance\Repositories;

use Modules\Finance\DTOs\CreateFeeDiscountDTO;
use Modules\Finance\DTOs\UpdateFeeDiscountDTO;
use Modules\Finance\Models\FeeDiscount;

class FeeDiscountRepository implements FeeDiscountRepositoryInterface
{
    public function findById(int $id): ?FeeDiscount
    {
        return FeeDiscount::query()->find($id);
    }

    public function findByIdOrFail(int $id): FeeDiscount
    {
        return FeeDiscount::query()->findOrFail($id);
    }

    public function existsByCode(string $code, ?int $excludeId = null): bool
    {
        return FeeDiscount::query()
            ->where('code', $code)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function create(CreateFeeDiscountDTO $dto, ?int $actorId = null): FeeDiscount
    {
        return FeeDiscount::query()->create([
            ...$dto->toArray(),
            'created_by' => $actorId,
        ]);
    }

    public function update(FeeDiscount $feeDiscount, UpdateFeeDiscountDTO $dto): FeeDiscount
    {
        $feeDiscount->update($dto->toArray());

        return $feeDiscount->refresh();
    }

    public function delete(FeeDiscount $feeDiscount): bool
    {
        return (bool) $feeDiscount->delete();
    }

    public function restore(FeeDiscount $feeDiscount): bool
    {
        return (bool) $feeDiscount->restore();
    }

    public function incrementUsage(FeeDiscount $feeDiscount): FeeDiscount
    {
        $feeDiscount->increment('used_count');

        return $feeDiscount->refresh();
    }
}
