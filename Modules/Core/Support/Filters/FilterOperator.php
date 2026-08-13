<?php

namespace Modules\Core\Support\Filters;

use Modules\Core\Traits\BaseEnum;

/**
 * The complete set of comparisons the filtering engine can express.
 *
 * A request may name an operator through `{field}_op`, but only ever selects
 * from the list its own Filter declares in `operators()` — this enum is the
 * vocabulary, not the permission.
 */
enum FilterOperator: string
{
    use BaseEnum;

    case Contains = 'contains';
    case StartsWith = 'starts_with';
    case EndsWith = 'ends_with';
    case Equals = 'equals';
    case NotEquals = 'not_equals';
    case GreaterThan = 'gt';
    case GreaterThanOrEqual = 'gte';
    case LessThan = 'lt';
    case LessThanOrEqual = 'lte';
    case Between = 'between';
    case Before = 'before';
    case After = 'after';
    case In = 'in';
    case IsNull = 'is_null';
    case IsNotNull = 'is_not_null';
    case Exists = 'exists';
    case NotExists = 'not_exists';

    public function label(): string
    {
        return __("core::filters.operators.{$this->value}");
    }
}
