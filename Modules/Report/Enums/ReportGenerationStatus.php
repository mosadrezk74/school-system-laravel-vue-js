<?php

namespace Modules\Report\Enums;

use Modules\Core\Traits\BaseEnum;

enum ReportGenerationStatus: string
{
    use BaseEnum;

    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return __("report::report.generation_statuses.{$this->value}");
    }
}
