<?php

namespace Modules\Behavior\Actions;

use Modules\Behavior\Models\BehaviorCategory;
use Modules\Core\Abstracts\BaseAction;

class RestoreBehaviorCategoryAction extends BaseAction
{
    /**
     * @param  BehaviorCategory  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var BehaviorCategory $category */
        $category = $input;

        return $this->executeWithTransaction(fn (): bool => (bool) $category->restore());
    }
}
