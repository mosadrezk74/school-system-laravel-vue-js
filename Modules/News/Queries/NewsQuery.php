<?php

namespace Modules\News\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\TranslatableFilter;
use Modules\News\Enums\NewsAudience;
use Modules\News\Enums\NewsStatus;
use Modules\News\Models\News;

class NewsQuery extends BaseQuery
{
    /**
     * `title` and `body` are JSON keyed by locale, matched through each
     * enabled language.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['title', 'body'];

    /** @var array<string, array<int, string>> */
    protected array $searchableRelations = [
        'author' => ['name'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = ['created_at', 'published_at', 'status', 'is_pinned'];

    protected function baseQuery(): Builder
    {
        return News::query()->with('author:id,name');
    }

    /**
     * `slug` is left out: it is derived from the title, so filtering by it
     * duplicates the title filter while exposing a routing detail.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'status' => EnumFilter::make(NewsStatus::class)->label('news.fields.status'),
            'audience' => EnumFilter::make(NewsAudience::class)->label('news.fields.audience'),
            'is_pinned' => BooleanFilter::make()->label('news.fields.isPinned'),
            'author_id' => ExactFilter::make()->label('news.fields.author')->nullable(),
            'title' => TranslatableFilter::make()->label('news.fields.title'),
            'published' => DateRangeFilter::make()->column('published_at')->label('news.fields.publishedAt'),
        ];
    }

    public function live(): self
    {
        $this->query->live();

        return $this;
    }

    /**
     * Restricts to what this account type may read. Staff pass null and see
     * every audience.
     *
     * Audience scoping, not a user-facing filter, so it stays a method rather
     * than joining allowedFilters().
     */
    public function visibleTo(?UserRole $role): self
    {
        if ($role !== null) {
            $this->query->forAudience($role);
        }

        return $this;
    }

    /**
     * The portal feed: live items this reader may see, pinned ones first.
     */
    public function feedFor(?UserRole $role): self
    {
        return $this->live()->visibleTo($role)->pinnedFirst();
    }

    public function pinnedFirst(): self
    {
        $this->query->orderByDesc('is_pinned')->orderByDesc('published_at')->orderByDesc('id');

        return $this;
    }
}
