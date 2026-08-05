<?php

namespace Modules\Staff\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Branch\Models\Department;
use Modules\Core\Abstracts\BaseModel;

class StaffProfile extends BaseModel
{
    protected $table = 'staff_profiles';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'salary_base' => 'decimal:2',
            'is_active' => 'boolean',
            'extra_fields' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
