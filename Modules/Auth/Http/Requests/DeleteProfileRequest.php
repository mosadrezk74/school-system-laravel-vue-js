<?php

namespace Modules\Auth\Http\Requests;

use Modules\Auth\Http\Requests\Concerns\PasswordValidationRules;
use Modules\Core\Abstracts\BaseRequest;

class DeleteProfileRequest extends BaseRequest
{
    use PasswordValidationRules;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => $this->currentPasswordRules(),
        ];
    }
}
