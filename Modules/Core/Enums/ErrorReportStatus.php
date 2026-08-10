<?php

namespace Modules\Core\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * Operational state of an error report as an admin works through it.
 */
enum ErrorReportStatus: string
{
    use BaseEnum;

    case New = 'new';
    case Reviewed = 'reviewed';
    case Resolved = 'resolved';

    public function label(): string
    {
        return __("core::errors.statuses.{$this->value}");
    }

    /** Chip colour in the admin table, matching the app's severity palette. */
    public function color(): string
    {
        return match ($this) {
            self::New => 'error',
            self::Reviewed => 'warning',
            self::Resolved => 'success',
        };
    }
}
