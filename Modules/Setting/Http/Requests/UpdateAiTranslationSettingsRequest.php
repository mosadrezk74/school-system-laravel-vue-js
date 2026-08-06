<?php

namespace Modules\Setting\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateAiTranslationSettingsRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'provider' => ['required', 'string', 'in:libretranslate'],
        ];
    }
}
