<?php

namespace Modules\SchoolClass\DTOs;

use Modules\SchoolClass\Http\Requests\StoreClassSectionsRequest;

/**
 * Several sections created under one grade level in a single submission.
 *
 * The grade level is held once at the top rather than on each row: it is a
 * property of the batch, and taking it per-row would let a crafted payload
 * file one section under a different grade.
 */
final readonly class CreateClassSectionsDTO
{
    /**
     * @param  array<int, CreateClassDTO>  $sections
     */
    public function __construct(
        public int $gradeLevelId,
        public array $sections,
    ) {}

    public static function fromRequest(StoreClassSectionsRequest $request): self
    {
        $gradeLevelId = (int) $request->validated('grade_level_id');

        $sections = array_map(
            fn (array $row): CreateClassDTO => new CreateClassDTO(
                gradeLevelId: $gradeLevelId,
                classroomId: isset($row['classroom_id']) && $row['classroom_id'] ? (int) $row['classroom_id'] : null,
                name: (string) $row['name'],
                capacity: (int) $row['capacity'],
                isActive: (bool) ($row['is_active'] ?? true),
            ),
            $request->validated('sections'),
        );

        return new self(
            gradeLevelId: $gradeLevelId,
            sections: $sections,
        );
    }
}
