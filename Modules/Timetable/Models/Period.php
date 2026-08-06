<?php

namespace Modules\Timetable\Models;

use Modules\Core\Abstracts\BaseModel;
use Spatie\Translatable\HasTranslations;

class Period extends BaseModel
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $table = 'periods';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'string',
            'end_time' => 'string',
            'is_break' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
