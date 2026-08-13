<?php

namespace Modules\Classroom\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Classroom\Enums\RoomType;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TextFilter;

class ClassroomQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['building'];

    /**
     * `name` is JSON keyed by locale, matched through each enabled language.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['name'];

    /** @var array<int, string> */
    protected array $sortableFields = ['capacity', 'floor', 'building', 'room_type', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Classroom::query();
    }

    /**
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'room_type' => EnumFilter::make(RoomType::class)->label('classroom.fields.room_type'),
            'is_active' => BooleanFilter::make()->label('classroom.fields.is_active'),
            'building' => TextFilter::make()->label('classroom.fields.building'),
            'floor' => NumericFilter::make()->label('classroom.fields.floor'),
            'capacity' => NumericFilter::make()->label('classroom.fields.capacity'),
            'has_projector' => BooleanFilter::make()->label('classroom.fields.has_projector'),
            'has_smartboard' => BooleanFilter::make()->label('classroom.fields.has_smartboard'),
        ];
    }
}
