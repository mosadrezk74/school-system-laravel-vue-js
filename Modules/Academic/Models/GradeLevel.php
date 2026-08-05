<?php

namespace Modules\Academic\Models;

use Modules\Core\Abstracts\BaseModel;
use Spatie\Translatable\HasTranslations;

class GradeLevel extends BaseModel
{
    /*
     * Spatie stores these as JSON keyed by locale, so one row serves every
     * language the school has enabled. It also casts them itself — listing
     * them as 'array' too would return raw arrays instead of the translation.
     */
    use HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['name', 'description'];

    protected $table = 'grade_levels';
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'passing_grade' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
