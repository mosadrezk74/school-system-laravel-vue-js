<?php

namespace Modules\Core\Support\Filters;

use Modules\Core\Traits\BaseEnum;

/**
 * The control AppFilterBar renders for a filter.
 *
 * This is the only thing the frontend is told about a field's shape; it never
 * infers a widget from a column name.
 */
enum FilterType: string
{
    use BaseEnum;

    case Text = 'text';
    case Number = 'number';
    case NumberRange = 'number-range';
    case Date = 'date';
    case DateRange = 'date-range';
    case Boolean = 'boolean';
    case Select = 'select';
    case MultiSelect = 'multi-select';

    public function label(): string
    {
        return __("core::filters.types.{$this->value}");
    }
}
