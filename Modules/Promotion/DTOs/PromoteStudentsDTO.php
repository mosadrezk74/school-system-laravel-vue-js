<?php

namespace Modules\Promotion\DTOs;

use Illuminate\Http\Request;

/**
 * A bulk promotion request.
 *
 * The target academic year is an explicit administrative input — it is the one
 * piece of academic context the admin genuinely chooses. The *source* year is
 * never accepted here: the Action resolves it from AcademicContextService.
 */
final readonly class PromoteStudentsDTO
{
    /**
     * @param  array<int, PromotionDecisionDTO>  $decisions
     */
    public function __construct(
        public ?int $targetAcademicYearId,
        public array $decisions,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            targetAcademicYearId: $request->filled('to_academic_year_id')
                ? $request->integer('to_academic_year_id')
                : null,
            decisions: array_map(
                static fn (array $row): PromotionDecisionDTO => PromotionDecisionDTO::fromArray($row),
                $request->validated('decisions', []),
            ),
        );
    }

    /**
     * @return array<int, int>
     */
    public function studentIds(): array
    {
        return array_map(
            static fn (PromotionDecisionDTO $decision): int => $decision->studentId,
            $this->decisions,
        );
    }
}
