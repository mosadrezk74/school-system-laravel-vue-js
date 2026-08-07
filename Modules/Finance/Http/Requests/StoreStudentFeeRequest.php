<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Finance\Enums\StudentFeeStatus;

class StoreStudentFeeRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:student_profiles,id'],
            'fee_structure_id' => ['required', 'integer', 'exists:fee_structures,id'],
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'lte:amount'],
            'status' => ['nullable', 'string', Rule::in(StudentFeeStatus::values())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_id' => __('finance::finance.fields.student_id'),
            'fee_structure_id' => __('finance::finance.fields.fee_structure_id'),
            'amount' => __('finance::finance.fields.amount'),
            'discount_amount' => __('finance::finance.fields.discount_amount'),
            'status' => __('finance::finance.fields.status'),
        ];
    }
}
