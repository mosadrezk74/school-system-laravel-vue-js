<?php

namespace Modules\News\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Abstracts\BaseQuery;
use Modules\News\Enums\NewsAudience;
use Modules\News\Enums\NewsStatus;
use Modules\News\Models\News;
use Modules\Setting\Services\LanguageService;

/**
 * Reading news.
 *
 * The portal rules — published only, dated at or before now, and matching the
 * reader's audience — live here rather than in each portal controller, so one
 * shared feed serves students, guardians and teachers without three copies of
 * the filtering and no chance of a new screen forgetting a clause.
 */
class NewsQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $sortableFields = ['created_at', 'published_at', 'status', 'is_pinned'];

    protected function baseQuery(): Builder
    {
        return News::query()->with('author:id,name');
    }

    /**
     * `title` and `body` are JSON keyed by locale, so the term is matched
     * against each enabled language's value. The locale list comes from
     * LanguageService so adding a language makes it searchable with no change
     * here.
     */
    public function search(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $locales = app(LanguageService::class)->activeLocales();

        $this->query->where(function (Builder $query) use ($term, $locales): void {
            foreach ($locales as $locale) {
                $query->orWhere("title->{$locale}", 'LIKE', "%{$term}%")
                    ->orWhere("body->{$locale}", 'LIKE', "%{$term}%");
            }
        });

        return $this;
    }

    public function status(?string $status): self
    {
        if ($status !== null && NewsStatus::tryFrom($status) instanceof NewsStatus) {
            $this->query->where('status', $status);
        }

        return $this;
    }

    public function audience(?string $audience): self
    {
        if ($audience !== null && NewsAudience::tryFrom($audience) instanceof NewsAudience) {
            $this->query->where('audience', $audience);
        }

        return $this;
    }

    /** Published, and not scheduled for a date still in the future. */
    public function live(): self
    {
        $this->query->live();

        return $this;
    }

    /**
     * Restricts to what this account type may read. Staff pass null and see
     * every audience.
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
