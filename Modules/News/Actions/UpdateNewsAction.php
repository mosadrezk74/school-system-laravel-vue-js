<?php

namespace Modules\News\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\News\DTOs\UpdateNewsDTO;
use Modules\News\Interfaces\Repositories\NewsRepositoryInterface;
use Modules\News\Models\News;

class UpdateNewsAction extends BaseAction
{
    public function __construct(
        protected NewsRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{news: News, dto: UpdateNewsDTO}  $input
     */
    public function execute(mixed $input): News
    {
        /** @var News $news */
        $news = $input['news'];

        /** @var UpdateNewsDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(
            fn (): News => $this->repository->updateFromDTO($news, $dto),
        );
    }
}
