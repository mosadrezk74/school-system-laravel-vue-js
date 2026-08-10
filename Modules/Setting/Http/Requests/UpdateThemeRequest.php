<?php

namespace Modules\Setting\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateThemeRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'theme' => ['required', 'string', 'in:light,dark'],
        ];
    }
}
