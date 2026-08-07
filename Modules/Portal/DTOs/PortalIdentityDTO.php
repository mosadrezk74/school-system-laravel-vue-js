<?php

namespace Modules\Portal\DTOs;

use Modules\Portal\Services\PortalScopeService;

final readonly class PortalIdentityDTO
{
    public function __construct(
        public ?int $userId,
        public ?string $name,
        public ?string $role,
        public ?int $profileId,
        public ?string $subtitle,
    ) {}

    public static function fromScope(PortalScopeService $scope): self
    {
        return new self(
            userId: $scope->user()?->id,
            name: $scope->user()?->name,
            role: $scope->role()?->value,
            profileId: self::resolveProfileId($scope),
            subtitle: self::resolveSubtitle($scope),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'name' => $this->name,
            'role' => $this->role,
            'profile_id' => $this->profileId,
            'subtitle' => $this->subtitle,
        ];
    }

    private static function resolveProfileId(PortalScopeService $scope): ?int
    {
        return match (true) {
            $scope->isStudent() => $scope->currentStudentProfile()?->id,
            $scope->isTeacher() => $scope->currentTeacher()?->id,
            $scope->isGuardian() => $scope->currentGuardian()?->id,
            default => null,
        };
    }

    private static function resolveSubtitle(PortalScopeService $scope): ?string
    {
        return match (true) {
            $scope->isStudent() => $scope->currentStudentProfile()?->currentClass?->name,
            $scope->isTeacher() => $scope->currentTeacher()?->position,
            $scope->isGuardian() => null,
            default => null,
        };
    }
}
