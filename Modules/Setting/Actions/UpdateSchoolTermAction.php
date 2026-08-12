<?php

namespace Modules\Setting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Setting\DTOs\UpdateSchoolTermDTO;
use Modules\Setting\Exceptions\SchoolTermOperationException;
use Modules\Setting\Interfaces\Repositories\SchoolTermRepositoryInterface;
use Modules\Setting\Models\SchoolTerm;

class UpdateSchoolTermAction extends BaseAction
{
    public function __construct(
        protected SchoolTermRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{term: SchoolTerm, dto: UpdateSchoolTermDTO}  $input
     */
    public function execute(mixed $input): SchoolTerm
    {
        /** @var SchoolTerm $term */
        $term = $input['term'];

        /** @var UpdateSchoolTermDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($term, $dto): SchoolTerm {
            /*
             * Enforced here as well as in the policy: families accepted this
             * exact wording, so editing a published or archived version would
             * invalidate the record of what they agreed to.
             */
            if (! $term->status->isEditable()) {
                throw SchoolTermOperationException::publishedCannotBeEdited();
            }

            return $this->repository->updateFromDTO($term, $dto);
        });
    }
}
