<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\Models\PaymentMethod;
use Modules\Finance\Repositories\PaymentMethodRepositoryInterface;

class RestorePaymentMethodAction extends BaseAction
{
    public function __construct(
        protected PaymentMethodRepositoryInterface $repository,
    ) {}

    /**
     * @param  PaymentMethod  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $input;

        return $this->executeWithTransaction(fn (): bool => $this->repository->restore($paymentMethod));
    }
}
