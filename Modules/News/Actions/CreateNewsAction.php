<?php

namespace Modules\News\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\News\DTOs\CreateNewsDTO;
use Modules\News\Interfaces\Repositories\NewsRepositoryInterface;
use Modules\News\Models\News;

class CreateNewsAction extends BaseAction
{
    public function __construct(
        protected NewsRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateNewsDTO  $input
     */
    public function execute(mixed $input): News
    {
        /** @var CreateNewsDTO $dto */
        $dto = $input;

        // The author is the signed-in user, never a value from the request.
        return $this->executeWithTransaction(
            fn (): News => $this->repository->createFromDTO($dto, auth()->id()),
        );
    }
}
