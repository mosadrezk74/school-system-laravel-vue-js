<?php

namespace Modules\Report\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Report\Enums\ReportFormat;
use Modules\Report\Enums\ReportGenerationStatus;

class ReportGeneration extends Model
{
    protected $table = 'report_generations';

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'filters_used' => 'array',
            'format' => ReportFormat::class,
            'status' => ReportGenerationStatus::class,
        ];
    }

    public function reportTemplate(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'report_template_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
