<?php

namespace Modules\Academic\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Database\Factories\SemesterFactory;
use Modules\Core\Abstracts\BaseModel;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $academic_year_id
 * @property int $sequence
 * @property CarbonInterface $start_date
 * @property CarbonInterface $end_date
 * @property CarbonInterface|null $exam_start_date
 * @property CarbonInterface|null $exam_end_date
 * @property bool $is_current
 * @property bool $is_active
 */
class Semester extends BaseModel
{
    use HasFactory;

    /*
     * Spatie stores this as JSON keyed by locale, so one row serves every
     * language the school has enabled. It also casts it itself — listing it
     * as 'array' too would return the raw array instead of the translation.
     */
    use HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['name'];

    protected $table = 'semesters';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'exam_start_date' => 'date',
            'exam_end_date' => 'date',
            'is_current' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * @param  Builder<Semester>  $query
     */
    public function scopeCurrent(Builder $query): void
    {
        $query->where('is_current', true);
    }

    /**
     * @param  Builder<Semester>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    protected static function newFactory(): SemesterFactory
    {
        return SemesterFactory::new();
    }
}
