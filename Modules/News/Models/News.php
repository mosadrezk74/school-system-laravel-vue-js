<?php

namespace Modules\News\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Abstracts\BaseModel;
use Modules\Core\Traits\HasUniqueSlug;
use Modules\News\Enums\NewsAudience;
use Modules\News\Enums\NewsStatus;
use Spatie\Translatable\HasTranslations;

class News extends BaseModel
{
    /*
     * Spatie stores these as JSON keyed by locale, so one row serves every
     * language the school has enabled. It casts them itself — listing them as
     * 'array' too would return raw arrays instead of the translation.
     */
    use HasTranslations;

    // Derives the slug from the title, tolerating the translatable shape and
    // avoiding collisions with soft-deleted rows.
    use HasUniqueSlug;

    /** @var array<int, string> */
    public array $translatable = ['title', 'body'];

    protected $table = 'news';

    /** Source column for HasUniqueSlug. */
    protected string $slugSource = 'title';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => NewsStatus::class,
            'audience' => NewsAudience::class,
            'published_at' => 'datetime',
            'is_pinned' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Items the school has actually released.
     *
     * Published *and* dated at or before now: a future date means the item is
     * queued, not live, so this is the single definition of "visible" that
     * every portal read goes through.
     *
     * @param  Builder<News>  $query
     */
    public function scopeLive(Builder $query): void
    {
        $query->where('status', NewsStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Narrows to what a given account type may read. Staff pass null and are
     * not filtered at all.
     *
     * @param  Builder<News>  $query
     */
    public function scopeForAudience(Builder $query, ?UserRole $role): void
    {
        $query->whereIn('audience', NewsAudience::visibleTo($role));
    }

    public function isLive(): bool
    {
        return $this->status === NewsStatus::Published
            && $this->published_at !== null
            && $this->published_at->lessThanOrEqualTo(now());
    }
}
