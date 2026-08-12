<?php

namespace Modules\Setting\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Setting\Enums\SchoolTermStatus;
use Modules\Setting\Models\SchoolTerm;
use Modules\Setting\Services\LanguageService;

class SchoolTermQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $sortableFields = ['version', 'status', 'published_at', 'created_at'];

    protected function baseQuery(): Builder
    {
        return SchoolTerm::query();
    }

    /**
     * `title` is JSON keyed by locale, so the term is matched against each
     * enabled language's value. The locale list comes from LanguageService so
     * adding a language makes it searchable without touching this class.
     */
    public function search(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $locales = app(LanguageService::class)->activeLocales();

        $this->query->where(function (Builder $query) use ($term, $locales): void {
            foreach ($locales as $locale) {
                $query->orWhere("title->{$locale}", 'LIKE', "%{$term}%");
            }
        });

        return $this;
    }

    public function status(?string $status): self
    {
        if ($status !== null && SchoolTermStatus::tryFrom($status) instanceof SchoolTermStatus) {
            $this->query->where('status', $status);
        }

        return $this;
    }

    public function withAuthor(): self
    {
        $this->query->with('createdBy:id,name');

        return $this;
    }

    /** How many people have agreed to each version. */
    public function withAcceptanceCount(): self
    {
        $this->query->withCount('acceptances');

        return $this;
    }
}
