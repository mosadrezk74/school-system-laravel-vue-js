<?php

namespace Modules\Hostel\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Branch\Models\Branch;
use Modules\Core\Abstracts\BaseModel;

class Hostel extends BaseModel
{
    protected $table = 'hostels';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warden(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warden_id');
    }
}
