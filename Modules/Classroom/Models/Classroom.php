<?php

namespace Modules\Classroom\Models;

use Modules\Core\Abstracts\BaseModel;
use Spatie\Translatable\HasTranslations;

class Classroom extends BaseModel
{
    /*
     * Spatie stores this as JSON keyed by locale, so one row serves every
     * language the school has enabled. It also casts it itself — listing it
     * as 'array' too would return the raw array instead of the translation.
     */
    use HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['name'];

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
