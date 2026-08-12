<?php

namespace Modules\SchoolClass\DTOs;

use Modules\SchoolClass\Http\Requests\UpdateClassRequest;

final readonly class UpdateClassDTO
{
    public function __construct(
        public int $gradeLevelId,
        public ?int $classroomId,
        public string $name,
        public int $capacity,
        public bool $isActive,
    ) {}

    public static function fromRequest(UpdateClassRequest $request): self
    {
        return new self(
            gradeLevelId: (int) $request->validated('grade_level_id'),
            classroomId: $request->validated('classroom_id') ? (int) $request->validated('classroom_id') : null,
            name: $request->validated('name'),
            capacity: (int) $request->validated('capacity'),
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'grade_level_id' => $this->gradeLevelId,
            'classroom_id' => $this->classroomId,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'is_active' => $this->isActive,
        ];
    }
}
