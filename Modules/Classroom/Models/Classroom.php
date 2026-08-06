<?php

namespace Modules\Classroom\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Abstracts\BaseModel;

class Classroom extends BaseModel
{
    protected $table = 'classrooms';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'has_projector' => 'boolean',
            'has_smartboard' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
