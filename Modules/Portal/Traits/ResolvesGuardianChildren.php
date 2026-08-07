<?php

namespace Modules\Portal\Traits;

use Illuminate\Http\Request;
use Modules\Portal\Services\PortalScopeService;
use Modules\Student\Models\StudentProfile;

trait ResolvesGuardianChildren
{
    protected function portalScope(): PortalScopeService
    {
        return app(PortalScopeService::class);
    }

    /**
     * The requested child is always matched against the guardian's own
     * children, so an id from the frontend can never widen access.
     */
    protected function selectedChild(Request $request): ?StudentProfile
    {
        return $this->portalScope()->resolveChild(
            $request->filled('child') ? (int) $request->input('child') : null,
        );
    }

    /**
     * @return array<int, array{id: int, name: string|null}>
     */
    protected function childOptions(): array
    {
        return $this->portalScope()->guardianChildren()
            ->map(fn (StudentProfile $child): array => [
                'id' => $child->id,
                'name' => $child->user?->name,
            ])
            ->values()
            ->all();
    }
}
