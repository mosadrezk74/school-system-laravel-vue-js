<?php

namespace Modules\Behavior\Actions;

use Modules\Behavior\DTOs\BehaviorCategoryDTO;
use Modules\Behavior\Models\BehaviorCategory;
use Modules\Core\Abstracts\BaseAction;

class CreateBehaviorCategoryAction extends BaseAction
{
    /**
     * @param  BehaviorCategoryDTO  $input
     */
    public function execute(mixed $input): BehaviorCategory
    {
        /** @var BehaviorCategoryDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): BehaviorCategory {
            return BehaviorCategory::create($dto->toArray());
        });
    }
}
