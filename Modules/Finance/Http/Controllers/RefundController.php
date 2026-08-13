<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Finance\Actions\RecordRefundAction;
use Modules\Finance\DTOs\CreateRefundDTO;
use Modules\Finance\Enums\PaymentMethod;
use Modules\Finance\Http\Requests\StoreRefundRequest;
use Modules\Finance\Models\Refund;
use Modules\Finance\Queries\RefundQuery;
use Modules\Finance\Transformers\RefundResource;

class RefundController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Refund::class);

        $filters = ListQueryDTO::fromRequest($request);

        $query = new RefundQuery;

        $refunds = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Finance::Refunds/Index', [
            'refunds' => RefundResource::collection($refunds),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema(),
            'refundMethods' => PaymentMethod::options(),
        ]);
    }

    /**
     * The refundable ceiling is never accepted from the form — the action
     * recomputes it under a row lock before writing.
     */
    public function store(StoreRefundRequest $request, RecordRefundAction $action): RedirectResponse
    {
        $this->authorize('create', Refund::class);

        $action->execute(CreateRefundDTO::fromRequest($request));

        return back()->with('success', __('finance::finance.refund_recorded'));
    }
}
