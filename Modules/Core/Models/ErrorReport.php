<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Constants\Pagination;
use Modules\Core\Enums\ErrorReportStatus;

/**
 * A recorded application error.
 *
 * Deliberately not a BaseModel: these are operational diagnostics, not school
 * records, so they carry no soft deletes and may be pruned outright.
 */
class ErrorReport extends Model
{
    protected $table = 'error_reports';

    protected $guarded = ['id'];

    protected $perPage = Pagination::DEFAULT_PER_PAGE;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ErrorReportStatus::class,
            'context' => 'array',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isResolved(): bool
    {
        return $this->status === ErrorReportStatus::Resolved;
    }
}
