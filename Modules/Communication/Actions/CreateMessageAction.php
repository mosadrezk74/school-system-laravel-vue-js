<?php

namespace Modules\Communication\Actions;

use Modules\Communication\DTOs\CreateMessageDTO;
use Modules\Communication\Models\BroadcastMessage;
use Modules\Communication\Repositories\MessageRepositoryInterface;
use Modules\Communication\Services\MessageDispatcherService;
use Modules\Core\Abstracts\BaseAction;

class CreateMessageAction extends BaseAction
{
    public function __construct(
        protected MessageRepositoryInterface $repository,
        protected MessageDispatcherService $dispatcher,
    ) {}

    /**
     * @param  CreateMessageDTO  $input
     */
    public function execute(mixed $input): BroadcastMessage
    {
        /** @var CreateMessageDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): BroadcastMessage {
            $message = $this->repository->create($dto, auth()->id());

            $this->dispatcher->dispatch($message);

            return $message;
        });
    }
}
