<?php

namespace Modules\Student\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Auth\Enums\Gender;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\RelationFilter;
use Modules\Core\Support\Filters\TextFilter;
use Modules\Student\Enums\BloodGroup;
use Modules\Student\Models\StudentProfile;

class StudentQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = [
        'admission_number',
        'roll_number',
        'guardian_phone',
        'father_phone',
        'mother_phone',
    ];

    /**
     * Name and email live on the related user, which is why every list of
     * students had to search two tables.
     *
     * @var array<string, array<int, string>>
     */
    protected array $searchableRelations = [
        'user' => ['name', 'email', 'username', 'national_id'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = [
        'admission_number',
        'roll_number',
        'admission_date',
        'is_active',
        'created_at',
    ];

    protected function baseQuery(): Builder
    {
        return StudentProfile::query()->with(['user', 'academicYear', 'currentClass']);
    }

    /**
     * Medical notes, emergency contacts and the address are deliberately absent:
     * they are free-text support data, not axes anyone lists students along, and
     * `allergies` / `medical_conditions` should not become a way to enumerate
     * students by health information.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            // Defaulted by the controller to the current academic context, and
            // overridable so past years stay reachable.
            'academic_year_id' => ExactFilter::make()->label('students.academicYear')->required(),

            // `nullable()` is what backs the "not yet assigned to a class"
            // choice the controller adds to the option list.
            'current_class_id' => ExactFilter::make()->label('students.currentClass')->nullable(),
            'grade_level_id' => RelationFilter::make('currentClass', 'grade_level_id')->label('students.gradeLevel'),
            'gender' => EnumFilter::make(Gender::class)->on('user')->label('students.gender'),
            'blood_group' => EnumFilter::make(BloodGroup::class)->label('students.bloodGroup'),
            'is_active' => BooleanFilter::make()->label('students.active'),
            'is_graduated' => BooleanFilter::make()->label('students.isGraduated'),
            'admission_date' => DateRangeFilter::make()->label('students.admissionDate'),
            'city' => TextFilter::make()->label('students.city'),
            'has_guardian' => RelationFilter::make('guardians')->label('students.hasGuardian'),
        ];
    }
}
