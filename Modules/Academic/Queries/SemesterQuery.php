<?php

namespace Modules\Academic\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Academic\Models\Semester;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Setting\Services\LanguageService;

class SemesterQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $filterableFields = ['academic_year_id', 'is_active'];

    /*
     * `name` is intentionally absent: it is a JSON column, so ordering by it
     * would sort by the raw document rather than by the visible text.
     */
    /** @var array<int, string> */
    protected array $sortableFields = [
        'sequence',
        'start_date',
        'end_date',
        'is_active',
        'created_at',
    ];

    /**
     * The academic year is eager loaded because the index table renders its
     * name on every row — without it the listing costs one query per semester.
     */
    protected function baseQuery(): Builder
    {
        return Semester::query()->with('academicYear:id,name');
    }

    /**
     * `name` is JSON keyed by locale, so the term is matched against each
     * enabled language's value rather than the raw column. The locale list
     * comes from LanguageService, so adding a language in the settings makes
     * it searchable without touching this class.
     */
    public function search(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $locales = app(LanguageService::class)->activeLocales();

        $this->query->where(function (Builder $query) use ($term, $locales): void {
            foreach ($locales as $locale) {
                $query->orWhere("name->{$locale}", 'LIKE', "%{$term}%");
            }
        });

        return $this;
    }

    public function academicYear(?int $academicYearId): self
    {
        if ($academicYearId) {
            $this->query->where('academic_year_id', $academicYearId);
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

    /** Archived rows are listed alongside live ones so they can be restored. */
    public function withArchived(): self
    {
        $this->query->withTrashed();

        return $this;
    }
}
