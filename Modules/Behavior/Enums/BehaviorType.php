<?php

namespace Modules\Behavior\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * Whether a category adds or removes points. The sign of `points` follows
 * from it, so the two must agree.
 */
enum BehaviorType: string
{
    use BaseEnum;

    case Merit = 'merit';
    case Violation = 'violation';

    public function label(): string
    {
        return __("behavior::behavior.types.{$this->value}");
    }
}
