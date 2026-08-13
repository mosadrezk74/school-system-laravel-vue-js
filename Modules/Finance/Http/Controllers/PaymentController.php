<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Core\Support\Filters\FilterOptions;
use Modules\Finance\Actions\ArchivePaymentAction;
use Modules\Finance\Actions\CancelPaymentAction;
use Modules\Finance\Actions\ConfirmPaymentAction;
use Modules\Finance\Actions\RecordPaymentAction;
use Modules\Finance\DTOs\CreatePaymentDTO;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Enums\PaymentMethod;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Http\Requests\StorePaymentRequest;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;
use Modules\Finance\Models\PaymentMethod as PaymentMethodModel;
use Modules\Finance\Queries\PaymentQuery;
use Modules\Finance\Transformers\PaymentResource;
use Modules\Finance\Transformers\RefundResource;
use Modules\Student\Models\StudentProfile;

class PaymentController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Payment::class);

        $filters = ListQueryDTO::fromRequest($request);

        $query = (new PaymentQuery)->academicYearId(AcademicContextService::yearId());

        $payments = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Finance::Payments/Index', [
            'payments' => PaymentResource::collection($payments),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema([
                'student_id' => $this->studentFilterOptions(),
                'payment_method_id' => FilterOptions::fromModels(
                    PaymentMethodModel::query()->where('is_active', true)->orderBy('display_order')->get(),
                ),
            ]),
            'statuses' => PaymentStatus::options(),
            ...$this->lookups(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Payment::class);

        return $this->render('Finance::Payments/Create', $this->lookups());
    }

    public function store(StorePaymentRequest $request, RecordPaymentAction $action): RedirectResponse
    {
        $this->authorize('create', Payment::class);

        $action->execute(CreatePaymentDTO::fromRequest($request));

        return $this->toRoute('payments.index', [], __('finance::finance.payment_recorded'));
    }

    public function show(Payment $payment): Response
    {
        $this->authorize('view', $payment);

        $payment->load(['student.user', 'invoice', 'paymentMethod', 'refunds']);

        return $this->render('Finance::Payments/Show', [
            'payment' => new PaymentResource($payment),
            'refunds' => RefundResource::collection($payment->refunds),
        ]);
    }

    /**
     * Turns a pending request into received money. The action owns the row
     * locking and the overpayment guard; this only settles who may ask.
     */
    public function confirm(Payment $payment, ConfirmPaymentAction $action): RedirectResponse
    {
        $this->authorize('confirm', $payment);

        $action->execute($payment);

        return back()->with('success', __('finance::finance.payment_confirmed'));
    }

    public function cancel(Payment $payment, CancelPaymentAction $action): RedirectResponse
    {
        $this->authorize('cancel', $payment);

        $action->execute($payment);

        return back()->with('success', __('finance::finance.payment_cancelled'));
    }

    public function destroy(Payment $payment, ArchivePaymentAction $action): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $action->execute($payment);

        return $this->toRoute('payments.index', [], __('finance::finance.payment_archived'));
    }

    /**
     * @return array<string, mixed>
     */
    private function lookups(): array
    {
        return [
            'invoices' => Invoice::query()
                ->whereNotIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::Cancelled->value])
                ->whereHas('student', fn ($query) => $query->where('academic_year_id', AcademicContextService::yearId()))
                ->with('student.user:id,name')
                ->get(['id', 'student_id', 'invoice_number', 'total_amount', 'balance_due'])
                ->map(fn (Invoice $invoice) => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'student_name' => $invoice->student?->user?->name,
                    'total_amount' => $invoice->total_amount,
                    'balance_due' => $invoice->balance_due,
                ])->values(),
            'paymentMethods' => PaymentMethod::options(),
        ];
    }

    /**
     * Students for the filter bar's picker, scoped to the active academic year
     * the same way the list itself is.
     *
     * @return array<int, array{title: string, value: mixed}>
     */
    private function studentFilterOptions(): array
    {
        return FilterOptions::from(
            StudentProfile::query()
                ->where('academic_year_id', AcademicContextService::yearId())
                ->where('is_active', true)
                ->with('user:id,name')
                ->get(),
            fn (StudentProfile $student): string => $student->user?->name ?? (string) $student->admission_number,
        );
    }
}
