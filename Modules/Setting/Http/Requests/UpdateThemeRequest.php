<?php

namespace Modules\Setting\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Auth\Enums\Theme;
use Modules\Core\Abstracts\BaseRequest;

class UpdateThemeRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'theme' => ['required', 'string', Rule::enum(Theme::class)],
        ];
    }
}
