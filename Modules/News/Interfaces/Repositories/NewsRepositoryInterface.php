<?php

namespace Modules\News\Interfaces\Repositories;

use Modules\News\DTOs\CreateNewsDTO;
use Modules\News\DTOs\UpdateNewsDTO;
use Modules\News\Models\News;

interface NewsRepositoryInterface
{
    public function findById(int $id): ?News;

    public function createFromDTO(CreateNewsDTO $dto, ?int $authorId): News;

    public function updateFromDTO(News $news, UpdateNewsDTO $dto): News;

    public function delete(News $news): bool;

    public function restore(News $news): bool;

    public function setPinned(News $news, bool $isPinned): News;

    public function publish(News $news): News;

    public function unpublish(News $news): News;
}
