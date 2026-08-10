<?php

namespace Modules\Finance\Repositories;

use Illuminate\Support\Collection;
use Modules\Finance\DTOs\CreatePaymentMethodDTO;
use Modules\Finance\DTOs\UpdatePaymentMethodDTO;
use Modules\Finance\Models\PaymentMethod;

class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    public function findById(int $id): ?PaymentMethod
    {
        return PaymentMethod::query()->find($id);
    }

    public function findByIdOrFail(int $id): PaymentMethod
    {
        return PaymentMethod::query()->findOrFail($id);
    }

    public function existsByCode(string $code, ?int $excludeId = null): bool
    {
        return PaymentMethod::query()
            ->where('code', $code)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function activeOrdered(): Collection
    {
        return PaymentMethod::query()->active()->ordered()->get();
    }

    public function create(CreatePaymentMethodDTO $dto, ?int $actorId = null): PaymentMethod
    {
        return PaymentMethod::query()->create([
            ...$dto->toArray(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    public function update(PaymentMethod $paymentMethod, UpdatePaymentMethodDTO $dto, ?int $actorId = null): PaymentMethod
    {
        $paymentMethod->update([
            ...$dto->toArray(),
            'updated_by' => $actorId,
        ]);

        return $paymentMethod->refresh();
    }

    public function delete(PaymentMethod $paymentMethod): bool
    {
        return (bool) $paymentMethod->delete();
    }

    public function restore(PaymentMethod $paymentMethod): bool
    {
        return (bool) $paymentMethod->restore();
    }
}
