<?php

namespace Modules\Auth\Transformers;

use App\Models\User;
use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin User
 */
class AccountResource extends BaseResource
{
    /**
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
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'national_id' => $this->national_id,
            'language' => $this->language,
            'theme' => $this->theme,
            'timezone' => $this->timezone,
            'email_verified_at' => $this->email_verified_at,
        ];
    }
}
