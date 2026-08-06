<?php

namespace Modules\Behavior\Actions;

use Modules\Behavior\DTOs\BehaviorCategoryDTO;
use Modules\Behavior\Models\BehaviorCategory;
use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\Translation\TranslationService;

class UpdateBehaviorCategoryAction extends BaseAction
{
    public function __construct(
        private readonly TranslationService $translationService,
    ) {}

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
            $sourceLocale = $this->translationService->detectSourceLocale($dto->name);

            $dto = new BehaviorCategoryDTO(
                name: $this->translationService->fillMissing($dto->name, $sourceLocale),
                code: $dto->code,
                type: $dto->type,
                points: $dto->points,
                isActive: $dto->isActive,
            );

            $category->update($dto->toArray());

            return $category->refresh();
        });
    }
}
