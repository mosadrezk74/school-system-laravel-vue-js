<?php

namespace Modules\Setting\Models;

use Modules\Core\Abstracts\BaseModel;

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
            'settings' => 'array',
        ];
    }
}
