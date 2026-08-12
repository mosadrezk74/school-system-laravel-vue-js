<?php

namespace Modules\Auth\Transformers;

use App\Models\User;
use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin User
 */
class AdminResource extends BaseResource
{
    /**
     * The password and two-factor columns are hidden on the model itself, so
     * they can never reach this array — but nothing here reads them either.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date?->toDateString(),
            'national_id' => $this->national_id,
            'is_active' => (bool) $this->is_active,

            'account_role' => $this->role?->value,
            'account_role_label' => $this->role?->label(),

            'roles' => $this->whenLoaded(
                'roles',
                fn () => $this->roles->pluck('name')->all(),
            ),

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
