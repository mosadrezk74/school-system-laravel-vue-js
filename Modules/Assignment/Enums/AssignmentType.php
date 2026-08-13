<?php

namespace Modules\Assignment\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * `assignments.assignment_type` was a free-text box the create form seeded
 * with an empty string, so in practice it held whatever each teacher typed.
 */
enum AssignmentType: string
{
    use BaseEnum;

    case Homework = 'homework';
    case Project = 'project';
    case Essay = 'essay';
    case Presentation = 'presentation';
    case Practical = 'practical';
    case Reading = 'reading';

    public function label(): string
    {
        return __("assignment::assignment.types.{$this->value}");
    }
}
