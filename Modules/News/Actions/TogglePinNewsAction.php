<?php

namespace Modules\News\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\News\Interfaces\Repositories\NewsRepositoryInterface;
use Modules\News\Models\News;

/**
 * Pins or unpins an item so it sits at the top of every feed it appears in.
 */
class TogglePinNewsAction extends BaseAction
{
    public function __construct(
        protected NewsRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{news: News, isPinned: bool}  $input
     */
    public function execute(mixed $input): News
    {
        /** @var News $news */
        $news = $input['news'];

        return $this->executeWithTransaction(
            fn (): News => $this->repository->setPinned($news, (bool) $input['isPinned']),
        );
    }
}
