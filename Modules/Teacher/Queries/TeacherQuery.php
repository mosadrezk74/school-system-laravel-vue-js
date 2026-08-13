<?php

namespace Modules\Teacher\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Auth\Enums\Gender;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\RelationFilter;
use Modules\Core\Support\Filters\TextFilter;
use Modules\Teacher\Enums\SalaryType;
use Modules\Teacher\Models\TeacherProfile;

class TeacherQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['employee_id', 'qualification', 'specialization', 'position'];

    /** @var array<string, array<int, string>> */
    protected array $searchableRelations = [
        'user' => ['name', 'email', 'username', 'national_id'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = [
        'employee_id',
        'position',
        'experience_years',
        'joining_date',
        'is_active',
        'created_at',
    ];

    protected function baseQuery(): Builder
    {
        return TeacherProfile::query()->with(['user', 'subjects']);
    }

    /**
     * `salary_base` is deliberately not filterable. Narrowing a staff list by
     * pay band would turn the directory into a payroll report for anyone who
     * can view teachers, which is a wider audience than the people who may see
     * compensation. `salary_type` stays, since it is a contract category
     * rather than an amount.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'gender' => EnumFilter::make(Gender::class)->on('user')->label('teacher.fields.gender'),
            'is_active' => BooleanFilter::make()->label('teacher.fields.is_active'),
            'subject_id' => RelationFilter::make('subjects', 'subjects.id')->label('teacher.fields.subject_ids'),
            'salary_type' => EnumFilter::make(SalaryType::class)->label('teacher.fields.salary_type'),
            'position' => TextFilter::make()->label('teacher.fields.position'),
            'specialization' => TextFilter::make()->label('teacher.fields.specialization'),
            'qualification' => TextFilter::make()->label('teacher.fields.qualification'),
            'experience_years' => NumericFilter::make()->label('teacher.fields.experience_years'),
            'joining_date' => DateRangeFilter::make()->label('teacher.fields.joining_date'),
        ];
    }
}
