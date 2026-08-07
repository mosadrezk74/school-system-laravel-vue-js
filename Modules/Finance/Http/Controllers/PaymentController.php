<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Services\AcademicYearService;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Finance\Actions\ArchivePaymentAction;
use Modules\Finance\Actions\RecordPaymentAction;
use Modules\Finance\DTOs\CreatePaymentDTO;
use Modules\Finance\DTOs\PaymentFilterDTO;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Enums\PaymentMethod;
use Modules\Finance\Http\Requests\StorePaymentRequest;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;
use Modules\Finance\Queries\PaymentQuery;
use Modules\Finance\Transformers\PaymentResource;

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
                'paid_from',
                'paid_to',
            ]),
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

        $payment->load(['student.user', 'invoice']);

        return $this->render('Finance::Payments/Show', [
            'payment' => new PaymentResource($payment),
        ]);
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
