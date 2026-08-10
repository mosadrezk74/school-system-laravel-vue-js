<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\UpdatePaymentMethodDTO;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\PaymentMethod;
use Modules\Finance\Repositories\PaymentMethodRepositoryInterface;

class UpdatePaymentMethodAction extends BaseAction
{
    public function __construct(
        protected PaymentMethodRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{paymentMethod: PaymentMethod, dto: UpdatePaymentMethodDTO}  $input
     */
    public function execute(mixed $input): PaymentMethod
    {
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $input['paymentMethod'];
        /** @var UpdatePaymentMethodDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($paymentMethod, $dto): PaymentMethod {
            if ($dto->code && $this->repository->existsByCode($dto->code, $paymentMethod->id)) {
                throw FinanceOperationException::paymentMethodCodeAlreadyExists();
            }

            return $this->repository->update($paymentMethod, $dto, auth()->id());
        });
    }
}
