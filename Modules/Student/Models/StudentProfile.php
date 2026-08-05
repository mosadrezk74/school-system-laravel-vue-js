<?php

namespace Modules\Student\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentProfile extends Model
{
    use SoftDeletes;

    protected $table = 'student_profiles';

    protected $fillable = [
        'user_id',
        'academic_year_id',
        'current_class_id',
        'admission_number',
        'roll_number',
        'admission_date',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'address',
        'city',
        'father_name',
        'father_phone',
        'mother_name',
        'mother_phone',
        'guardian_name',
        'guardian_phone',
        'guardian_relation',
        'blood_group',
        'allergies',
        'medical_conditions',
        'previous_school',
        'scholarship_percentage',
        'is_graduated',
        'is_active',
        'extra_fields',
        'created_by',
        'updated_by',
    ];

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
}
