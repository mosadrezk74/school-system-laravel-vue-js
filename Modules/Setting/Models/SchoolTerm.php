<?php

namespace Modules\Setting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Abstracts\BaseModel;
use Modules\Setting\Enums\SchoolTermStatus;
use Spatie\Translatable\HasTranslations;

class SchoolTerm extends BaseModel
{
    /*
     * Spatie stores these as JSON keyed by locale, so one row serves every
     * language the school has enabled. It casts them itself — listing them
     * as 'array' too would return raw arrays instead of the translation.
     */
    use HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['title', 'content'];

    protected $table = 'school_terms';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SchoolTermStatus::class,
            'version' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acceptances(): HasMany
    {
        return $this->hasMany(SchoolTermAcceptance::class, 'school_term_id');
    }

    public function isDraft(): bool
    {
        return $this->status === SchoolTermStatus::Draft;
    }

    public function isPublished(): bool
    {
        return $this->status === SchoolTermStatus::Published;
    }

    public function isArchived(): bool
    {
        return $this->status === SchoolTermStatus::Archived;
    }

    /**
     * @param  Builder<SchoolTerm>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', SchoolTermStatus::Published);
    }

    /**
     * @param  Builder<SchoolTerm>  $query
     */
    public function scopeDraft(Builder $query): void
    {
        $query->where('status', SchoolTermStatus::Draft);
    }
}
