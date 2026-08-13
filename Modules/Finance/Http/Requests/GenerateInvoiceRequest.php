<?php

namespace Modules\Finance\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class GenerateInvoiceRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:student_profiles,id'],
            'issue_date' => ['required', 'date', 'before_or_equal:today'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_id' => __('finance::finance.fields.student_id'),
            'issue_date' => __('finance::finance.fields.issue_date'),
            'due_date' => __('finance::finance.fields.due_date'),
            'notes' => __('finance::finance.fields.notes'),
        ];
    }
}
