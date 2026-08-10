<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\CreatePaymentMethodDTO;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\PaymentMethod;
use Modules\Finance\Repositories\PaymentMethodRepositoryInterface;

class CreatePaymentMethodAction extends BaseAction
{
    public function __construct(
        protected PaymentMethodRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreatePaymentMethodDTO  $input
     */
    public function execute(mixed $input): PaymentMethod
    {
        /** @var CreatePaymentMethodDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): PaymentMethod {
            if ($this->repository->existsByCode($dto->code)) {
                throw FinanceOperationException::paymentMethodCodeAlreadyExists();
            }

            return $this->repository->create($dto, auth()->id());
        });
    }
}
