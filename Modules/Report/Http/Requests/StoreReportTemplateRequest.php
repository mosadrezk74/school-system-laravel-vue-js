<?php

namespace Modules\Report\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Report\Enums\ReportCategory;

class StoreReportTemplateRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->translatableRules('name'),
            'code' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:15', Rule::in(ReportCategory::values())],
            'query_data' => ['nullable', 'array'],
            'columns' => ['nullable', 'array'],
            'filters' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('report::report.fields.name'),
            'code' => __('report::report.fields.code'),
            'category' => __('report::report.fields.category'),
            'query_data' => __('report::report.fields.query_data'),
            'columns' => __('report::report.fields.columns'),
            'filters' => __('report::report.fields.filters'),
            'is_active' => __('report::report.fields.is_active'),
        ];
    }
}
