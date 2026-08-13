<?php

namespace Modules\Meeting\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * The four states a meeting moves through.
 *
 * These values were previously written out only in the Vue filter dropdown,
 * which meant the domain lived in the frontend and nothing on the server
 * constrained what `?status=` could be. Declaring them here makes the enum the
 * whitelist for filtering and the source of the select's options.
 *
 * `meetings.status` remains a plain string column: this enum is deliberately
 * scoped to filtering, and changing how the model stores or casts the value
 * would be a domain change rather than a filtering one.
 */
enum MeetingStatus: string
{
    use BaseEnum;

    case Scheduled = 'scheduled';
    case Live = 'live';
    case Finished = 'finished';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __("meeting::meeting.statuses.{$this->value}");
    }
}
