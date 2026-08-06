<?php

namespace Modules\Classroom\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseDTO;

final class CreateClassroomDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public ?string $code,
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
            code: $request->validated('code'),
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
            'code' => $this->code,
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
