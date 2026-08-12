<?php

namespace Modules\Promotion\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Promotion\Interfaces\Repositories\PromotionRepositoryInterface;
use Modules\Promotion\Models\StudentPromotion;
use Modules\Promotion\Services\PromotionGuardService;
use Modules\Student\Models\StudentProfile;

/**
 * Undoes a promotion, putting the student back where they were.
 *
 * The promotion row is the only source of the previous state — nothing is
 * inferred. Because the row also carries was_graduated and the previous class,
 * a graduation or a transfer can be undone as precisely as a plain promotion.
 *
 * Only the student's most recent decision may be reverted: rolling back an
 * older one would write them over a move that came after it.
 */
class RevertPromotionAction extends BaseAction
{
    public function __construct(
        protected PromotionRepositoryInterface $repository,
        protected PromotionGuardService $guard,
    ) {}

    /**
     * @param  StudentPromotion  $input
     */
    public function execute(mixed $input): StudentProfile
    {
        /** @var StudentPromotion $promotion */
        $promotion = $input;

        return $this->executeWithTransaction(function () use ($promotion): StudentProfile {
            $latest = $this->repository->latestForStudent($promotion->student_id);

            $this->guard->assertRevertable($promotion, $latest);

            $student = $this->repository->restoreFrom($promotion);

            /*
             * The row is removed rather than flagged: the unique constraint on
             * (student_id, from_academic_year_id) must free up so the school
             * can record a different decision for the same year.
             */
            $this->repository->delete($promotion);

            return $student;
        });
    }
}
