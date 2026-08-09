<?php

namespace Modules\Academic\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Academic\Models\GradeLevel;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Setting\Services\LanguageService;

class GradeLevelQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $filterableFields = ['is_active'];

    /** @var array<int, string> */
    protected array $sortableFields = [
        'display_order',
        'is_active',
        'created_at',
    ];

    protected function baseQuery(): Builder
    {
        return GradeLevel::query();
    }

    /**
     * `name` and `description` are JSON keyed by locale, so the term is matched
     * against each enabled language's value rather than the raw column. The
     * locale list comes from LanguageService so adding a language to the
     * settings makes it searchable without touching this class.
     */
    public function search(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $locales = app(LanguageService::class)->activeLocales();

        $this->query->where(function (Builder $query) use ($term, $locales): void {
            foreach ($locales as $locale) {
                $query->orWhere("name->{$locale}", 'LIKE', "%{$term}%")
                    ->orWhere("description->{$locale}", 'LIKE', "%{$term}%");
            }
        });

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
