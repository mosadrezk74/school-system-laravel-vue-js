<?php

namespace Modules\Setting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Setting\Exceptions\SchoolTermOperationException;
use Modules\Setting\Interfaces\Repositories\SchoolTermRepositoryInterface;
use Modules\Setting\Models\SchoolTerm;

/**
 * Discards a draft.
 *
 * Only a draft can go: a published or archived version is part of the record
 * of what families agreed to, so it stays in the history permanently.
 */
class DeleteSchoolTermAction extends BaseAction
{
    public function __construct(
        protected SchoolTermRepositoryInterface $repository,
    ) {}

    /**
     * @param  SchoolTerm  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var SchoolTerm $term */
        $term = $input;

        return $this->executeWithTransaction(function () use ($term): bool {
            if (! $term->status->isEditable()) {
                throw SchoolTermOperationException::onlyDraftCanBeDeleted();
            }

            return $this->repository->delete($term);
        });
    }
}
