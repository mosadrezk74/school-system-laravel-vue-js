<?php

namespace Modules\Communication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Abstracts\BaseModel;
use Spatie\Translatable\HasTranslations;

class BroadcastMessage extends BaseModel
{
    use HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['title', 'body'];

    protected $table = 'broadcast_messages';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_ids' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
