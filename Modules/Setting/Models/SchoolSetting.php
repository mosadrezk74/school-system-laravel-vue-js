<?php

namespace Modules\Setting\Models;

use Modules\Core\Abstracts\BaseModel;
use Modules\Setting\Services\SettingsService;

class SchoolSetting extends BaseModel
{
    protected $table = 'school_settings';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'setup_completed' => 'boolean',
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => SettingsService::forgetCache());
        static::deleted(fn () => SettingsService::forgetCache());
    }
}
