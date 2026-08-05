<?php

namespace Modules\GuardianProfile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\Student\Models\StudentProfile;

class GuardianStudent extends Model
{
    protected $table = 'parent_student';
    protected $guarded = ['id'];
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary_contact' => 'boolean',
            'can_pickup' => 'boolean',
            'receives_notifications' => 'boolean',
        ];
    }
    public function parent(): BelongsTo
    {
        return $this->belongsTo(GuardianProfile::class, 'parent_id');
    }
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }
}
