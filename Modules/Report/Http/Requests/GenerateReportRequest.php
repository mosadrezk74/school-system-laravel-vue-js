<?php

namespace Modules\Report\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Report\Enums\ReportFormat;
use Modules\Report\Services\ReportRegistry;

class GenerateReportRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', Rule::in(app(ReportRegistry::class)->codes())],
            'filters' => ['nullable', 'array'],
            'format' => ['nullable', 'string', Rule::enum(ReportFormat::class)],
            'persist' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => __('report::report.fields.code'),
            'filters' => __('report::report.fields.filters'),
            'format' => __('report::report.fields.format'),
        ];
    }
}
