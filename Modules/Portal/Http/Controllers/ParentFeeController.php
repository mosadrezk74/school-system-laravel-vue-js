<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Queries\InvoiceQuery;
use Modules\Finance\Queries\PaymentQuery;
use Modules\Finance\Transformers\InvoiceResource;
use Modules\Finance\Transformers\PaymentResource;
use Modules\Portal\Services\PortalScopeService;
use Modules\Portal\Traits\ResolvesGuardianChildren;

class ParentFeeController extends BaseInertiaController
{
    use ResolvesGuardianChildren;

    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request): Response
    {
        $filters = ListQueryDTO::fromRequest($request);

        /*
         * The guardian scope is applied to the query itself, before any
         * request-driven filter is resolved against it. A parent narrowing by
         * another family's student therefore gets an empty list rather than a
         * wider one — the scope is an AND, never something a filter can widen.
         */
        $query = (new InvoiceQuery)->studentIds($this->scope->guardianChildIds());

        $invoices = $query
            ->where($filters->values)
            ->sort('issue_date', 'desc')
            ->paginate($filters->perPage);

        return $this->render('Portal::Parent/Fees', [
            'invoices' => InvoiceResource::collection($invoices),
            'filters' => $query->activeFilters($filters->values),
            'statuses' => InvoiceStatus::options(),
            'children' => $this->childOptions(),
        ]);
    }

    public function payments(Request $request): Response
    {
        $payments = (new PaymentQuery)
            ->studentIds($this->scope->guardianChildIds())
            ->sort('paid_at', 'desc')
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Portal::Parent/Payments', [
            'payments' => PaymentResource::collection($payments),
        ]);
    }
}
