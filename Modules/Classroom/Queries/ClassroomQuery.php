<?php

namespace Modules\Classroom\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Setting\Services\LanguageService;

class ClassroomQuery extends BaseQuery
{
    protected array $searchableFields = ['building'];

    protected array $filterableFields = ['room_type', 'is_active'];

    protected array $sortableFields = ['capacity', 'floor', 'building', 'room_type', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Classroom::query();
    }

    /**
     * `name` is JSON keyed by locale, so it is matched through each enabled
     * language's value rather than as a plain column. The locale list comes
     * from LanguageService, so adding a language in the settings makes it
     * searchable without touching this class.
     */
    public function search(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $locales = app(LanguageService::class)->activeLocales();

        $this->query->where(function (Builder $query) use ($term, $locales) {
            foreach ($this->searchableFields as $field) {
                $query->orWhere($field, 'LIKE', "%{$term}%");
            }

            foreach ($locales as $locale) {
                $query->orWhere("name->{$locale}", 'LIKE', "%{$term}%");
            }
        });

        return $this;
    }

    public function roomType(?string $roomType): self
    {
        if ($roomType) {
            $this->query->where('room_type', $roomType);
        }

        return $this;
    }

    public function isActive(?bool $isActive): self
    {
        if ($isActive !== null) {
            $this->query->where('is_active', $isActive);
        }

        return $this;
    }
}
