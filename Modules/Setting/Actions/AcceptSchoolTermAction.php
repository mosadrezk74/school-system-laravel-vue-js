<?php

namespace Modules\Setting\Actions;

use App\Models\User;
use Modules\Core\Abstracts\BaseAction;
use Modules\Setting\Exceptions\SchoolTermOperationException;
use Modules\Setting\Interfaces\Repositories\SchoolTermRepositoryInterface;
use Modules\Setting\Models\SchoolTerm;
use Modules\Setting\Models\SchoolTermAcceptance;

/**
 * Records that a person has agreed to the terms in force.
 *
 * The version is resolved server-side from what is actually published, never
 * taken from the request: a client that posted a term id could otherwise
 * record agreement to an old or unreleased wording.
 */
class AcceptSchoolTermAction extends BaseAction
{
    public function __construct(
        protected SchoolTermRepositoryInterface $repository,
    ) {}

    /**
     * @param  User  $input  The person accepting — always the signed-in user.
     */
    public function execute(mixed $input): SchoolTermAcceptance
    {
        /** @var User $user */
        $user = $input;

        return $this->executeWithTransaction(function () use ($user): SchoolTermAcceptance {
            $term = $this->repository->currentPublished();

            if (! $term instanceof SchoolTerm) {
                throw SchoolTermOperationException::noPublishedVersion();
            }

            /*
             * Belt and braces with the status check: currentPublished() can
             * only return a published row, but this keeps the rule explicit
             * if the query is ever widened.
             */
            if (! $term->status->isAcceptable()) {
                throw SchoolTermOperationException::notAcceptable();
            }

            if ($this->repository->acceptanceFor($term, $user) instanceof SchoolTermAcceptance) {
                throw SchoolTermOperationException::alreadyAccepted();
            }

            return $this->repository->recordAcceptance($term, $user);
        });
    }
}
