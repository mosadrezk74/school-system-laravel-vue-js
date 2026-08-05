<?php

namespace Modules\Student\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Academic\Models\AcademicYear;
use Modules\Auth\Enums\UserRole;
use Modules\Student\Models\StudentProfile;

/**
 * @extends Factory<StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    protected $model = StudentProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => UserRole::Student->value]),
            'academic_year_id' => AcademicYear::factory(),
            'current_class_id' => null,
            'admission_number' => fake()->unique()->numerify('AD-#####'),
            'admission_date' => now()->toDateString(),
            'scholarship_percentage' => 0,
            'is_graduated' => false,
            'is_active' => true,
        ];
    }
}
