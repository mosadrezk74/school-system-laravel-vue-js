<?php

namespace Modules\SchoolClass\DTOs;

use Modules\Academic\Services\AcademicYearService;
use Modules\SchoolClass\Http\Requests\StoreClassSectionsRequest;

/**
 * Several sections created under one grade level in a single submission.
 *
 * The grade level and academic year are held once at the top rather than on
 * each row: they are properties of the batch, and taking them per-row would
 * let a crafted payload file one section under a different grade.
 */
final readonly class CreateClassSectionsDTO
{
    /**
     * @param  array<int, CreateClassDTO>  $sections
     */
    public function __construct(
        public int $gradeLevelId,
        public int $academicYearId,
        public array $sections,
    ) {}

    public static function fromRequest(StoreClassSectionsRequest $request): self
    {
        $gradeLevelId = (int) $request->validated('grade_level_id');
        $academicYearId = AcademicYearService::requiredId();

        $sections = array_map(
            fn (array $row): CreateClassDTO => new CreateClassDTO(
                gradeLevelId: $gradeLevelId,
                academicYearId: $academicYearId,
                classroomId: isset($row['classroom_id']) && $row['classroom_id'] ? (int) $row['classroom_id'] : null,
                name: (string) $row['name'],
                capacity: (int) $row['capacity'],
                isActive: (bool) ($row['is_active'] ?? true),
            ),
            $request->validated('sections'),
        );

        return new self(
            gradeLevelId: $gradeLevelId,
            academicYearId: $academicYearId,
            sections: $sections,
        );
    }
}
