<?php

namespace Modules\Core\Services;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Resolves which portal a signed-in account belongs to and where it lands.
 *
 * The role => portal map is configuration, not code: an application defines
 * its own roles in config/portals.php and this stays reusable as-is.
 */
class PortalRouter
{
    public function landingRoute(?Authenticatable $user): string
    {
        return $this->portalConfig($user)['route'] ?? $this->fallbackRoute();
    }

    public function landingUrl(?Authenticatable $user): string
    {
        return route($this->landingRoute($user), absolute: false);
    }

    public function portalFor(?Authenticatable $user): ?string
    {
        return $this->portalConfig($user)['portal'] ?? null;
    }

    public function isPortalUser(?Authenticatable $user): bool
    {
        return $this->portalFor($user) !== null;
    }

    /**
     * @return array<string, mixed>
     */
    private function portalConfig(?Authenticatable $user): array
    {
        $role = $this->roleOf($user);

        return $role === null ? [] : (config("portals.roles.{$role}") ?? []);
    }

    private function roleOf(?Authenticatable $user): ?string
    {
        $role = $user?->{config('portals.role_attribute', 'role')} ?? null;

        return $role instanceof \BackedEnum ? (string) $role->value : $role;
    }

    private function fallbackRoute(): string
    {
        return config('portals.fallback_route', 'dashboard');
    }
}
