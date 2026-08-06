<?php

namespace Modules\Meeting\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class MeetingFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?int $teacherId,
        public ?int $branchId,
        public ?string $status,
        public ?string $date,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            teacherId: $request->filled('teacher_id') ? (int) $request->input('teacher_id') : null,
            branchId: $request->filled('branch_id') ? (int) $request->input('branch_id') : null,
            status: $request->string('status')->toString() ?: null,
            date: $request->string('date')->toString() ?: null,
            sortBy: $request->string(Pagination::SORT_PARAM, 'scheduled_at')->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
