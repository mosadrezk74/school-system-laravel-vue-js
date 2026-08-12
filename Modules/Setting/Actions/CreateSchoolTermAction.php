<?php

namespace Modules\Setting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Setting\DTOs\CreateSchoolTermDTO;
use Modules\Setting\Exceptions\SchoolTermOperationException;
use Modules\Setting\Interfaces\Repositories\SchoolTermRepositoryInterface;
use Modules\Setting\Models\SchoolTerm;

/**
 * Drafts the next version of the school's terms.
 *
 * This is also how a published version is "edited": its wording is frozen, so
 * the admin drafts the next version instead and publishes that.
 */
class CreateSchoolTermAction extends BaseAction
{
    public function __construct(
        protected SchoolTermRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateSchoolTermDTO  $input
     */
    public function execute(mixed $input): SchoolTerm
    {
        /** @var CreateSchoolTermDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): SchoolTerm {
            /*
             * One draft at a time. Two open drafts would race for the next
             * version number and leave the admin unsure which one publishing
             * would release.
             */
            if ($this->repository->existingDraft() instanceof SchoolTerm) {
                throw SchoolTermOperationException::draftAlreadyExists();
            }

            // Held until this transaction commits, so a concurrent create
            // blocks here rather than claiming the same version.
            $version = $this->repository->lockNextVersion();

            return $this->repository->createFromDTO($dto, $version, auth()->id());
        });
    }
}
