<?php

namespace Modules\Core\Models;

use Modules\Core\Constants\Pagination;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

/**
 * An audit-log entry, read through the application's own namespace.
 *
 * Extends the package model rather than replacing it so the table, casts and
 * subject/causer morphs keep coming from Spatie, while giving us a stable
 * class to hang a policy and route-model binding on.
 *
 * No query scopes are declared here on purpose: the parent already ships
 * inLog(), forSubject(), forEvent() and causedBy() with signatures of its own,
 * and redeclaring any of them is a fatal incompatible-override. Filtering
 * lives in ActivityQuery instead, which is where the sibling ErrorReportQuery
 * keeps it too.
 *
 * Deliberately not a BaseModel: like ErrorReport these are operational
 * records, not school records — no soft deletes, and they may be pruned.
 */
class Activity extends SpatieActivity
{
    protected $perPage = Pagination::DEFAULT_PER_PAGE;
}
