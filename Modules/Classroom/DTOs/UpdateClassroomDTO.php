<?php

namespace Modules\Classroom\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseDTO;

final class UpdateClassroomDTO extends BaseDTO
{
    public function __construct(
        /** @var array<string, string|null> */
        public array $name,
        public int $capacity,
        public ?int $floor,
        public ?string $building,
        public string $roomType,
        public bool $hasProjector,
        public bool $hasSmartboard,
        public bool $isActive,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            capacity: (int) $request->validated('capacity', 30),
            floor: $request->validated('floor') ? (int) $request->validated('floor') : null,
            building: $request->validated('building'),
            roomType: $request->validated('room_type', 'classroom'),
            hasProjector: (bool) $request->validated('has_projector', false),
            hasSmartboard: (bool) $request->validated('has_smartboard', false),
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'capacity' => $this->capacity,
            'floor' => $this->floor,
            'building' => $this->building,
            'room_type' => $this->roomType,
            'has_projector' => $this->hasProjector,
            'has_smartboard' => $this->hasSmartboard,
            'is_active' => $this->isActive,
        ];
    }
}
