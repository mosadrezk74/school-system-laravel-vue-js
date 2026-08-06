<?php

namespace Modules\Timetable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Meeting\Models\Meeting;

class TimetableSession extends Model
{
    protected $table = 'timetable_sessions';

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'session_date' => 'date',
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(TimetableEntry::class, 'timetable_entry_id');
    }

    public function meeting(): HasOne
    {
        return $this->hasOne(Meeting::class, 'timetable_session_id');
    }
}
