<?php

namespace Modules\Library\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Branch\Models\Branch;
use Modules\Core\Abstracts\BaseModel;

class LibraryItem extends BaseModel
{
    protected $table = 'library_items';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'authors' => 'array',
            'price' => 'decimal:2',
            'fine_per_day' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LibraryCategory::class, 'category_id');
    }
}
