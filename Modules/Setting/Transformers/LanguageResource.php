<?php

namespace Modules\Setting\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin \Modules\Setting\Models\Language
 */
class LanguageResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'locale' => $this->locale,
            'direction' => $this->direction,
            'is_active' => (bool) $this->is_active,
            'is_default' => (bool) $this->is_default,
        ];
    }
}
