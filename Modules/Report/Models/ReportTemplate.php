<?php

namespace Modules\Report\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Abstracts\BaseModel;

class ReportTemplate extends BaseModel
{
    protected $table = 'report_templates';
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'query_data' => 'array',
            'columns' => 'array',
            'filters' => 'array',
            'is_active' => 'boolean',
        ];
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
