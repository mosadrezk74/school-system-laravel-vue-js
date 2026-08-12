<?php

namespace Modules\Auth\Transformers;

use Illuminate\Http\Request;
use Modules\Auth\Models\Role;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin Role
 */
class RoleResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,

            // System roles carry a translated display label; custom ones have
            // no lang entry, so their raw name is shown instead.
            'label' => $this->displayLabel(),
            'guard_name' => $this->guard_name,
            'is_system' => $this->isSystem(),

            'permissions_count' => $this->whenCounted('permissions'),
            'users_count' => $this->whenCounted('users'),

            'permissions' => $this->whenLoaded(
                'permissions',
                fn () => $this->permissions->pluck('name')->all(),
            ),

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    private function displayLabel(): string
    {
        $key = "auth::roles.{$this->name}";
        $translated = __($key);

        return is_string($translated) && $translated !== $key ? $translated : $this->name;
    }
}
