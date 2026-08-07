<?php

namespace Modules\Report\Enums;

use Modules\Core\Traits\BaseEnum;

enum ReportFormat: string
{
    use BaseEnum;

    case Json = 'json';
    case Pdf = 'pdf';
    case Excel = 'excel';

    public function label(): string
    {
        return __("report::report.formats.{$this->value}");
    }
}
