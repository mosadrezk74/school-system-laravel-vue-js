<?php

namespace Modules\Subject\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Branch\Models\Branch;
use Modules\Core\Abstracts\BaseModel;
use Spatie\Translatable\HasTranslations;

class Subject extends BaseModel
{
    /*
     * Spatie stores these as JSON keyed by locale, so one row serves every
     * language the school has enabled. It also casts them itself — listing
     * them as 'array' too would return raw arrays instead of the translation.
     */
    use HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['name', 'description'];

    protected $table = 'subjects';
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pass_grade' => 'decimal:2',
            'max_marks' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
