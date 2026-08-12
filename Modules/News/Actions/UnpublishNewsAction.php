<?php

namespace Modules\News\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\News\Exceptions\NewsOperationException;
use Modules\News\Interfaces\Repositories\NewsRepositoryInterface;
use Modules\News\Models\News;

/**
 * Pulls a published item back to draft, removing it from every portal feed.
 */
class UnpublishNewsAction extends BaseAction
{
    public function __construct(
        protected NewsRepositoryInterface $repository,
    ) {}

    /**
     * @param  News  $input
     */
    public function execute(mixed $input): News
    {
        /** @var News $news */
        $news = $input;

        return $this->executeWithTransaction(function () use ($news): News {
            if (! $news->status->isPublished()) {
                throw NewsOperationException::notPublished();
            }

            return $this->repository->unpublish($news);
        });
    }
}
