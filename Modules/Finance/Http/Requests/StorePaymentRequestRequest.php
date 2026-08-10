<?php

namespace Modules\Finance\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

/**
 * A parent opening a payment request.
 *
 * Deliberately accepts no amount. What is owed is read from the invoice inside
 * the action, so there is no field here for a tampered total to arrive in.
 * Ownership of the invoice is settled by the controller against
 * PortalScopeService, not by an `exists` rule.
 */
class StorePaymentRequestRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'invoice_id' => __('finance::finance.fields.invoice_id'),
            'payment_method_id' => __('finance::finance.fields.payment_method'),
            'notes' => __('finance::finance.fields.notes'),
        ];
    }
}
