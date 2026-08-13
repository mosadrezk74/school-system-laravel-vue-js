<?php

namespace Modules\GuardianProfile\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Auth\Enums\Gender;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\RelationFilter;
use Modules\Core\Support\Filters\TextFilter;
use Modules\GuardianProfile\Enums\RelationshipType;
use Modules\GuardianProfile\Models\GuardianProfile;

class GuardianQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['occupation'];

    /**
     * Name, email and phone live on the related user, which is why every list
     * of guardians had to search two tables.
     *
     * @var array<string, array<int, string>>
     */
    protected array $searchableRelations = [
        'user' => ['name', 'email', 'username', 'phone', 'national_id'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = ['occupation', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return GuardianProfile::query()->with(['user']);
    }

    /**
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'is_active' => BooleanFilter::make()->label('guardians.active'),
            'gender' => EnumFilter::make(Gender::class)->on('user')->label('guardians.gender'),
            'occupation' => TextFilter::make()->label('guardians.occupation'),

            // Which children a guardian is linked to, and whether they are
            // linked to any at all — an unlinked guardian account is the thing
            // office staff go looking for.
            'student_id' => RelationFilter::make('students', 'student_id')->label('guardians.student'),
            'relationship' => EnumFilter::make(RelationshipType::class)
                ->on('students')
                ->column('relationship')
                ->label('guardians.relationship'),
            'has_students' => RelationFilter::make('students')->label('guardians.hasStudents'),
        ];
    }
}
