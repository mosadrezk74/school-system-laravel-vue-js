<?php

namespace Modules\Student\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academic\Models\AcademicYear;
use Modules\Core\Abstracts\BaseModel;
use Modules\GuardianProfile\Models\GuardianStudent;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Database\Factories\StudentProfileFactory;

class StudentProfile extends BaseModel
{
    use HasFactory;

    protected $table = 'student_profiles';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'admission_date' => 'date',
            'scholarship_percentage' => 'decimal:2',
            'is_graduated' => 'boolean',
            'is_active' => 'boolean',
            'extra_fields' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function currentClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'current_class_id');
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(GuardianStudent::class, 'student_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function newFactory(): StudentProfileFactory
    {
        return StudentProfileFactory::new();
    }
}
