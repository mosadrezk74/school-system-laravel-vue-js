<?php

namespace Modules\Behavior\Actions;

use Modules\Behavior\DTOs\BehaviorCategoryDTO;
use Modules\Behavior\Models\BehaviorCategory;
use Modules\Core\Abstracts\BaseAction;

class UpdateBehaviorCategoryAction extends BaseAction
{
    /**
     * @param  array{category: BehaviorCategory, dto: BehaviorCategoryDTO}  $input
     */
    public function execute(mixed $input): BehaviorCategory
    {
        /** @var BehaviorCategory $category */
        $category = $input['category'];
        /** @var BehaviorCategoryDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($category, $dto): BehaviorCategory {
            $category->update($dto->toArray());

            return $category->refresh();
        });
    }
}
