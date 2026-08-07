<?php

namespace Modules\GuardianProfile\DTOs;

use Modules\GuardianProfile\Http\Requests\LinkStudentRequest;

final readonly class LinkGuardianStudentDTO
{
    public function __construct(
        public int $studentId,
        public string $relationship,
        public bool $isPrimaryContact,
        public bool $canPickup,
        public bool $receivesNotifications,
    ) {}

    public static function fromRequest(LinkStudentRequest $request): self
    {
        return new self(
            studentId: (int) $request->validated('student_id'),
            relationship: $request->validated('relationship'),
            isPrimaryContact: (bool) $request->validated('is_primary_contact', true),
            canPickup: (bool) $request->validated('can_pickup', true),
            receivesNotifications: (bool) $request->validated('receives_notifications', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'student_id' => $this->studentId,
            'relationship' => $this->relationship,
            'is_primary_contact' => $this->isPrimaryContact,
            'can_pickup' => $this->canPickup,
            'receives_notifications' => $this->receivesNotifications,
        ];
    }
}
