<?php

namespace Modules\News\Repositories;

use Modules\News\DTOs\CreateNewsDTO;
use Modules\News\DTOs\UpdateNewsDTO;
use Modules\News\Enums\NewsStatus;
use Modules\News\Interfaces\Repositories\NewsRepositoryInterface;
use Modules\News\Models\News;

class NewsRepository implements NewsRepositoryInterface
{
    public function findById(int $id): ?News
    {
        return News::query()->find($id);
    }

    public function createFromDTO(CreateNewsDTO $dto, ?int $authorId): News
    {
        return News::query()->create([
            ...$dto->toArray(),
            'published_at' => $this->resolvePublishedAt($dto->status, $dto->publishedAt, null),
            'author_id' => $authorId,
        ]);
    }

    public function updateFromDTO(News $news, UpdateNewsDTO $dto): News
    {
        $news->update([
            ...$dto->toArray(),
            'published_at' => $this->resolvePublishedAt(
                $dto->status,
                $dto->publishedAt,
                $news->published_at?->toDateTimeString(),
            ),
        ]);

        return $news->refresh();
    }

    public function delete(News $news): bool
    {
        return (bool) $news->delete();
    }

    public function restore(News $news): bool
    {
        return (bool) $news->restore();
    }

    public function setPinned(News $news, bool $isPinned): News
    {
        $news->update(['is_pinned' => $isPinned]);

        return $news->refresh();
    }

    public function publish(News $news): News
    {
        $news->update([
            'status' => NewsStatus::Published,

            // Keeps an explicitly scheduled date; otherwise it goes live now.
            'published_at' => $news->published_at ?? now(),
        ]);

        return $news->refresh();
    }

    /**
     * Back to draft. The date is cleared so re-publishing does not silently
     * reuse a date that has since passed.
     */
    public function unpublish(News $news): News
    {
        $news->update([
            'status' => NewsStatus::Draft,
            'published_at' => null,
        ]);

        return $news->refresh();
    }

    /**
     * A draft never carries a date; a published item always does, defaulting
     * to now when the admin did not schedule one.
     */
    private function resolvePublishedAt(NewsStatus $status, ?string $requested, ?string $existing): ?string
    {
        if (! $status->isPublished()) {
            return null;
        }

        return $requested ?? $existing ?? now()->toDateTimeString();
    }
}
