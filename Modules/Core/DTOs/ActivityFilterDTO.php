<?php

namespace Modules\Core\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

/**
 * What an admin may narrow the activity log by.
 *
 * Deliberately carries no academic_year_id or semester_id: the current context
 * is resolved server-side from AcademicContextService, so a client cannot
 * relabel the returned entries by editing the query string.
 */
final readonly class ActivityFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?string $logName,
        public ?string $event,
        public ?string $subjectType,
        public ?int $subjectId,
        public ?int $causerId,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            logName: $request->input('log_name'),
            event: $request->input('event'),
            subjectType: $request->input('subject_type'),
            subjectId: $request->filled('subject_id') ? $request->integer('subject_id') : null,
            causerId: $request->filled('causer_id') ? $request->integer('causer_id') : null,
            sortBy: $request->string(Pagination::SORT_PARAM, Pagination::DEFAULT_SORT)->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
