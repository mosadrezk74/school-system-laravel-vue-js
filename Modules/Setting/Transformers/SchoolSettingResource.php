<?php

namespace Modules\Setting\Transformers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Abstracts\BaseResource;
use Modules\Setting\Models\SchoolSetting;

/**
 * @mixin SchoolSetting
 */
class SchoolSettingResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_name' => $this->school_name,
            'school_code' => $this->school_code,
            'school_email' => $this->school_email,
            'school_phone' => $this->school_phone,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            'currency_symbol' => $this->currency_symbol,
            'logo_path' => $this->logo_path,
            'logo_url' => $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null,
            'setup_completed' => (bool) $this->setup_completed,
            'settings' => $this->settings ?? [],
        ];
    }
}
