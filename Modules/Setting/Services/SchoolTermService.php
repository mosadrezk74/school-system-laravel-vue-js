<?php

namespace Modules\Setting\Services;

use App\Models\User;
use Modules\Setting\Interfaces\Repositories\SchoolTermRepositoryInterface;
use Modules\Setting\Models\SchoolTerm;
use Modules\Setting\Models\SchoolTermAcceptance;

/**
 * Answers "which terms are in force, and has this person agreed to them?".
 *
 * Acceptance is always evaluated against the version currently published, so
 * publishing v3 automatically puts everyone who only ever accepted v2 back
 * into the "needs to accept" state — no backfill or per-user flag involved.
 */
class SchoolTermService
{
    public function __construct(
        private readonly SchoolTermRepositoryInterface $repository,
    ) {}

    public function current(): ?SchoolTerm
    {
        return $this->repository->currentPublished();
    }

    public function acceptanceFor(User $user): ?SchoolTermAcceptance
    {
        $term = $this->current();

        return $term instanceof SchoolTerm
            ? $this->repository->acceptanceFor($term, $user)
            : null;
    }

    /**
     * True only when the user has accepted the version in force right now.
     * With no published version there is nothing to accept, so this reads
     * true rather than blocking the portal behind a prompt that cannot be
     * satisfied.
     */
    public function hasAcceptedCurrent(User $user): bool
    {
        if (! $this->current() instanceof SchoolTerm) {
            return true;
        }

        return $this->acceptanceFor($user) instanceof SchoolTermAcceptance;
    }

    /**
     * The payload every portal screen needs to decide whether to show the
     * agreement prompt.
     *
     * @return array{term: SchoolTerm|null, accepted_current: bool, accepted_at: string|null}
     */
    public function statusFor(User $user): array
    {
        $term = $this->current();

        if (! $term instanceof SchoolTerm) {
            return ['term' => null, 'accepted_current' => true, 'accepted_at' => null];
        }

        $acceptance = $this->repository->acceptanceFor($term, $user);

        return [
            'term' => $term,
            'accepted_current' => $acceptance instanceof SchoolTermAcceptance,
            'accepted_at' => $acceptance?->accepted_at?->toIso8601String(),
        ];
    }
}
