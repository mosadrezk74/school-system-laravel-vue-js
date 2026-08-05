<?php

namespace Modules\Hostel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Hostel\Models\Hostel;

class HostelRoom extends Model
{
    protected $table = 'hostel_rooms';
    protected $guarded = ['id'];
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monthly_fee' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }
    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }
}
