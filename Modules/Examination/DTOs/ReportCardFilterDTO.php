<?php

namespace Modules\Examination\DTOs;

use Illuminate\Http\Request;

/**
 * What an admin may narrow the report-card screen by.
 *
 * Deliberately carries no academic_year_id or semester_id: the current context
 * is resolved server-side from AcademicContextService, so a client cannot ask
 * for a different year by editing the query string.
 */
final readonly class ReportCardFilterDTO
{
    public function __construct(
        public ?int $studentId,
        public ?int $classId,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            studentId: $request->filled('student_id') ? $request->integer('student_id') : null,
            classId: $request->filled('class_id') ? $request->integer('class_id') : null,
        );
    }
}
