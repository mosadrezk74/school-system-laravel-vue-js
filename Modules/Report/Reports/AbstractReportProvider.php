<?php

namespace Modules\Report\Reports;

use App\Models\User;
use Modules\Academic\Services\AcademicContextService;
use Modules\Auth\Enums\UserRole;
use Modules\Report\Contracts\ReportProviderInterface;
use Modules\Report\Enums\ReportColumnType;
use Modules\Report\Enums\ReportFilterType;
use Modules\SchoolClass\Models\SchoolClass;

abstract class AbstractReportProvider implements ReportProviderInterface
{
    public function name(): string
    {
        return __('report::report.providers.'.str_replace('.', '_', $this->code()));
    }

    /**
     * @param  array<int, array{value: string|int, label: string}>  $options
     * @return array<string, mixed>
     */
    protected function filter(
        string $key,
        ReportFilterType $type,
        bool $required = false,
        array $options = [],
    ): array {
        return [
            'key' => $key,
            'label' => __("report::report.filters.{$key}"),
            'type' => $type->value,
            'required' => $required,
            'options' => $options,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function column(string $key, ReportColumnType $type = ReportColumnType::Text): array
    {
        return [
            'key' => $key,
            'label' => __("report::report.columns.{$key}"),
            'type' => $type->value,
        ];
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    protected function classOptions(): array
    {
        return SchoolClass::query()
            ->where('academic_year_id', AcademicContextService::yearId())
            ->get(['id', 'name'])
            ->map(fn (SchoolClass $class): array => [
                'value' => $class->id,
                'label' => $class->name,
            ])
            ->all();
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    protected function teacherOptions(): array
    {
        return User::query()
            ->where('role', UserRole::Teacher->value)
            ->get(['id', 'name'])
            ->map(fn (User $user): array => [
                'value' => $user->id,
                'label' => $user->name,
            ])
            ->all();
    }
}
