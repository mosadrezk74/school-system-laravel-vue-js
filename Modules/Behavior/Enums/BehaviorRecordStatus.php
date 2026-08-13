<?php

namespace Modules\Behavior\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * Where a record sits in its review workflow.
 */
enum BehaviorRecordStatus: string
{
    use BaseEnum;

    case Reported = 'reported';
    case Reviewed = 'reviewed';

    public function label(): string
    {
        return __("behavior::behavior.statuses.{$this->value}");
    }
}
