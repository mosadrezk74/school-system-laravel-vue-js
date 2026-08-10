<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Finance\Actions\CreatePaymentRequestAction;
use Modules\Finance\DTOs\CreatePaymentRequestDTO;
use Modules\Finance\Http\Requests\StorePaymentRequestRequest;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;
use Modules\Finance\Repositories\PaymentMethodRepositoryInterface;
use Modules\Finance\Transformers\InvoiceResource;
use Modules\Finance\Transformers\PaymentMethodResource;
use Modules\Finance\Transformers\PaymentResource;
use Modules\Portal\Services\PortalScopeService;

/**
 * The parent's "Pay Now" flow.
 *
 * Every invoice reached here is re-resolved against the guardian's own
 * children, so an id belonging to another family produces a 404 rather than a
 * payable bill. The amount is never read from the request — the action takes
 * it from the invoice.
 */
class ParentPaymentRequestController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function create(Invoice $invoice, PaymentMethodRepositoryInterface $paymentMethods): Response
    {
        $invoice = $this->resolveOwnedInvoice($invoice->id);

        return $this->render('Portal::Parent/PayInvoice', [
            'invoice' => new InvoiceResource($invoice),
            /*
             * Only active methods are ever sent to the browser, and the action
             * re-checks the chosen one, so disabling a method takes effect
             * even for a parent sitting on an already-rendered page.
             */
            'paymentMethods' => PaymentMethodResource::collection($paymentMethods->activeOrdered()),
        ]);
    }

    public function store(StorePaymentRequestRequest $request, CreatePaymentRequestAction $action): RedirectResponse
    {
        $invoice = $this->resolveOwnedInvoice((int) $request->validated('invoice_id'));

        $payment = $action->execute(new CreatePaymentRequestDTO(
            invoiceId: $invoice->id,
            paymentMethodId: (int) $request->validated('payment_method_id'),
            notes: $request->validated('notes') ?: null,
        ));

        return redirect()
            ->route('parent.payment-requests.show', $payment->id)
            ->with('success', __('finance::finance.payment_request_created'));
    }

    public function show(int $payment): Response
    {
        $record = Payment::query()
            ->with(['invoice', 'paymentMethod', 'student.user'])
            ->whereIn('student_id', $this->scope->guardianChildIds())
            ->findOrFail($payment);

        return $this->render('Portal::Parent/PaymentRequest', [
            'payment' => new PaymentResource($record),
        ]);
    }

    /**
     * Ownership is applied as a query constraint rather than a check after the
     * fact, so a foreign invoice is never loaded in the first place.
     */
    private function resolveOwnedInvoice(int $invoiceId): Invoice
    {
        return Invoice::query()
            ->with(['student.user', 'items'])
            ->whereIn('student_id', $this->scope->guardianChildIds())
            ->findOrFail($invoiceId);
    }
}
