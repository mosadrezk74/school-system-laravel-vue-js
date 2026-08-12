<?php

namespace Modules\Setting\Repositories;

use App\Models\User;
use Illuminate\Database\QueryException;
use Modules\Setting\DTOs\CreateSchoolTermDTO;
use Modules\Setting\DTOs\UpdateSchoolTermDTO;
use Modules\Setting\Enums\SchoolTermStatus;
use Modules\Setting\Exceptions\SchoolTermOperationException;
use Modules\Setting\Interfaces\Repositories\SchoolTermRepositoryInterface;
use Modules\Setting\Models\SchoolTerm;
use Modules\Setting\Models\SchoolTermAcceptance;

class SchoolTermRepository implements SchoolTermRepositoryInterface
{
    public function findById(int $id): ?SchoolTerm
    {
        return SchoolTerm::query()->find($id);
    }

    public function currentPublished(): ?SchoolTerm
    {
        return SchoolTerm::query()->published()->first();
    }

    public function existingDraft(): ?SchoolTerm
    {
        return SchoolTerm::query()->draft()->first();
    }

    /**
     * Soft-deleted rows are counted so a discarded draft never frees its
     * number — the unique index covers them too, and reusing a version would
     * make the history ambiguous.
     */
    public function lockNextVersion(): int
    {
        $highest = SchoolTerm::withTrashed()
            ->lockForUpdate()
            ->max('version');

        return (int) $highest + 1;
    }

    public function createFromDTO(CreateSchoolTermDTO $dto, int $version, ?int $createdBy): SchoolTerm
    {
        return SchoolTerm::query()->create([
            ...$dto->toArray(),
            'version' => $version,
            'status' => SchoolTermStatus::Draft,
            'created_by' => $createdBy,
        ]);
    }

    public function updateFromDTO(SchoolTerm $term, UpdateSchoolTermDTO $dto): SchoolTerm
    {
        $term->update($dto->toArray());

        return $term->refresh();
    }

    public function delete(SchoolTerm $term): bool
    {
        return (bool) $term->delete();
    }

    public function lockPublishedForUpdate(): void
    {
        SchoolTerm::query()->published()->lockForUpdate()->get();
    }

    /**
     * Archive-then-publish in that order: the reverse would momentarily leave
     * two published rows visible to a concurrent reader.
     */
    public function publish(SchoolTerm $term): SchoolTerm
    {
        SchoolTerm::query()
            ->published()
            ->whereKeyNot($term->getKey())
            ->update(['status' => SchoolTermStatus::Archived]);

        $term->update([
            'status' => SchoolTermStatus::Published,
            'published_at' => now(),
        ]);

        return $term->refresh();
    }

    public function acceptanceFor(SchoolTerm $term, User $user): ?SchoolTermAcceptance
    {
        return SchoolTermAcceptance::query()
            ->where('school_term_id', $term->getKey())
            ->where('user_id', $user->getKey())
            ->first();
    }

    /**
     * The unique index on (school_term_id, user_id) is the real guard against
     * a double submit; a duplicate surfaces as the same business error the
     * pre-check raises, rather than a 500.
     */
    public function recordAcceptance(SchoolTerm $term, User $user): SchoolTermAcceptance
    {
        try {
            return SchoolTermAcceptance::query()->create([
                'school_term_id' => $term->getKey(),
                'user_id' => $user->getKey(),
                'accepted_at' => now(),
            ]);
        } catch (QueryException $exception) {
            if ($this->isUniqueViolation($exception)) {
                throw SchoolTermOperationException::alreadyAccepted();
            }

            throw $exception;
        }
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        return (int) ($exception->errorInfo[1] ?? 0) === 1062;
    }
}
