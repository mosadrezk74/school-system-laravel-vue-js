<?php

namespace Modules\Finance\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class PaymentFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?int $studentId,
        public ?int $invoiceId,
        public ?string $paymentMethod,
        public ?string $paidFrom,
        public ?string $paidTo,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            studentId: $request->filled('student_id') ? (int) $request->input('student_id') : null,
            invoiceId: $request->filled('invoice_id') ? (int) $request->input('invoice_id') : null,
            paymentMethod: $request->string('payment_method')->toString() ?: null,
            paidFrom: $request->string('paid_from')->toString() ?: null,
            paidTo: $request->string('paid_to')->toString() ?: null,
            sortBy: $request->string(Pagination::SORT_PARAM, Pagination::DEFAULT_SORT)->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
