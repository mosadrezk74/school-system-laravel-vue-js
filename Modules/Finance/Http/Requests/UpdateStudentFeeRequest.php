<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Finance\Enums\StudentFeeStatus;

class UpdateStudentFeeRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'lte:amount'],
            'status' => ['required', 'string', Rule::in(StudentFeeStatus::values())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'amount' => __('finance::finance.fields.amount'),
            'discount_amount' => __('finance::finance.fields.discount_amount'),
            'status' => __('finance::finance.fields.status'),
        ];
    }
}
