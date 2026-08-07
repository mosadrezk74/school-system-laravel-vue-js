<?php

namespace Modules\StudyMaterial\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\GradeLevel;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseModel;
use Modules\StudyMaterial\Enums\StudyResourceType;
use Modules\Subject\Models\Subject;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $subject_id
 * @property int $grade_level_id
 * @property int|null $classroom_id
 * @property int $teacher_id
 * @property StudyResourceType $type
 * @property string|null $external_url
 * @property int $downloads_count
 */
class StudyResource extends BaseModel implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    public const MEDIA_COLLECTION = 'file';

    /** @var array<int, string> */
    public array $translatable = ['title', 'description'];

    protected $table = 'study_resources';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => StudyResourceType::class,
            'downloads_count' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION)->singleFile();
    }

    public function file(): ?Media
    {
        return $this->getFirstMedia(self::MEDIA_COLLECTION);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @param  Builder<StudyResource>  $query
     */
    public function scopeOwnedBy(Builder $query, int $teacherId): void
    {
        $query->where('teacher_id', $teacherId);
    }
}
