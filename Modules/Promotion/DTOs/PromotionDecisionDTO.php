<?php

namespace Modules\Promotion\DTOs;

use Modules\Promotion\Enums\PromotionStatus;

/**
 * One student's decision within a bulk promotion.
 *
 * Carries no source year or class: those are read from the student's own
 * record inside the Action, so a client cannot restate where a student was.
 */
final readonly class PromotionDecisionDTO
{
    public function __construct(
        public int $studentId,
        public PromotionStatus $status,
        public ?int $targetClassId,
        public ?string $remarks,
    ) {}

    /**
     * @param  array{student_id: int|string, status: string, target_class_id?: int|string|null, remarks?: string|null}  $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            studentId: (int) $row['student_id'],
            status: PromotionStatus::from((string) $row['status']),
            targetClassId: isset($row['target_class_id']) && $row['target_class_id'] !== null && $row['target_class_id'] !== ''
                ? (int) $row['target_class_id']
                : null,
            remarks: $row['remarks'] ?? null,
        );
    }
}
