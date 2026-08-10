<?php

namespace Modules\Finance\Repositories;

use Illuminate\Support\Collection;
use Modules\Finance\DTOs\CreatePaymentMethodDTO;
use Modules\Finance\DTOs\UpdatePaymentMethodDTO;
use Modules\Finance\Models\PaymentMethod;

interface PaymentMethodRepositoryInterface
{
    public function findById(int $id): ?PaymentMethod;

    public function findByIdOrFail(int $id): PaymentMethod;

    public function existsByCode(string $code, ?int $excludeId = null): bool;

    /**
     * The methods a parent may choose from, in the order the admin set.
     *
     * @return Collection<int, PaymentMethod>
     */
    public function activeOrdered(): Collection;

    public function create(CreatePaymentMethodDTO $dto, ?int $actorId = null): PaymentMethod;

    public function update(PaymentMethod $paymentMethod, UpdatePaymentMethodDTO $dto, ?int $actorId = null): PaymentMethod;

    public function delete(PaymentMethod $paymentMethod): bool;

    public function restore(PaymentMethod $paymentMethod): bool;
}
