<?php

namespace Modules\Behavior\Actions;

use Modules\Behavior\DTOs\BehaviorCategoryDTO;
use Modules\Behavior\Models\BehaviorCategory;
use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\Translation\TranslationService;

class CreateBehaviorCategoryAction extends BaseAction
{
    public function __construct(
        private readonly TranslationService $translationService,
    ) {}

    /**
     * @param  BehaviorCategoryDTO  $input
     */
    public function execute(mixed $input): BehaviorCategory
    {
        /** @var BehaviorCategoryDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): BehaviorCategory {
            $sourceLocale = $this->translationService->detectSourceLocale($dto->name);

            $dto = new BehaviorCategoryDTO(
                name: $this->translationService->fillMissing($dto->name, $sourceLocale),
                code: $dto->code,
                type: $dto->type,
                points: $dto->points,
                isActive: $dto->isActive,
            );

            return BehaviorCategory::create($dto->toArray());
        });
    }
}
