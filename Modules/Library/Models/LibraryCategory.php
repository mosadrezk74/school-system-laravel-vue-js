<?php

namespace Modules\Library\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Abstracts\BaseModel;
use Spatie\Translatable\HasTranslations;
use Modules\Library\Models\LibraryCategory;

class LibraryCategory extends BaseModel
{
    /*
     * Spatie stores these as JSON keyed by locale, so one row serves every
     * language the school has enabled. It also casts them itself — listing
     * them as 'array' too would return raw arrays instead of the translation.
     */
    use HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['name'];

    protected $table = 'library_categories';
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(LibraryCategory::class, 'parent_category_id');
    }
}
