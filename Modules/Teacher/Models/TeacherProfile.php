<?php

namespace Modules\Teacher\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Branch\Models\Department;
use Modules\Core\Abstracts\BaseModel;

class TeacherProfile extends BaseModel
{
    protected $table = 'teacher_profiles';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'salary_base' => 'decimal:2',
            'primary_subjects' => 'array',
            'is_active' => 'boolean',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
