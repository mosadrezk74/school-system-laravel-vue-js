<?php

namespace Modules\Setting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Setting\Exceptions\SchoolTermOperationException;
use Modules\Setting\Interfaces\Repositories\SchoolTermRepositoryInterface;
use Modules\Setting\Models\SchoolTerm;

/**
 * Releases a draft as the school's terms in force.
 *
 * The invariant is that at most one version is published at any moment. Two
 * admins clicking publish on different drafts at the same time is the case
 * that breaks it, so the whole thing runs under a row lock inside one
 * transaction rather than a read-then-write.
 */
class PublishSchoolTermAction extends BaseAction
{
    public function __construct(
        protected SchoolTermRepositoryInterface $repository,
    ) {}

    /**
     * @param  SchoolTerm  $input
     */
    public function execute(mixed $input): SchoolTerm
    {
        /** @var SchoolTerm $term */
        $term = $input;

        return $this->executeWithTransaction(function () use ($term): SchoolTerm {
            /*
             * Locks the currently published row(s) first. A second publish
             * blocks here until this transaction commits, then re-reads the
             * state it is about to change instead of racing against it.
             */
            $this->repository->lockPublishedForUpdate();

            // Re-read under the lock: the status may have moved on since the
            // controller resolved the model.
            $fresh = $this->repository->findById($term->getKey());

            if (! $fresh instanceof SchoolTerm || ! $fresh->isDraft()) {
                throw SchoolTermOperationException::onlyDraftCanBePublished();
            }

            return $this->repository->publish($fresh);
        });
    }
}
