<?php

namespace Modules\Setting\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TranslatableFilter;
use Modules\Setting\Enums\SchoolTermStatus;
use Modules\Setting\Models\SchoolTerm;

class SchoolTermQuery extends BaseQuery
{
    /**
     * `title` is JSON keyed by locale, matched through each enabled language.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['title'];

    /** @var array<int, string> */
    protected array $sortableFields = ['version', 'status', 'published_at', 'created_at'];

    protected function baseQuery(): Builder
    {
        return SchoolTerm::query();
    }

    /**
     * `content` is not filterable: it is the full body of the terms document,
     * so matching against it would scan long text for no useful narrowing that
     * the title does not already give.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'status' => EnumFilter::make(SchoolTermStatus::class)->label('schoolTerms.status'),
            'title' => TranslatableFilter::make()->label('schoolTerms.title'),
            'version' => NumericFilter::make()->label('schoolTerms.version'),
            'created_by' => ExactFilter::make()->label('schoolTerms.author')->nullable(),
            'published' => DateRangeFilter::make()->column('published_at')->label('schoolTerms.publishedAt'),
        ];
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
