<?php

namespace Modules\Finance\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\FilterOperator;
use Modules\Core\Support\Filters\FilterType;
use Modules\Finance\Enums\InvoiceStatus;

/**
 * "Overdue" is three conditions, not a column: past its due date, still
 * carrying a balance, and not already settled or cancelled.
 *
 * It gets its own Filter rather than staying a bare Query method so it appears
 * in `filterSchema()` alongside everything else and the filter bar can render
 * and clear it like any other field. The composite rule stays here, in the
 * module that owns the domain.
 */
class OverdueInvoiceFilter extends Filter
{
    public static function make(): self
    {
        return new self;
    }

    /** @return array<int, FilterOperator> */
    public function operators(): array
    {
        return [FilterOperator::Equals];
    }

    public function type(): FilterType
    {
        return FilterType::Boolean;
    }

    /**
     * Only the affirmative narrows the list. "Overdue: no" is deliberately not
     * the inverse — a user clearing the toggle wants every invoice back, not
     * the complement set.
     *
     * @param  array<string, mixed>  $input
     */
    protected function resolve(array $input, string $key): ?bool
    {
        $value = $input[$key] ?? null;

        if ($this->isBlank($value)) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?: null;
    }

    /**
     * @param  Builder<Model>  $query
     */
    protected function constrainColumn(
        Builder $query,
        string $column,
        FilterOperator $operator,
        mixed $value,
    ): void {
        $query
            ->whereDate('due_date', '<', now()->toDateString())
            ->where('balance_due', '>', 0)
            ->whereNotIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::Cancelled->value]);
    }
}
