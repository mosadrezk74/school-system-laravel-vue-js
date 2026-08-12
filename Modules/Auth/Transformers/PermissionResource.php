<?php

namespace Modules\Auth\Transformers;

use Illuminate\Http\Request;
use Modules\Auth\Models\Permission;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin Permission
 */
class PermissionResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'module' => $this->module,

            // "student.create" → resource "student", ability "create", so the
            // UI can render a resource-per-row, ability-per-column grid.
            'resource' => $this->resourceName(),
            'ability' => $this->abilityName(),
        ];
    }

    private function resourceName(): string
    {
        return (string) str($this->name)->beforeLast('.');
    }

    private function abilityName(): string
    {
        return (string) str($this->name)->afterLast('.');
    }
}
