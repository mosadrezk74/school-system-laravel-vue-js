<?php

namespace Modules\Teacher\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * How `salary_base` should be read. The labels were already hard-coded in the
 * teacher form's JS locale files; this makes the backend the single source.
 */
enum SalaryType: string
{
    use BaseEnum;

    case Fixed = 'fixed';
    case Hourly = 'hourly';
    case Contract = 'contract';

    public function label(): string
    {
        return __("teacher::teacher.salaryTypes.{$this->value}");
    }
}
