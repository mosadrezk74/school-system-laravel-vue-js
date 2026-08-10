<?php

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Enums\ErrorReportStatus;

class UpdateErrorReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The controller's policy check is the authority.
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(ErrorReportStatus::values())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => __('core::errors.admin.fields.status'),
        ];
    }
}
