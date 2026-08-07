<?php

namespace Modules\Finance\DTOs;

use Illuminate\Http\Request;

final readonly class ReportFilterDTO
{
    public function __construct(
        public ?int $studentId,
        public ?int $gradeLevelId,
        public ?int $classroomId,
        public ?int $feeCategoryId,
        public ?string $dateFrom,
        public ?string $dateTo,
        public ?int $year,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            studentId: $request->filled('student_id') ? (int) $request->input('student_id') : null,
            gradeLevelId: $request->filled('grade_level_id') ? (int) $request->input('grade_level_id') : null,
            classroomId: $request->filled('classroom_id') ? (int) $request->input('classroom_id') : null,
            feeCategoryId: $request->filled('fee_category_id') ? (int) $request->input('fee_category_id') : null,
            dateFrom: $request->string('date_from')->toString() ?: null,
            dateTo: $request->string('date_to')->toString() ?: null,
            year: $request->filled('year') ? (int) $request->input('year') : null,
        );
    }
}
