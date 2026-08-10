<?php

namespace Modules\Behavior\DTOs;

use Modules\Behavior\Http\Requests\StoreBehaviorCategoryRequest;
use Modules\Behavior\Http\Requests\UpdateBehaviorCategoryRequest;

/**
 * Shared between create and update, following AcademicYearDTO's convention
 * for simple lookup/config resources (unlike Student's split Create/Update
 * DTOs, which exist because create and update genuinely diverge there —
 * password handling, admission-number generation, etc). Categories have no
 * such divergence, so one DTO covers both.
 */
final readonly class BehaviorCategoryDTO
{
    public function __construct(
        public array $name,
        public string $type,
        public int $points,
        public bool $isActive,
    ) {}

    public static function fromRequest(StoreBehaviorCategoryRequest|UpdateBehaviorCategoryRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            type: $request->validated('type'),
            points: (int) $request->validated('points', 0),
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'points' => $this->points,
            'is_active' => $this->isActive,
        ];
    }
}
