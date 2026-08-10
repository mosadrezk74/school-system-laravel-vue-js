<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Services\AcademicYearService;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Finance\Actions\ArchivePaymentAction;
use Modules\Finance\Actions\CancelPaymentAction;
use Modules\Finance\Actions\ConfirmPaymentAction;
use Modules\Finance\Actions\RecordPaymentAction;
use Modules\Finance\DTOs\CreatePaymentDTO;
use Modules\Finance\DTOs\PaymentFilterDTO;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Enums\PaymentMethod;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Http\Requests\StorePaymentRequest;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;
use Modules\Finance\Queries\PaymentQuery;
use Modules\Finance\Transformers\PaymentResource;
use Modules\Finance\Transformers\RefundResource;

class PaymentController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Payment::class);

        $filters = PaymentFilterDTO::fromRequest($request);

        $payments = (new PaymentQuery)
            ->academicYearId(AcademicYearService::id())
            ->searchReference($filters->search)
            ->studentId($filters->studentId)
            ->invoiceId($filters->invoiceId)
            ->paymentMethod($filters->paymentMethod)
            ->status($request->string('status')->toString() ?: null)
            ->paidBetween($filters->paidFrom, $filters->paidTo)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Finance::Payments/Index', [
            'payments' => PaymentResource::collection($payments),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'student_id',
                'invoice_id',
                'payment_method',
                'status',
                'paid_from',
                'paid_to',
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
                ->whereHas('student', fn ($query) => $query->where('academic_year_id', AcademicYearService::id()))
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
}
