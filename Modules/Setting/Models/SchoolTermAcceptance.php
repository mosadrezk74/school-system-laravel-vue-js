<?php

namespace Modules\Setting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One person's agreement to one version of the school's terms.
 *
 * Extends Model rather than BaseModel on purpose: this is an evidence record,
 * so it carries no deleted_at. An acceptance either happened or it did not.
 */
class SchoolTermAcceptance extends Model
{
    protected $table = 'school_term_acceptances';

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    public function schoolTerm(): BelongsTo
    {
        return $this->belongsTo(SchoolTerm::class, 'school_term_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
