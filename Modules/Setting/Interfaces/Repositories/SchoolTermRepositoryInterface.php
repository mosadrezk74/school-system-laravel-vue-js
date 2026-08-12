<?php

namespace Modules\Setting\Interfaces\Repositories;

use App\Models\User;
use Modules\Setting\DTOs\CreateSchoolTermDTO;
use Modules\Setting\DTOs\UpdateSchoolTermDTO;
use Modules\Setting\Models\SchoolTerm;
use Modules\Setting\Models\SchoolTermAcceptance;

interface SchoolTermRepositoryInterface
{
    public function findById(int $id): ?SchoolTerm;

    public function currentPublished(): ?SchoolTerm;

    public function existingDraft(): ?SchoolTerm;

    /**
     * Next version number, taken under a row lock held until the surrounding
     * transaction commits so two concurrent drafts cannot claim the same one.
     */
    public function lockNextVersion(): int;

    public function createFromDTO(CreateSchoolTermDTO $dto, int $version, ?int $createdBy): SchoolTerm;

    public function updateFromDTO(SchoolTerm $term, UpdateSchoolTermDTO $dto): SchoolTerm;

    public function delete(SchoolTerm $term): bool;

    /**
     * Marks the given draft published and archives whatever was published
     * before it, as one atomic step.
     */
    public function publish(SchoolTerm $term): SchoolTerm;

    /** Locks every published row so two publishes cannot interleave. */
    public function lockPublishedForUpdate(): void;

    public function acceptanceFor(SchoolTerm $term, User $user): ?SchoolTermAcceptance;

    public function recordAcceptance(SchoolTerm $term, User $user): SchoolTermAcceptance;
}
