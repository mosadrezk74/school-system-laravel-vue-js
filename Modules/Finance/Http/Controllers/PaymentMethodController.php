<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Finance\Actions\ArchivePaymentMethodAction;
use Modules\Finance\Actions\CreatePaymentMethodAction;
use Modules\Finance\Actions\RestorePaymentMethodAction;
use Modules\Finance\Actions\UpdatePaymentMethodAction;
use Modules\Finance\DTOs\CreatePaymentMethodDTO;
use Modules\Finance\DTOs\UpdatePaymentMethodDTO;
use Modules\Finance\Enums\PaymentMethodType;
use Modules\Finance\Http\Requests\StorePaymentMethodRequest;
use Modules\Finance\Http\Requests\UpdatePaymentMethodRequest;
use Modules\Finance\Models\PaymentMethod;
use Modules\Finance\Queries\PaymentMethodQuery;
use Modules\Finance\Transformers\PaymentMethodResource;

class PaymentMethodController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PaymentMethod::class);

        $filters = ListQueryDTO::fromRequest($request);

        $query = new PaymentMethodQuery;

        $paymentMethods = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Finance::PaymentMethods/Index', [
            'paymentMethods' => PaymentMethodResource::collection($paymentMethods),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema(),
            'types' => PaymentMethodType::options(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', PaymentMethod::class);

        return $this->render('Finance::PaymentMethods/Create', [
            'types' => PaymentMethodType::options(),
        ]);
    }

    public function store(StorePaymentMethodRequest $request, CreatePaymentMethodAction $action): RedirectResponse
    {
        $this->authorize('create', PaymentMethod::class);

        $action->execute(CreatePaymentMethodDTO::fromRequest($request));

        return $this->toRoute('payment-methods.index', [], __('finance::finance.payment_method_created'));
    }

    public function show(PaymentMethod $paymentMethod): Response
    {
        $this->authorize('view', $paymentMethod);

        return $this->render('Finance::PaymentMethods/Show', [
            'paymentMethod' => new PaymentMethodResource($paymentMethod),
        ]);
    }

    public function edit(PaymentMethod $paymentMethod): Response
    {
        $this->authorize('update', $paymentMethod);

        return $this->render('Finance::PaymentMethods/Edit', [
            'paymentMethod' => new PaymentMethodResource($paymentMethod),
            'types' => PaymentMethodType::options(),
        ]);
    }

    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod, UpdatePaymentMethodAction $action): RedirectResponse
    {
        $this->authorize('update', $paymentMethod);

        $action->execute(['paymentMethod' => $paymentMethod, 'dto' => UpdatePaymentMethodDTO::fromRequest($request)]);

        return $this->toRoute('payment-methods.index', [], __('finance::finance.payment_method_updated'));
    }

    public function destroy(PaymentMethod $paymentMethod, ArchivePaymentMethodAction $action): RedirectResponse
    {
        $this->authorize('delete', $paymentMethod);

        $action->execute($paymentMethod);

        return $this->toRoute('payment-methods.index', [], __('finance::finance.payment_method_archived'));
    }

    public function restore(int $paymentMethod, RestorePaymentMethodAction $action): RedirectResponse
    {
        $paymentMethod = PaymentMethod::withTrashed()->findOrFail($paymentMethod);

        $this->authorize('restore', $paymentMethod);

        $action->execute($paymentMethod);

        return $this->toRoute('payment-methods.index', [], __('finance::finance.payment_method_restored'));
    }
}
