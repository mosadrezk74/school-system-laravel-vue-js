<?php

namespace Modules\News\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\News\Interfaces\Repositories\NewsRepositoryInterface;
use Modules\News\Models\News;

/**
 * Archives a news item. Soft delete, matching every other record in the
 * schema, so a removal can be undone.
 */
class DeleteNewsAction extends BaseAction
{
    public function __construct(
        protected NewsRepositoryInterface $repository,
    ) {}

    /**
     * @param  News  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var News $news */
        $news = $input;

        return $this->executeWithTransaction(
            fn (): bool => $this->repository->delete($news),
        );
    }
}
