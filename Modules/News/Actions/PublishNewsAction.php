<?php

namespace Modules\News\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\News\Exceptions\NewsOperationException;
use Modules\News\Interfaces\Repositories\NewsRepositoryInterface;
use Modules\News\Models\News;

/**
 * Releases a draft to the school.
 *
 * A scheduled date is preserved, so publishing an item dated next Monday makes
 * it live on Monday rather than immediately — the portal's own date filter is
 * what withholds it until then.
 */
class PublishNewsAction extends BaseAction
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
            if ($news->status->isPublished()) {
                throw NewsOperationException::alreadyPublished();
            }

            return $this->repository->publish($news);
        });
    }
}
